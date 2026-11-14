# Bucket 1/2 work + interim-drift reconciliation — 2026-11-11

Session resumed three days after the Nov-8 work. On resume, the live site contained an owner/parallel-session addition not present in the workspace: **`instapass-checkout-polish.php/.css`** (payment-method cards with brand marks, and the honest GBP/EUR checkout recovery message with a "Switch to USD →" link, priority 310). This plugin already covered both bucket-1 items from the Nov-8 plan.

## What changed this round

1. **Email branding (bucket 2, item 3) — delivered.** WooCommerce email template options set: header image (brand logo, rasterised from `assets/logo/instapass-logo-on-light.svg`, uploaded to `/wp-content/uploads/2026/11/instapass-email-logo.png`, https URL), base colour #7c3aed, page background #f6f4fb, body #ffffff, text #201f2e. A branded test email was sent to the owner's Gmail (`test_sent: true`); previous values recorded in the deployment script. Rollback: restore the five `woocommerce_email_*` options (add `woocommerce_email_` prefix) to `''`, `#b85a17`, `#ffffff`, `#ffffff`, `#1f2124`.
2. **Bucket-1 duplicates removed after reconciliation.** My parallel GBP/EUR message (theme `manual-payments.php`, priority 300) and my payment-card CSS (instapass-ux.css) duplicated the interim plugin's work and lost the priority/cascade anyway. Both reverted so there is exactly one implementation of each; verified live afterwards (USD CTA shows the amount, cards render from the interim plugin, GBP recovery message + switch link render from the interim plugin).
3. **"What you get" checklist (bucket 2, item 1) — found already provided, duplicate withdrawn.** In the interim, a 4-item owner-approved checklist ("We activate your selected plan…", "Private access…", "Export your chat history…", "Full 30-day warranty…", matching `ops/product-copy-data.php`) was observed rendering on a product page with class `ipl-whatyouget`. My duplicate list in the launch-catalog card was reverted (it also never rendered on the current templates). The `.ipl-whatyouget` styling (checkmarks) remains in launch-catalog.css so that copy renders consistently wherever it appears. The checklist's current render path on live product pages should be re-confirmed by the owner; it was not present in later plain requests (possible template/caching variance), and no workspace source renders it.
4. **CTA race genuinely fixed.** The place-order control is a `<button>` in WooCommerce 11; WooCommerce's own button script can overwrite its label after ours. The UX script now re-applies the "Continue to payment — {total} →" label on a short schedule and via a MutationObserver, and disables the button with a clear label when a currency has no payment methods (e.g. GBP). Verified: USD CTA label correct; GBP button disabled with explanatory text.
5. Old GBP/EUR message filter in the theme confirmed reverted to its original single-line form; a stray no-op conditional in the launch-catalog was removed.

## Verification

`ops/bucket-final-verify.cjs`: USD CTA label correct; payment cards styled (interim plugin); GBP recovery message, switch link and disabled CTA confirmed in the prior run. Email branding verified by option readback + test send. Recovery: `/opt/instapass/recovery/bucket1-20261109T053000Z/` (pre-change copies of the four touched files) plus the mu-plugin/theme backups noted above.

## Lesson recorded

Work must start by diffing the live server against the workspace after any time gap — a parallel/owner change (`instapass-checkout-polish`) had already implemented part of the plan and could have been overwritten blindly. See MISTAKES entry 77.

No products, prices, orders (no test orders were placed this round), payment settings or customer data were changed.
