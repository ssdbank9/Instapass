import { preparePriceCommand } from './price-command.mjs';

export const COMMANDCODE_CHAT_COMPLETIONS_URL = 'https://api.commandcode.ai/provider/v1/chat/completions';
export const COMMANDCODE_MODEL_IDS = Object.freeze([
  'deepseek/deepseek-v4.1-flash',
  'Qwen/Qwen3.8-Flash',
  'z-ai/glm-5.3-flash',
]);

const MAX_COMMAND_LENGTH = 500;
const MAX_RESPONSE_LENGTH = 16_384;
const MAX_OUTPUT_TOKENS = 256;
const DEFAULT_TIMEOUT_MS = 12_000;

const SYSTEM_PROMPT = `Interpret a store owner's requested change to one WooCommerce product. Return one JSON object only, with exactly these keys: operation, target, value, rounding, clarification. operation must be "regular", "sale", or "discount"; target is the exact product name or SKU stated by the owner; value is a positive number; rounding is "exact" or "rounded"; clarification is null or a short question. If the owner is ambiguous, set operation, target, value, and rounding to null and ask one clarification. Never invent a product name, SKU, price, or discount. Never suggest changing stock, visibility, coupons, orders, payments, or accounts. Do not emit markdown or extra keys.`;

function fail(message, code) {
  const error = new Error(message);
  error.code = code;
  throw error;
}

function normalizeIntent(raw) {
  if (!raw || typeof raw !== 'object' || Array.isArray(raw)) fail('Model returned an invalid instruction object.', 'invalid_model_output');
  const allowedKeys = ['operation', 'target', 'value', 'rounding', 'clarification'];
  if (Object.keys(raw).length !== allowedKeys.length || Object.keys(raw).some(key => !allowedKeys.includes(key))) {
    fail('Model returned unexpected instruction fields.', 'invalid_model_output');
  }
  if (raw.clarification !== null) {
    if (typeof raw.clarification !== 'string' || !raw.clarification.trim() || raw.clarification.length > 240) {
      fail('Model returned an invalid clarification.', 'invalid_model_output');
    }
    if (raw.operation !== null || raw.target !== null || raw.value !== null || raw.rounding !== null) {
      fail('A clarification must not include an actionable instruction.', 'invalid_model_output');
    }
    return { clarification: raw.clarification.trim() };
  }
  if (!['regular', 'sale', 'discount'].includes(raw.operation)) fail('Model selected an unsupported operation.', 'invalid_model_output');
  if (typeof raw.target !== 'string' || !raw.target.trim() || raw.target.length > 120) fail('Model returned an invalid product target.', 'invalid_model_output');
  if (typeof raw.value !== 'number' || !Number.isFinite(raw.value) || raw.value <= 0 || raw.value > 100_000) fail('Model returned an invalid price or percentage.', 'invalid_model_output');
  if (raw.operation === 'discount' && raw.value >= 100) fail('Discount must be less than 100 percent.', 'invalid_model_output');
  if (!['exact', 'rounded'].includes(raw.rounding)) fail('Model returned an invalid rounding mode.', 'invalid_model_output');
  return {
    operation: raw.operation,
    target: raw.target.trim(),
    value: raw.value,
    rounding: raw.rounding,
  };
}

function toDeterministicCommand(intent) {
  const amount = intent.value.toFixed(2).replace(/\.00$/, '').replace(/(\.\d)0$/, '$1');
  if (intent.operation === 'discount') {
    return `Give ${intent.target} ${amount}% off ${intent.rounding}`;
  }
  return `Set ${intent.target} ${intent.operation} price to ${amount}`;
}

function canSendToModel(command) {
  const hasPriceIntent = /\b(price|regular|sale|discount|off|cheaper|lower|raise|increase|reduce)\b|%/i.test(command);
  const containsPrivateWorkflow = /\b(order|payment|transaction|wallet|refund|customer|screenshot|email|phone|password|login|account)\b/i.test(command)
    || /[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}/.test(command)
    || /(?:\+?\d[\s().-]*){8,}/.test(command);
  return hasPriceIntent && !containsPrivateWorkflow;
}

