import assert from 'node:assert/strict';
import {
  COMMANDCODE_CHAT_COMPLETIONS_URL,
  COMMANDCODE_MODEL_IDS,
  createCommandCodeAdapter,
} from './commandcode-adapter.mjs';

const products = [
  { id: 1, name: 'Canva Pro', sku: 'canva-pro', type: 'simple', regular_price: '20', sale_price: '', categories: ['Creativity'], status: 'publish', fingerprint: 'catalogue-fingerprint-1' },
];
const model = COMMANDCODE_MODEL_IDS[0];
const instruction = { operation: 'sale', target: 'Canva Pro', value: 9.99, rounding: 'exact', clarification: null };
const providerResponse = content => ({
  ok: true,
  status: 200,
  text: async () => JSON.stringify({ choices: [{ message: { content: JSON.stringify(content) } }] }),
});
const tests = [];
const test = (name, run) => tests.push([name, run]);

test('default disabled and deterministic commands stay local', async () => {
  let calls = 0;
  const interpret = createCommandCodeAdapter({ fetchImpl: async () => { calls++; throw new Error('must not be called'); } });
  const result = await interpret({ command: 'Give canva-pro 25% off', products });
  assert.equal(result.source, 'local');
  assert.equal(result.preview.changes[0].after.sale_price, '14.99');
  assert.equal(calls, 0);
});

test('unsupported wording fails locally while the adapter is disabled', async () => {
  let calls = 0;
  const interpret = createCommandCodeAdapter({ fetchImpl: async () => { calls++; } });
  await assert.rejects(interpret({ command: 'Could you make Canva Pro a little cheaper?', products }), { code: 'model_disabled' });
  assert.equal(calls, 0);
});

test('enabled adapter sends one bounded request and routes output through deterministic pricing', async () => {
  let calls = 0;
  let captured;
  const interpret = createCommandCodeAdapter({
    enabled: true,
    apiKey: 'unit-test-secret',
    model,
    timeoutMs: 1500,
    fetchImpl: async (url, options) => { calls++; captured = { url, options }; return providerResponse(instruction); },
  });
  const result = await interpret({ command: 'Could you set the sale price for Canva Pro to 9.99?', products });
  assert.equal(calls, 1);
  assert.equal(captured.url, COMMANDCODE_CHAT_COMPLETIONS_URL);
  assert.equal(captured.options.headers.Authorization, 'Bearer unit-test-secret');
  const sent = JSON.parse(captured.options.body);
  assert.equal(sent.model, model);
  assert.equal(sent.max_tokens, 256);
  assert.equal(sent.stream, false);
  assert.equal(result.source, 'model');
  assert.equal(result.preview.changes[0].after.sale_price, '9.99');
  assert.equal(result.preview.requires_owner_confirmation, true);
});

test('ambiguous model interpretation asks a clarification and produces no preview', async () => {
  const interpret = createCommandCodeAdapter({
    enabled: true, apiKey: 'unit-test-secret', model,
    fetchImpl: async () => providerResponse({ operation: null, target: null, value: null, rounding: null, clarification: 'Which product do you mean?' }),
  });
  const result = await interpret({ command: 'Lower the sale price', products });
  assert.equal(result.clarification, 'Which product do you mean?');
  assert.equal(result.preview, null);
});

test('model cannot invent target absent from the local catalogue', async () => {
  const interpret = createCommandCodeAdapter({ enabled: true, apiKey: 'unit-test-secret', model, fetchImpl: async () => providerResponse({ ...instruction, target: 'invented-product' }) });
  await assert.rejects(interpret({ command: 'Make the mystery item cheaper', products }), /exact product or published category match/i);
});

test('requests containing order or contact details never reach the model', async () => {
  let calls = 0;
  const interpret = createCommandCodeAdapter({ enabled: true, apiKey: 'unit-test-secret', model, fetchImpl: async () => { calls++; return providerResponse(instruction); } });
  await assert.rejects(interpret({ command: 'Change the price and check order 1234 for a.j@example.com', products }), { code: 'request_not_sent' });
  assert.equal(calls, 0);
});

test('missing keys and non-allowlisted models fail before any request', async () => {
  let calls = 0;
  const fetchImpl = async () => { calls++; return providerResponse(instruction); };
  assert.throws(() => createCommandCodeAdapter({ enabled: true, model, fetchImpl }), { code: 'missing_api_key' });
  assert.throws(() => createCommandCodeAdapter({ enabled: true, apiKey: 'unit-test-secret', model: 'not-a-model', fetchImpl }), { code: 'invalid_model' });
  assert.equal(calls, 0);
});

test('unexpected model output fields are rejected', async () => {
  const interpret = createCommandCodeAdapter({ enabled: true, apiKey: 'unit-test-secret', model, fetchImpl: async () => providerResponse({ ...instruction, execute_code: 'delete orders' }) });
  await assert.rejects(interpret({ command: 'Make Canva cheaper', products }), { code: 'invalid_model_output' });
});

test('HTTP failures and timeouts are not retried or switched to a fallback model', async () => {
  let calls = 0;
  const httpFailure = createCommandCodeAdapter({ enabled: true, apiKey: 'unit-test-secret', model, fetchImpl: async () => { calls++; return { ok: false, status: 429 }; } });
  await assert.rejects(httpFailure({ command: 'Make Canva cheaper', products }), { code: 'provider_error' });
  assert.equal(calls, 1);

  const timeout = createCommandCodeAdapter({ enabled: true, apiKey: 'unit-test-secret', model, fetchImpl: async () => { calls++; const e = new Error('timeout'); e.name = 'AbortError'; throw e; } });
  await assert.rejects(timeout({ command: 'Make Canva cheaper', products }), { code: 'provider_timeout' });
  assert.equal(calls, 2);
});

test('invalid catalogue and timeouts are rejected before requests', async () => {
  let calls = 0;
  assert.throws(() => createCommandCodeAdapter({ enabled: true, apiKey: 'unit-test-secret', model, timeoutMs: 20_000, fetchImpl: async () => { calls++; } }), { code: 'invalid_timeout' });
  const interpret = createCommandCodeAdapter({ enabled: true, apiKey: 'unit-test-secret', model, fetchImpl: async () => { calls++; } });
  await assert.rejects(interpret({ command: 'Make Canva cheaper', products: null }), { code: 'invalid_catalogue' });
  assert.equal(calls, 0);
});

for (const [name, run] of tests) {
  await run();
  console.log(`PASS ${name}`);
}
console.log(`${tests.length} adapter checks passed; provider calls were mocked and no real API calls were made.`);
