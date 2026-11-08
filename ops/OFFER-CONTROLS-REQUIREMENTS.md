# Offer controls - requirements and acceptance

Date: 2026-10-19. User-authorized extension of existing WordPress store; payments remain unchanged.

| ID | Requirement | Acceptance |
|---|---|---|
| R1 | Any two purchased units qualify, including 2 x Plus one month. Three/four units use current 7.5/10% thresholds. | Native cart totals and browser same-SKU quantity 2/3/4; one coupon only. |
| R2 | Owner creates/edits/duplicates offers without code. | One dashboard form for name, SKU, plan family, duration, descriptions, draft/publish, stock and restock ETA. Guard permission, nonce and stale data. |
| R3 | Owner chooses product logo/image, including new duration offers. | Built-in brand selector, optional second logo, WordPress media picker, preview; selected visual renders on card/product/cart. |
| R4 | Owner controls independent USD/GBP and automatic/fixed PKR prices, sale amounts/percent and start/end dates. | Save validation before mutation, same schedule honored in all three currencies, current pricing and money regressions. |
| R5 | Owner controls coupon timing and eligible products. | Per-offer coupon enable/disable; managed coupon start/end and all/selected products; counting and discounted lines obey scope. |
| R6 | Product tiles show current total savings. | Display active price reduction amount and percentage; term savings and conditional extra coupon savings clearly labelled without adding percentages. |
| R7 | Default USD except UK GBP; PKR remains selectable. | Country mapping GB=GBP, PK/other/unknown=USD; manual choice remembered and old order currency preserved. |
| R8 | Preserve live commercial records unless owner explicitly edits. | Before/after catalog/order/coupon/payment audit; source guards, rollback files, no real test purchases. |
| R9 | One-click Activate/Deactivate and stock toggles; optional quantity tracking; Monthly, 6-month, Annual and custom tenors; separate full-term base/sale prices; savings shown on catalog tiles. | Live owner-page render and isolated browser/native checks cover controls; each tenor is a separate offer with its own price, stock, active state, family and logo. |

Assumptions: mix-and-match means separately purchasable product/duration offers plus configurable basket coupons; existing product and bulk-edit screens remain available. Sale dates use the WordPress timezone and apply across the offer's currencies. Saved manual currency selection takes priority over first-visit location detection. A coupon advertised as extra remains conditional on its stated quantity; sale and coupon savings are compounded rather than percentages added.
