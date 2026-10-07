# Instapass text-command automation — development foundation

2026-10-07. User authorised developing an n8n-based setup with CommandCode as a possible AI provider, including text commands for prices and discounts. Zero additional spending remains a constraint. This folder is local development; nothing here is connected to production.

## Delivered and verified

- `price-command.mjs`: deterministic, side-effect-free price proposal generator. No model calls, credentials, live product reads or writes.
- `price-command.test.mjs`: 16 checks passed with fixture products, including ambiguity rejection, category scope, numeric validation, missing prices, rounding, preservation of stock/visibility and existing sale dates, and no input mutation.
- `instapass-price-preview.n8n.json`: inactive importable draft workflow: Manual Trigger → sample input → validated preview. It has no HTTP, payment, email, Telegram or database nodes. JSON and embedded code can be checked locally; import/execution on an actual n8n installation is unverified.

## Current development slice — owner-confirmed WordPress price operations

- WooCommerce administrators use `WooCommerce > Pricing Assistant`; all four REST routes require `manage_woocommerce`, and browser requests include a WordPress REST nonce.
- Preview accepts one exact product name or SKU and the deterministic command grammar. The server stores a five-minute proposal in a dedicated WordPress table. The browser shows the matched product, before/after prices and actual discount, then requires a separate owner confirmation.
- Confirmation atomically claims a pending operation and acquires a database-backed WordPress options-row lease for that product. It uses WordPress database methods and a unique option name rather than engine-specific advisory-lock functions. An expired lease is reclaimed with a compare-and-swap update, and release deletes only the current owner's exact token. Abandoned leases expire after five minutes. Confirmation also checks preview expiry and the original product fingerprint, applies WooCommerce price setters, reloads and verifies the saved values, and records an audit event. Repeated confirmation is idempotent. A stale preview is refused. The lease serializes this assistant's operations; ordinary WooCommerce admin edits do not use the same lease.
- Undo is limited to an applied operation and refuses if the product fingerprint changed since the operation. A save or audit-finalization failure is locked for manual review; the automation does not retry uncertain writes.
- The page shows the current user's latest 30 operations and audit events. The database table is created on plugin activation and preserves history when the plugin is deactivated.
- Percentage calculations call `instapass_price_values()` from the active theme. This plugin makes no external API, model, n8n, payment, email or paid-service calls; browser requests go only to this WordPress site's REST API. Commands are deterministic and exact-target only.
- Status: PHP 8.3 WASM syntax, JavaScript syntax, and focused mocked checks passed for preview persistence, permission gates, explicit confirmation, duplicate confirmation, assistant product locking/reclaim/release, expiry, stale-price refusal, audit history, undo, stale-undo refusal and failure lockout. A local SQLite WordPress/WooCommerce preview exists; activation, database migration, REST authentication, WooCommerce save hooks, actual database locking, races with ordinary admin edits, browser interaction and deployment remain unverified. Do not install or activate on the live site until those integration/security checks pass.

WordPress pricing assistant examples:

- `Set chatgpt-plus sale price to 14.99`
- `Set chatgpt-plus regular price to 20`
- `Give chatgpt-plus 25% off`
- `Give chatgpt-plus 25% off exact`

Exact product names or unique SKUs are required. The WordPress plugin supports one product per operation. The separate offline parser supports category scope; the WordPress plugin does not yet. Percentage commands default to nearest .99. The preview shows actual percentage because .99 rounding may differ from the requested percentage. A positive proposed regular price is required. Coupon creation, schedules, enabling/disabling, order changes, and natural-language model interpretation are not implemented yet.

## Production design to implement after connection details

1. Owner-only command entry (separate from public tool finder).
2. Read authoritative WooCommerce product IDs, prices and current fingerprints.
3. Parse straightforward commands locally for zero model cost. Optional AI can interpret flexible phrasing into a strictly validated instruction, never executable code.
4. Local preview, confirmation, stale-state guard, operation audit and guarded undo endpoints are implemented; live WordPress integration and adversarial concurrency review remain pending.
5. Before adding AI or n8n, prove the existing deterministic path works on a disposable WordPress/WooCommerce installation with representative simple/external/variation products and all save hooks enabled. Do not exercise it on live catalogue products until the owner approves a tested release.

The existing theme already has `instapass_price_values` and `instapass_price_fingerprint` in `inc/pricing.php`; production implementation should use the PHP calculation as final authority rather than trusting JavaScript/model numbers. Existing start/end dates remain unchanged by default. Scheduled discounts and coupons need their own explicit operations and tests.

## CommandCode integration findings

Official docs: https://commandcode.ai/docs/provider

- API supports `https://api.commandcode.ai/provider/v1/chat/completions` and bearer authentication. Endpoint availability varies by model.
- Docs state Go plan does not include API access; GOAT, Pro, Max, Team and Provider do. Coding-plan API calls consume their plan credits; Provider is pay as you go.
- Docs advertise some temporary zero-credit models, but availability and entitlement need verification for the user's account. No model chosen and no API call made.
- Store a key only in authenticated n8n credentials after secure admin access exists. Never paste it into chat, frontend code, workflow exports, Git or logs.
- User amended the no-credit-spend rule on 2026-10-07: included subscription credits may be consumed, after billing controls are verified, with no additional purchases or charges. Keep calls disabled until those controls and account/model entitlement are verified. No plan upgrade, extra-credit purchase, automatic reload or chargeable fallback is authorised.

## Pending deployment dependencies

- Exact OCI instance shape/RAM, existing load and Always Free allocation have not been verified.
- n8n instance URL/access not supplied; no n8n installation performed.
- Secure admin connection (current WordPress URL is HTTP) remains to resolve.
- CommandCode plan/access and securely entered credentials remain to resolve.
- Live WooCommerce email transport remains unconfigured; installing FluentSMTP did not fix delivery.
- The owner pricing flow exists only in local source; its live WordPress database/API integration and security review remain to test before packaging or deployment. Public recommendation/chatbot and Telegram alerts are later flows, not delivered by this demo.
