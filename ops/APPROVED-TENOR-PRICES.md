# Approved subscription prices - 2026-10-19

Owner policy supersedes the earlier $170 Plus / $67-per-month Max annual discussion. USD annual base prices are set so native 10% bundle discounts reach the requested targets, allowing native one-cent basket discount allocation.

| Listing | Upfront USD before coupon | After eligible 10% | PKR before coupon at rate 280 |
| --- | ---: | ---: | ---: |
| ChatGPT Plus - 1 month (existing) | 14.99 | 13.49 | 4,200 |
| ChatGPT Plus - 1 year | 173.33 | About156 | 47,500 fixed override |
| Claude Max 5x - 1 month (existing) | 75.00 | 67.50 | 21,000 |
| Claude Max 5x - 6 months | 432.00 | 388.80 | 121,000 |
| Claude Max 5x - 1 year | 833.33 | About750 | 233,300 |

Six-month Max is $72/month equivalent, paid as $432 upfront. Native coupon allocation may move an item total by one cent depending on the other basket lines. Annual discounted Max is $62.50/month equivalent, paid as $750 upfront. Annual discounted Plus is $13/month equivalent, paid as $156 upfront. No automatic recurring billing is introduced. The 10% code requires at least four distinct product/tenor selections; buying one annual offer alone does not qualify. Quantities and same-family/same-duration duplicates do not inflate the count.

| Requirement | Acceptance evidence |
| --- | --- |
| Correct exact prices / no .99 substitution | Native product prices and guarded bulk exemption per listing |
| Plus annual PKR fixed at47,500 | Independent regular/sale owner fields; default conversion preserved elsewhere |
| Original Plus one month | Owner confirmed; retain14.99 effective USD price |
| Higher tenors separate and purchasable together | Three new simple listings; family links; order duration metadata |
| Max only5x / copy preserved | Exact source SKU guard; excluded Max20 and Pro remain unchanged |
| GBP independent | New GBP prices stay blank; no automatic GBP conversion |
| Account optional / coupons / payments | Native existing checkout; one coupon and no new receiving settings |
| Owner no-code controls | Products > Edit > General: fixed PKR regular, sale and percentage; exact USD import checkbox |
| Protection | Nonce/capability/fingerprint validation; unrelated catalog/order/payment/coupon fingerprints |
| Verified publication | 29 native pricing +24 currency regressions; guarded source/catalog deployment;17 live pricing checks +18 rendered copy/width checks |

PKR override takes priority over conversion. A USD global discount or rate change does not rewrite a fixed PKR price; edit its PKR sale or percentage explicitly. Checkout percentage coupons apply in the selected currency. The ordinary derived PKR unit remains rounded to nearest100 before quantity; coupons retain minor units. Existing orders retain their saved amounts.

Status: LIVE. New published listings:138 Plus annual,139 Max5x six months,140 Max5x annual. Existing29 is explicitly one month at14.99;31 is one month at75.00. No longer-term Claude Pro offer was invented.

Verification and scope:
- Twenty-nine isolated native pricing checks passed, including fixed PKR order persistence, later price changes leaving that order unchanged, stale quote detection, owner save actions, nonce/anonymous/stale/malformed rejection, default conversion restoration and exact USD import eligibility. Existing24currency/checkout regressions passed.
- Seventeen live anonymous pricing checks passed (including a post-price-change TOOLKIT75 basket total91.56,discount7.42): native four-line subtotal1513.66,discount151.37,total1362.29; single annual rejects TOOLKIT10; PKR annual Plus47500; GBP unpriced; guest/phone layout preserved.
- Eighteen live desktop/phone copy checks passed. Required25%additional-limits text starts the Pro and Max5x briefs and is geometrically verified as unclipped. Max20x excluded. New higher-tenor descriptions use confirmed upfront terms and the refund-policy link; legacy monthly descriptions are no longer copied to new offers.
- Only intended catalog mutations were made. Before/after release guards proved83unrelated catalog records, all3historical orders, configured exchange rate, coupon data/settings and payment settings unchanged. The later approved brief-order/copy patch preserved the complete price/stock/order/coupon/payment audit.
- Live sources match staged bytes; pricing recovery:/opt/instapass/recovery/approved-prices-20261019T045109Z; copy recovery:/opt/instapass/recovery/approved-prices-copy-20261019T045337Z.
- Backup:/opt/instapass/recovery/pre-commerce-20261019T044646Z;58restored tables and database integrity passed. Full application disaster recovery is not claimed.
- No live order, payment, account or customer email created. Isolated containers auto-cleaned; no test ports/tunnel or synthetic owner credentials used by this pricing fixture.
- No GBP amounts or additional receiving methods enabled. Existing broader cohost TLS finding is recorded separately in THREE-PRODUCT-OFFER.md; this pricing release did not touch Caddy or Wasooli.

Evidence (local QA):outputs/approved-prices-deploy.txt,approved-prices-live-verification.json,approved-offer-copy-verification.json and phone screenshots. Source/validation helpers and the full Control Center baseline are retained in Git for reproducibility.

Operator sequence:run-approved-prices.ps1 -Action test, then backup, then deploy. The deploy action is guarded for the previous source release and intentionally refuses a blind rerun; reconcile actual state first. Configuration helpers are identity-guarded. Copy repair applies only to the verified release IDs. For rollback, unpublish the three new listings, restore the two existing listings' original duration/family/price metadata from recovery, and restore only affected source files; preserve newer orders and actual coupon usage. Revert brief and new-offer content from copy-before.json only if that copy change is also being rolled back.