/**
 * Create an optional CommandCode interpretation adapter.
 *
 * It is disabled unless explicitly enabled. Recognized deterministic commands
 * never call the provider. Any provider request is a single bounded call with
 * no retry or paid-model fallback; provider output is only accepted if the
 * existing deterministic preview engine can validate its normalized command.
 */
export function createCommandCodeAdapter({
  enabled = false,
  apiKey = '',
  model = '',
  fetchImpl = globalThis.fetch,
  timeoutMs = DEFAULT_TIMEOUT_MS,
} = {}) {
  if (typeof enabled !== 'boolean') fail('Adapter enable flag must be boolean.', 'invalid_config');
  if (enabled && !COMMANDCODE_MODEL_IDS.includes(model)) fail('Choose a documented, allowlisted CommandCode model ID.', 'invalid_model');
  if (enabled && (typeof apiKey !== 'string' || !apiKey.trim())) fail('CommandCode API key is required when the adapter is enabled.', 'missing_api_key');
  if (typeof fetchImpl !== 'function') fail('A fetch implementation is required.', 'missing_fetch');
  if (!Number.isInteger(timeoutMs) || timeoutMs < 1 || timeoutMs > 15_000) fail('Timeout must be between 1 and 15000 milliseconds.', 'invalid_timeout');

  return async function interpretPriceCommand({ command, products, currency = 'USD' } = {}) {
    if (typeof command !== 'string' || !command.trim() || command.length > MAX_COMMAND_LENGTH) {
      fail('Enter a command of at most 500 characters.', 'invalid_command');
    }
    if (!Array.isArray(products)) fail('A local WooCommerce product snapshot is required.', 'invalid_catalogue');
    try {
      return {
        source: 'local',
        preview: preparePriceCommand(command, products, currency),
      };
    } catch {
      if (!enabled) fail('This wording needs model interpretation; the optional adapter is disabled. Use a supported exact command.', 'model_disabled');
    }
    if (!canSendToModel(command)) fail('This request is outside the safe product-pricing interpreter scope and was not sent.', 'request_not_sent');

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeoutMs);
    let response, bodyText;
    try {
      response = await fetchImpl(COMMANDCODE_CHAT_COMPLETIONS_URL, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${apiKey}`,
          'Content-Type': 'application/json',
        },
        signal: controller.signal,
        body: JSON.stringify({
          model,
          messages: [
            { role: 'system', content: SYSTEM_PROMPT },
            { role: 'user', content: command.trim() },
          ],
          temperature: 0,
          max_tokens: MAX_OUTPUT_TOKENS,
          stream: false,
        }),
      });
      if (response?.ok) bodyText = await response.text();
    } catch (error) {
      if (error?.name === 'AbortError') fail('CommandCode request timed out; no retry was made.', 'provider_timeout');
      fail('CommandCode request failed; no retry or provider fallback was attempted.', 'provider_unavailable');
    } finally {
      clearTimeout(timer);
    }

    if (!response?.ok) fail(`CommandCode returned HTTP ${Number(response?.status) || 0}; no retry or fallback was attempted.`, 'provider_error');
    if (typeof bodyText !== 'string' || bodyText.length > MAX_RESPONSE_LENGTH) fail('CommandCode response exceeded the allowed size.', 'invalid_provider_response');

    let body;
    try {
      body = JSON.parse(bodyText);
    } catch {
      fail('CommandCode returned malformed JSON.', 'invalid_provider_response');
    }
    const content = body?.choices?.[0]?.message?.content;
    if (typeof content !== 'string' || content.length > 2_000) fail('CommandCode response did not contain a bounded text instruction.', 'invalid_provider_response');

    let decoded;
    try {
      decoded = JSON.parse(content);
    } catch {
      fail('Model instruction was not valid JSON.', 'invalid_model_output');
    }
    const intent = normalizeIntent(decoded);
    if (intent.clarification) return { source: 'model', clarification: intent.clarification, preview: null };

    const normalizedCommand = toDeterministicCommand(intent);
    const preview = preparePriceCommand(normalizedCommand, products, currency);
    return { source: 'model', normalized_command: normalizedCommand, preview };
  };
}
