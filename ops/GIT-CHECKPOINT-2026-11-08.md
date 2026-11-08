# Instapass Git checkpoint — 8 November 2026

This checkpoint records the reviewed website source and documentation accumulated since the19October commit. It is a local source checkpoint; it does not publish to GitHub, redeploy the site or back up the live database.

## Included scope

- Grouped product tiles, duration choices, credit selectors and owner product/price/tenure controls.
- Independently editable USD/GBP/EUR prices, configurable USD-to-PKR rate and PKR unit rounding; owner discount controls.
- Guest checkout/tracking and account allocation, contact/refund/FAQ copy, access guidance and notification requirements.
- Deployment, recovery and verification source, relevant owner guides, dated acceptance records and the canonical MISTAKES.md log.
- The latest Ultra official comparison mapping:200USD/month reference, inherited by all linked terms, with compact TOOLKIT5 and tax text.

Current owner entry point: [Product Manager](https://instapass.online/wp-admin/admin.php?page=instapass-product-manager). See [the owner guide](PRODUCT-OFFERS-OWNER-GUIDE.md), [implementation status](../IMPLEMENTATION-STATUS.md) and the dated acceptance records for behavior and limits. Saved product prices, stock, coupon configuration and orders reside in WordPress; committing source does not snapshot those database values.

## Checks run for this checkpoint

- PHP8.3 parsing:78candidate PHP files pass using the installed PHP-WASM runtime.
- JavaScript syntax:42candidate files pass; Python parsing:34scripts pass; shell syntax:22scripts pass; pricing JSON parses.
- Existing offline money suite passes13valid cases,12malformed-price cases and5invalid quantities; per-unit PKR rounding and policy identity pass.
- Existing independent GBP suite passes11valid and18invalid cases, blank-unavailable handling and explicit zero sale.
- Existing automation preview/workflow suites pass permission/nonce checks, saved preview and confirmation, idempotency, locking, stale/corrupt refusal, audit and guarded undo.
- Content scan/manual review of candidate credential findings: no literal API token/private key found. Flagged matches were runtime-generated isolated database passwords and a WooCommerce lookup-table name. Additional OAuth/JWT/credential-URL scan found no matches.
- Latest Ultra acceptance remains the previously completed39native and116live browser checks; those checks were not rerun solely for the Git checkpoint. See [Ultra acceptance](ULTRA-SAVINGS-ACCEPTANCE.md).

## Excluded and preserved locally

No credentials, SSH keys, environment/config secrets, browser sessions, private order records, production exports, migration archives, generated packages, screenshots or output JSON are added. Private handoffs and live dummy-order evidence/helpers remain local. Existing ignored PROJECT-DECISIONS.md remains local; this checkpoint and the owner guides provide the committed entry points. Superseded pending lesson copies and one-off authenticated browser setup helpers remain local; MISTAKES.md remains canonical. Python bytecode/cache files are ignored. No local evidence was deleted.

## Limits and next decisions

Version-specific migration scripts and historical tests preserve their original assumptions. Do not rerun old migrations against the current catalog or run live order/email harnesses as a generic test suite. Many operations tests require a disposable isolated database and explicit scope. Review current acceptance records before selecting them.

Official Ultra naming/usage has changed relative to the storefront's historical20x wording; the latest banner fix did not revise that commercial description. Payment collection/reconciliation, provider-side access/expiry and broader launch readiness remain separate tasks. Protected server backups and dated deployment audits remain outside Git; this checkpoint does not reverify every deployment or test described in historical records.
