# Grouped launch catalog — 6 November 2026

LIVE on instapass.online. User requested one tile per product, in-tile durations, other products held for later, original tile appearance, and pricing deferred. Later added two monthly-expiring credit products with $50–$500 balances and explicitly corrected the step to $25.

| Visible tile | Selectable options |
| --- | --- |
| Claude Pro | 1, 6, 12 months |
| Claude Max 5× | 1, 6, 12 months |
| Claude Ultra 20× | 1, 6, 12 months |
| ChatGPT Plus | 1, 6, 12 months |
| Canva Pro | 12 months |
| Claude Credits | $50–$500 credit balance, $25 steps; credits expire monthly |
| ChatGPT Credits | $50–$500 credit balance, $25 steps; credits expire monthly |

## Implementation and owner controls

Additive MU module `mu-plugins/instapass-launch-catalog.php` v1.1.0 and its CSS/JS. Original visual language retained: four desktop columns, coloured logos, quiet logo panels, borders and mobile stacking. Customer collections show one family tile rather than repeating duration/credit-balance listings. Each option remains an independently managed simple WooCommerce offer, compatible with the existing owner price/stock controls. There are 13 subscription options plus 38 credit-balance options behind seven visible tiles.

Original raw USD/PKR/GBP prices, stock and coupon flags were preserved. All options are explicitly held for pricing review. Public price getters, price HTML, structured offers and purchasability prevent old prices or direct add-to-cart from bypassing the hold. No zero/free placeholder prices were assigned. Products outside the launch set were retained as drafts, not deleted. Previous order snapshots were not rewritten.

Owner screen: [Instapass → Product Tiles](https://instapass.online/wp-admin/admin.php?page=instapass-product-tiles). Edit tile names/captions, show/hide a family, follow each option's link to Products & Offers, and separately mark its price reviewed/ready once real pricing is supplied. A positive USD full-term price is required before approval. Independent GBP and fixed/automatic PKR editing remain in the existing offer editor. No option was approved during this release.

Credit balances mean USD-denominated credit face value, not selling price. Each supported balance is tied to a specific backend product; selection carries to its detail URL. Credit balance and monthly expiry are included in cart data and saved order-item metadata for future fulfilment. Monthly expiry is a disclosed term, not newly implemented provider-side expiration/provisioning automation. Actual credit redemption/delivery and paid checkout remain pending.

## Tests and evidence

- PHP lint for module, both migrations, native checks and rollback helper passes in the patched PHP runtime. JS and Python source syntax checks pass.
- Initial isolated migration: five families, thirteen options, four new unpriced offers, exactly five visible products; original stored prices/stock/coupon flags and orders preserved. Native tests reject forged purchasing, exercise future price approval, and render the owner approval controls.
- Initial five-tile UI: 63 isolated and 63 live browser checks pass after visual correction. Original layout and logo colours inspected on desktop and phone. Stale empty-catalog text suppressed.
- Credit extension: seven families, 38 credit faces, 37 new unpriced offers (existing Claude Credits $100 offer reused), previous subscription grouping and original price/stock/order values preserved. Native cart/order-item metadata check retains `$75` and `Monthly`; all 51 options remain unapproved.
- Final UI: **197 isolated and 197 live browser checks pass**, run through Desktop Commander on X1CarbonPC using the installed Playwright/Chrome runtime. Includes every $25 increment for both sliders, min/max and plus/minus behaviour, keyboard operation, output/panel consistency, all subscription durations, logos, no duplicate cards, no old buy links, selected credit face on detail page, homepage/detail rendering and phone/desktop width.
- Final visible Chrome tab opened at the live shop. Evidence in ignored outputs: `launch-catalog-tiles.png`, `launch-catalog-desktop.png`, `launch-catalog-phone.png`, `launch-catalog-browser.json`. No production order, payment, customer account or test email submitted.
- Instapass and Wasooli HTTPS health checks pass after both releases. Only the current task's exact test containers/networks and preview SSH tunnels were removed. Protected recovery records remain.

## Recovery and limits

Protected server recovery folders:

- `/opt/instapass/recovery/grouped-catalog-20261106T114755Z`: original five-family release, database export, original product snapshot, mapping and candidate sources.
- `/opt/instapass/recovery/grouped-catalog-20261106T120824Z`: credit extension, original five-family sources/configuration snapshots, database export, product snapshot and final mapping.

Use the scoped rollback helper with the matching product snapshot and previous module sources. It restores affected fields/metadata and holds newly created records as drafts; do not restore an old production database merely to undo catalog UI changes or overwrite newer orders. Migration helpers are guarded one-time operations, not generic repeatable updaters.

Pricing, totals in each currency, coupon/credit combinations, expiry start-date definition, provider fulfilment and paid activation still need the pricing/operations acceptance pass. Historical scripts/docs saying every tenor gets its own public tile are superseded for this launch. No whole-store readiness claim is made.
