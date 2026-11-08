# Max and Ultra term pricing — 8 November 2026

Owner-authorized data-only update. No runtime plugin/theme code or payment integration changed. Existing currency/pricing controls perform the calculations.

| Offer | Months | Monthly equivalent in USD/GBP/EUR | Full upfront amount in USD/GBP/EUR | Automatic PKR upfront |
|---|---:|---:|---:|---:|
| Claude Max 5x | 1 | 75 | 75 | 21,000 |
| Claude Max 5x | 6 | 72 | 432 | 121,000 |
| Claude Max 5x | 12 | 69 | 828 | 231,800 |
| Claude Ultra 20x | 1 | 115 | 115 | 32,200 |
| Claude Ultra 20x | 6 | 110 | 660 | 184,800 |
| Claude Ultra 20x | 12 | 105 | 1,260 | 352,800 |

The monetary numbers are independent prices in each fiat currency, not conversions from USD to pounds/euros. PKR uses the current editable rate280 and rounds the full-term product unit to the nearest100; percentage coupon totals retain their proper minor units. Thus a rounded PKR monthly equivalent may differ slightly from multiplying the advertised USD monthly equivalent by280.

Requirement scope: IDs31/139/140 (Max) and32/149/150 (Ultra) priced and approved. Native/USD and independent GBP/EUR sales cleared, previous sale dates reset, fixed PKR overrides cleared to automatic, coupons allowed, obsolete pricing-hold paragraph replaced only where its exact original text appeared. Twelve backend duration offers now approved across four AI tiles; Canva and both credit tiles remain on hold. Seven visible tiles retained. The single TOOLKIT5 policy remains5% for2or more eligible quantities, including repeats. Historical coupon records retained.

Backend:47 live native assertions pass for mapping/readiness, all currency amounts, cleared sales/dates, automatic PKR, coupon eligibility, twelve approved offers and the unchanged single bundle. Migration verifies exact original hashes of non-target products and historical orders; checks each target's stock settings and preserves rate/promotion/payment options. Production shop and cohosted Wasooli health probes pass. Raw release output:outputs/max-ultra-release.txt. No real order, customer details, payment or email submitted. This routine price/configuration change uses the existing previously tested runtime; it did not create a new isolated clone.

Live verification: 184 Chrome browser assertions via Desktop Commander pass across USD, GBP, EUR and PKR. All six selected duration panels show the right monthly equivalents and full-term totals, correct buy links, loaded logos, and no pricing-hold text. Pro/Plus prices and the three remaining held tiles are checked. Seven-tile layout and mobile/desktop widths pass. Annual Ultra detail pages show the right selected term and no obsolete pricing-hold paragraph. Actual guest carts containing Max monthly plus Ultra monthly accept TOOLKIT5 and total180.50 in USD/GBP/EUR or50,540 in PKR; signed quote fields remain present. No Place order action or customer details entered. Report:outputs/max-ultra-browser.json. Screenshots inspected:outputs/max-ultra-live-tiles.png, outputs/max-ultra-max-mobile.png and outputs/max-ultra-ultra-mobile.png. PHP lint and JavaScript/Python syntax checks pass. No temporary clone or tunnel was created in this installment.

Recovery: /opt/instapass/recovery/max-ultra-pricing-20261108T050109Z contains the protected database snapshot, scoped before-state and native-result report. Scripts:ops/max-ultra-pricing.php and ops/max-ultra-release.py. Apply refuses mapping/readiness/rate drift and a reused backup; failure restores only touched prices/descriptions/meta and the prior launch mapping. Later owner edits require fresh review before rollback. Do not restore a whole old database over newer orders.

Remaining: confirm Canva and credit-selling prices; payment collection and provider fulfilment/expiry remain the separate pending phases.
