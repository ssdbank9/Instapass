# Offer controls acceptance - 2026-10-19

Live: Control Center 0.7.0, Tenor Offers 0.3.0, scoped theme/Guest Orders updates. Owner URL: https://instapass.online/wp-admin/admin.php?page=instapass-offers.

| Requirement | Evidence | Status |
|---|---|---|
| R1 repeated units qualify | Native 2/3/4 same-SKU cart totals; live Plus two=$28.48, three=$41.60, four=$53.96 | Passed |
| R2 no-code product creation/edit/copy/publish/trash | Native validation/capability/stale tests; real browser draft creation, publication, concurrent edit rejection and retirement | Passed isolated; live read-only owner form render passed |
| R3 selectable brand/media/second logo and current duration logos | Browser native media picker, image save/clear; native image priority and family fallback; live IDs138/139/140 logos load, annual card logo inspected | Passed |
| R4 USD/fixed or automatic PKR/independent GBP, sales and dates | Native 50 checks includes three currency saves, future/active/expired windows, retained expiry after native cron clears dates; browser actual owner save | Passed isolated; existing live price records unchanged |
| R5 coupon toggles, schedules and product scope | Native count/discount-line exclusion, future/expired refusal/no advertisement; browser actual rules form save | Passed isolated; live current codes/percentages unchanged |
| R6 transparent total savings on tile/product | Native sale+coupon=19%; live Max annual save60 (6.7%) now, total144 (16%) vs12 monthly purchases with TOOLKIT10/4 units; phone card screenshot inspected | Passed |
| R7 USD outside UK, UK GBP; manual PKR retained | Native mapping and existing24 currency regressions; live fresh USD and remembered manual PKR | Passed rule and current-client tests; physical UK browser traffic not tested |
| R8 preserve commercial records/settings | Pre/post exact hashes: 88 products, three historical orders, coupon records/config, rate, gateway settings unchanged; all12 runtime source hashes match | Passed deployment audit |

Evidence: outputs/offer-controls-native.json (50), offer-controls-currency.json (24), offer-controls-browser.json (29), offer-controls-live.json (28). Live owner PHP render/readback:19 checks. Browser workflows use an isolated live-database clone with mail interception and an internal Docker network. No real order/payment/account/email was submitted on production. Live anonymous carts only were used for coupon verification.

Recovery: /opt/instapass/recovery/offer-controls-deploy-20261019T131951Z contains eight original runtime files, original/candidate hash manifests and before/after commercial-state audits. Four newly added files are offer-manager.php and offers.css/offers.js/offer-savings.css. Roll back code using those exact originals/new-file records, preserving any products/orders created subsequently; never restore an old database merely to undo this UI release. First preflight-only backup131104Z is historical, not the final candidate.

Current test closeout: dedicated containers/network/upload volume, tmpfs database, known short-lived files, disposable credentials and tunnel removed through exact-name guards and nonrecursive cleanup. No production export was retained for this test. The older guest-commerce snapshot previously blocked by Desktop Commander's recursive-delete policy was outside this release's cleanup scope.

Limits: standalone combination offers have owner-managed inventory/fulfilment and count as one cart unit. Current owner editor targets simple digital offers, matching the live catalog; the native advanced product editor remains available. Current three managed coupon slots are configurable; no automatic recurring charge or provider account provisioning was added. Payment configuration, actual restock email delivery, refunds and full-store launch acceptance remain separate work.

## Owner product controls — 2026-10-20

Live Control Center 0.7.3 adds radio-based Active / Deactivated / Private status, radio stock availability, optional quantity tracking, Monthly / 6 months / Annual / Other tenor choices, separate full-term USD/PKR/GBP base and sale prices, and a live price preview. The catalog shows Base USD, Current USD and Tile saving; each product row offers one-click Activate/Deactivate and In stock/Out of stock controls, plus checkboxes for bulk Activate/Deactivate. Tenor shortcuts create a separate draft with the same product family/logo and blank prices, and Save & add another tenor remains available. Existing order history is preserved on deactivation.

Verification: 59 isolated PHP/WooCommerce checks and 35 isolated browser checks passed, including optional quantity persistence, refusal to mark zero tracked stock in stock, mobile/desktop layout, price preview/tile savings, tenor draft, radio groups, quick status and stock actions, bulk activation, stale-write refusal, same-product quantity coupons and guest checkout. Read-only live PHP render passed 22 checks for runtime version, dashboard fields, quick action forms, tenor links and price columns. All 12 candidate runtime paths passed source-hash validation; exactly four runtime files differed from the live versions: the Control Center entrypoint, offer manager, and offer-editor CSS/JS. Before/after audit hashes match exactly for 90 catalog products, four orders, coupon configuration, currency settings and payment gateways. Recovery: `/opt/instapass/recovery/offer-controls-deploy-20261020T163018Z`. No live offer, order, coupon, currency, price, stock, or payment setting was edited as part of this UI deployment.

Test-scope note: browser workflows mutate only the isolated cloned database. No live admin action was clicked or real order/payment submitted. The live check is a read-only render; browser interaction was exercised against the isolated clone.
