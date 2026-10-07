# Instapass text-command automation — development foundation

2026-10-07. User authorised developing an n8n-based setup with CommandCode as a possible AI provider, including text commands for prices and discounts. Zero additional spending remains a constraint. This folder is local development; nothing here is connected to production.

## Delivered and verified

- `price-command.mjs`: deterministic, side-effect-free price proposal generator. No model calls, credentials, live product reads or writes.
- `price-command.test.mjs`: 16 checks passed with fixture products, including ambiguity rejection, category scope, numeric validation, missing prices, rounding, preservation of stock/visibility and existing sale dates, and no input mutation.
- `instapass-price-preview.n8n.json`: inactive importable draft workflow: Manual Trigger → sample input → validated preview. It has no HTTP, payment, email, Telegram or database nodes. JSON and embedded code can be checked locally; import/execution on an actual n8n installation is unverified.

## Current development slice — authenticated WordPress preview screen and endpoint

- `plugins/instapass-automation/instapass-automation.php` adds `POST /wp-json/instapass-automation/v1/commands/preview`.
- WooCommerce administrators can enter a command at `WooCommerce > Pricing Preview`. The screen displays the matched product, current/proposed regular and sale prices, actual discount and preserved sale timing.
- The route requires the WordPress `manage_woocommerce` capability. WordPress cookie-authenticated REST requests also require a REST nonce.
- It accepts one exact product name or SKU and the existing deterministic price-command grammar. It returns before/after values and a fingerprint; it does not persist a proposal or change a product.
- Percentage calculations call the active theme's `instapass_price_values()` helper, so the storefront's PHP calculation stays authoritative. If WooCommerce or those helpers are unavailable, the route fails closed with HTTP 503.
- This screen is preview-only; it has no apply/confirm button. No category operation, coupon, AI call, n8n connection, or live endpoint exercise is included in this slice. Do not activate or deploy until the following slices and live security review are complete.
- Status: PHP 8.3 WASM syntax check and focused mocked checks passed for route/page registration, capability gating, nonce/asset configuration, read-only rendering, 25% nearest-.99 calculation, preview output and invalid-discount rejection. JavaScript syntax was checked locally. Real WordPress REST authentication, product lookup against the live catalogue, browser interaction and deployment remain unverified. This is not production-ready automation.

Supported examples:

- `Set Canva Pro sale price to $9.99`
- `Set claude-pro regular price to 40`
- `Give category Creativity 25% off`
- `Give Canva Pro 25% off exact`

Exact product names or unique SKUs are required. Categories must be prefixed with `category`; category commands include published products only. Percentage commands default to nearest .99. The preview shows actual percentage because .99 rounding may differ from the requested percentage. A positive current regular price is required. Coupon creation, schedules, enabling/disabling, order changes, and natural-language model interpretation are not implemented yet.

## Production design to implement after connection details

1. Owner-only command entry (separate from public tool finder).
2. Read authoritative WooCommerce product IDs, prices and current fingerprints.
3. Parse straightforward commands locally for zero model cost. Optional AI can interpret flexible phrasing into a strictly validated instruction, never executable code.
4. Generate an immutable preview with expiry, target IDs, before/after prices and sale timing. Return a clarification for ambiguous names or unspecified regular/sale price.
5. Owner confirms that exact preview; server checks owner identity, permissions, expiry and current price fingerprints. Reject stale previews.
6. Apply through WooCommerce product setters with durable operation IDs, serialized product updates and an audit log. Do not let model text select arbitrary endpoints or perform writes. No direct SQL or arbitrary shell execution.
7. Report success/failure per product and provide a reviewed undo proposal using saved prior values; undo must check that no later changes would be overwritten.

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
- Live catalogue adapter, owner auth, preview storage, confirmed apply/undo and audit endpoints remain to build and test. Public recommendation/chatbot and Telegram alerts are later flows, not delivered by this demo.
