# Monthly price presentation and taxes-covered policy - 2026-10-19

Owner clarified: retain Claude Max5x annual833.33, approximately750after eligible10%discount, equivalent62.50permonth. Do not switch to the suggested65monthly target. Primary monthly figures help compare durations; full-term paid-upfront totals must remain visible.

| Requirement | Implementation / evidence |
| --- | --- |
| Monthly pricing on selected subscription cards and pages | Tenor Offers0.2.1 monthly-pricing.php, selected currency, explicit monthly-equivalent label |
| Full upfront total clear | Same price block; duration cards and detailed bundle breakdown; checkout keeps native full-term total |
| Work back all bundle tiers | Live owner coupon config:2offers5%,3offers7.5%,4offers10%; one code, required selection count shown |
| Correct Max annual equivalents | Base69.44;5%65.97;7.5%64.24;10%62.50 per month |
| Full-term corresponding amounts |833.33;791.66;770.83;approximately750.00; native basket allocation can move a line by one cent |
| Honest bargain comparison | Compare against current same-family one-month price in selected currency; do not invent public provider savings |
| Tax absorption wording | Applicable taxes are covered by Instapass. No extra tax at checkout. Owner policy; existing Woo calculation off/zero tax rates verified and kept |
| No monetary/schema mutation | Display filter only outside cart/checkout/accounts/admin; native price getters, Store API, orders and payment settings unchanged |
| PKR override and GBP | Divide existing fixed/derived term price by duration; never independently convert monthly USD or invent missing GBP |
| Full site control | Product prices/duration/family and native owner coupon settings remain sources; no hardcoded promotional dollar amount |
| Persistence and verification |30isolated native checks passed;31live checks passed including full-term payment, tax, coupons, currency and actual card bounds |

Presentation is limited to linked simple offers with a confirmed month/year duration. Unspecified durations and day/week offers are left as saved. Monthly figures are comparison equivalents, not automatic monthly billing. Customer-facing tax copy does not claim taxes have already been remitted, and this release does not set tax rates or tax-exempt customer statuses.

Released source recovery:/opt/instapass/recovery/monthly-pricing-20261019T050834Z. Runtime files match SHA256-verified staging; all catalog price/stock, order, coupon, receiving-method, currency and tax audit values unchanged.

Fresh backup:/opt/instapass/recovery/pre-commerce-20261019T050711Z,58tables restored and database integrity passed; no full application disaster recovery is claimed.

LIVE and verified.31live anonymous browser checks pass at390/1440px, including main price, all3coupon tiers, exact full-term checkout total, no added tax row, optional accounts, PKR47500/3958.33equivalent and missingGBP held. The initial page-width-only checks missed a clipped native max-content card wrapper;0.2.1fixes it and adds element-level bounds/scroll assertions. The final phone card screenshot was visually inspected and all upfront/coupon details are visible. CSS repair recovery:/opt/instapass/recovery/monthly-card-wrap-20261019T051211Z; financial audit unchanged. Evidence:outputs/monthly-pricing-live-verification.json,monthly-annual-card-390.png,monthly-annual-390.png,monthly-annual-1440.png and monthly-checkout-390.png. Dedicated isolated containers auto-cleaned. No live order/payment/customer email created.

Rollback: restore the Tenor plugin files from monthly-pricing-20261019T050834Z to remove the new display feature, or restore only the two files in monthly-card-wrap-20261019T051211Z to undo that CSS repair. Preserve actual orders, prices, coupon use and tax settings; no database restore is needed for this presentation-only change.
