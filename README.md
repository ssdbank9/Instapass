# Instapass

Source repository for the Instapass WooCommerce storefront, owner administration, guest orders and operational checks. The November 2026 checkpoint captures the current local source; runtime product records, credentials and private evidence are stored separately.

## Project layout

- `theme-v1.9.0/instapass/` — current WordPress block theme source.
- `plugins/instapass-control-center/` — offer editing, currency pricing, rate settings, discounts and owner controls.
- `plugins/instapass-guest-orders/` — guest order tracking, payment reporting and account allocation.
- `plugins/instapass-tenor-offers/` — duration choices and monthly-equivalent presentation.
- `mu-plugins/` — grouped catalog, editable credit cycles, product manager and access guidance.
- `plugins/instapass-automation/` — capability-gated price previews, explicit confirmation, audit history and guarded undo.
- `automation/` — deterministic price preview engine and inactive n8n demonstration workflow.
- `.qa/` — selected offline pricing, currency and automation checks.
- `ops/` — deployment/recovery helpers, test harnesses, acceptance records and owner guides. Historical scripts are version-specific; review their guards before use.

## Development status

Start with [the November checkpoint](ops/GIT-CHECKPOINT-2026-11-08.md), [implementation status](IMPLEMENTATION-STATUS.md) and [the owner product guide](ops/PRODUCT-OFFERS-OWNER-GUIDE.md). Later dated acceptance records supersede earlier catalog, coupon and currency policies.

The site has grouped product tiles, four-currency pricing, optional accounts, guest tracking and editable product/duration/credit controls. The latest Ultra banner correction has dated native and live browser acceptance. This repository checkpoint is not a new deployment or a full launch-readiness claim. Real payment reconciliation, provider fulfilment and remaining operational checks must follow their separate acceptance requirements.

Price-command automation requires an authenticated owner-capability user and a REST nonce for cookie-authenticated requests. Saved previews require a separate confirmation before applying; audit records and guarded undo preserve the change history. The inactive n8n demonstration is separate from this workflow.

## Local checks

Run the deterministic JavaScript preview checks with:

```sh
node automation/price-command.test.mjs
```

The PHP syntax and mocked endpoint checks use PHP-WASM from `.qa/package.json` and can be run from `.qa`:

```sh
node lint-instapass-automation.mjs
node instapass-automation-preview-test.mjs
node instapass-automation-workflow-test.mjs
node control-center-money.test.mjs
node control-center-gbp.test.mjs
```

## Security and configuration

Do not add WordPress credentials, API keys, n8n credentials, customer payment evidence, order keys, or production database exports. Runtime payment settings and secrets belong in the protected WordPress/server configuration, not in this repository.


## Canonical project lessons

`MISTAKES.md` is the Git-tracked canonical Instapass lessons log. `AGENTS.md` directs future work to this record. Historical pending files are consolidated there; add new project findings to the canonical file. Live implementation and recovery evidence is summarized in `IMPLEMENTATION-STATUS.md`.
