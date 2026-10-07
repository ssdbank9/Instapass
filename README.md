# Instapass

Public source repository for the Instapass WooCommerce storefront and its local automation prototype.

## Project layout

- `theme-v1.9.0/instapass/` — current WordPress block theme source.
- `plugins/instapass-automation/` — owner-capability-gated price-command preview endpoint. This prototype is read-only; it does not write product data.
- `automation/` — deterministic price preview engine and inactive n8n demonstration workflow.
- `.qa/` — selected offline checks for the preview endpoint.

## Development status

The storefront theme and manual payment-report flow have been tested in the university demo environment. The automation endpoint has passed PHP 8.3 WASM syntax checking and mocked preview checks, but it has not been installed or exercised in WordPress. No live price changes, AI API calls, n8n deployment, or automated payment verification are part of this prototype.

The preview endpoint uses the theme's WooCommerce price calculation helper as its source of truth. It requires an authenticated WordPress user with `manage_woocommerce` capability and a REST nonce for cookie-authenticated requests. It deliberately provides no apply/confirm route.

## Local checks

Run the deterministic JavaScript preview checks with:

```sh
node automation/price-command.test.mjs
```

The PHP syntax and mocked endpoint checks use PHP-WASM from `.qa/package.json` and can be run from `.qa`:

```sh
node lint-instapass-automation.mjs
node instapass-automation-preview-test.mjs
```

## Security and configuration

Do not add WordPress credentials, API keys, n8n credentials, customer payment evidence, order keys, or production database exports. Runtime payment settings and secrets belong in the protected WordPress/server configuration, not in this repository.
