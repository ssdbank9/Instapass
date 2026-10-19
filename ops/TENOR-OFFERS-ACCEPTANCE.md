# Same product, separate tenor offers - requirements and acceptance

Owner request: the same product must be offered for higher tenors and added separately. Existing coupons are5% for two selections and10% for four. No new duration, price or stock entitlement has been supplied.

| Requirement | Implementation / acceptance |
| --- | --- |
| Same product, independently priced tenors | Separate simple WooCommerce listings linked by an owner-entered product family; no guessed duration/price |
| Discover and add each tenor separately | Product-page duration choices with price, stock and separate add action; existing basket preserved |
| Owner control without code | Native product editor group field, existing Access duration/pricing/stock controls; management guide |
| Bundle threshold counts product+tenor | Distinct linked family and duration count separately; repeat same family+duration counts once, independent quantities |
| Preserve product/tier identity | Owner chooses family; no automatic name matching or combining different plans |
| Order clarity | Duration on cart/checkout and saved order line; price/currency snapshot remains native |
| Keep currency/restock features | Simple listings reuse existing USD/PKR/GBP price controls and simple-product restock alerts |
| Safe publication | No live catalog changes; clone integration/browser tests, guarded deployment, live checks and before/after catalog/order hashes |

Status: framework installed and verified. Additional customer-visible tenor listings await owner-supplied durations and prices; synthetic examples exist only in the isolated clone.

## Verified results - 2026-10-19

- 22 native WordPress/WooCommerce integration checks passed: four durations of one family, duplicate annual1 year/12 months identity, two/four duration coupon totals, repeated quantity exclusion, disabled purchase for sold-out/unpriced tenors, private draft exclusion, returning guest eligibility and saved Duration on each of four order lines.
- 22 actual isolated browser checks passed: product-page choices, only 4 purchasable buttons among 6 published options, direct addition of 1 month then 3 months preserving both cart rows, both coupon totals, phone fit, owner save of family field in native editor and persistent linked choices, owner guide and independent coupon enabled controls. Screenshot outputs/tenor-offers-isolated-390.png visually inspected.
- 11 live anonymous cart/browser checks passed after activation. Existing USD 2-product discount 1.20 and 4-product discount 24.90, total 224.06 remain correct, guest checkout remains optional, phone fit and Instapass/Wasooli HTTP 200 confirmed. No live order/payment/account/email submitted. Actual live higher-tenor purchasing is pending commercial term/price inputs, not claimed from synthetic tests.
- Guarded deployment recovery:/opt/instapass/recovery/tenor-offers-deploy-20261019T024515Z. Installed PHP/CSS sources match reviewed uploads. Guest Orders 0.2.1 changes only bundle terminology/version; Tenor Offers 0.1.0 activated. Product/order/coupon audit values unchanged;20 priced/65 intentional unpriced products and3orders remain. Product hash9b30817dd5cc513d7730eda3b03dc2d21e9605adaf8e157d619d5c57d719832c; order hash0887a8418822bd718190c8ea1449481db1472341168f2993356d3ad884a1694a.
- Fresh archive checks and isolated 58-table database restoration/integrity passed at pre-commerce-20261019T023921Z. Full application restore is a separate acceptance exercise.
- Dedicated test containers/network/tunnel and synthetic credentials removed; root-protected recovery/evidence retained. New plugin has no paid service or background sender.
- Remaining input: exact product/plan, additional duration, USD price and any independently entered GBP price. Each offer's stock/access terms also remain owner-controlled. No multiplying monthly prices or inventing availability.

Rollback: deactivate instapass-tenor-offers and restore only the saved Promotions and Guest Orders PHP files. Preserve subsequent orders, prices and family metadata. No database rollback is needed to remove this framework.
Owner product scope confirmed: ChatGPT Plus, Claude Pro and Claude Max. Exact higher durations/USD prices are still pending; Claude Max 5× versus 20× needs confirmation. Keep those plans distinct. No new commercial listing is authorized by a guessed price or duration.

Additional price-independence acceptance: 24 native integration checks now pass after adding a two-tenor case with prices10.99and20.99. Basket subtotal31.98,5%discount1.60,total30.38. Higher-price duration remains visible. This expanded isolated rerun introduced no runtime source change; its temporary web/database/network and synthetic server credentials were cleaned again. The22browser and11live checks above apply to identical runtime sources.
