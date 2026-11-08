# Instapass Tenor Offers 0.3.0

A tenor is a separately priced simple product listing. Link the same product and plan by entering an identical readable name in General > Same product / other tenors. Existing Access duration, USD/GBP prices, discounts, stock and restock controls remain per listing. Keep drafts until terms/prices are confirmed. Product families are explicit; names are never automatically matched.

Published, catalog-visible family offers with an access duration appear as duration choices on product pages. Priced in-stock choices add directly to native checkout and preserve other basket items. Sold-out and quote-only offers show availability without a buy button. Hidden/draft listings stay absent. Duration is displayed in cart and retained in order-item metadata. Current block theme's product excerpt renders the choices.

Bundle thresholds count purchased eligible units, including repeats of the same product and tenor. Two monthly units of one product qualify for the two-item offer. Family/duration grouping remains for linked duration choices, not coupon deduplication. Current5%/7.5%/10% offers retain one-code-per-order, optional accounts and existing payment matching.

Owner guide: Instapass > Products & Offers. Create or copy a draft and choose title/duration/logo/prices/discounts/stock/terms in one form, then publish deliberately. The native product editor remains available. No new commercial tenor or price is inferred.

Rollback: deactivate this module and restore the backed-up Promotions PHP and Guest Orders PHP if undoing the new coupon wording/counting. Existing per-listing prices/orders are preserved; family metadata may remain harmlessly. Do not restore a whole production database to undo plugin code.

Current implementation uses simple listings because the live currency editor and restock module target those. It does not convert any live listing into a variable product or claim a variable-product payment/GBP/restock workflow.

## Monthly price presentation

Linked simple offers with confirmed month/year durations show a monthly equivalent and the full prepaid-term amount on catalog cards, product pages and duration choices. Keep entering the full term price in Products > Edit > General; do not replace annual prices with monthly figures. Cart, checkout, Store API values and saved orders keep full-term native prices. GBP remains independent; missing GBP is never converted. Fixed PKR overrides are divided from the saved full-term PKR amount, without a second conversion or rounding the monthly value to Rs100.

Multi-month product pages show current eligible bundle tiers from owner coupon configuration, required item counts including repeated quantities, monthly equivalents and upfront totals. One code per order; native line rounding remains. Annual Max833.33 shows69.44/month at base,65.97with5%,64.24with7.5%,62.50with10%. A comparison against a published one-month family offer uses its current selected-currency price, not an invented vendor price. No automatic recurring billing is introduced.

The owner-approved tax policy reads: Applicable taxes are covered by Instapass. No extra tax at checkout. Existing tax calculation is disabled with no tax rates; this release leaves all tax settings unchanged and does not claim prior remittance or tax-registration status. Customer invoices and historical orders are not recalculated.

The0.2.1card fix constrains native max-content price wrappers and wraps the monthly unit so prepaid totals and coupon conditions stay visible inside phone cards. Verification and recovery:ops/MONTHLY-PRICING-ACCEPTANCE.md.

## Owner pricing authority (0.2.3)

The displayed best bundle now selects the lowest actual current full-term amount, rather than assuming the last configured offer is always strongest. The three original generated release price paragraphs refresh from the current selected-currency price so future owner edits cannot leave the old USD amounts visible. Only the recognized original paragraph is refreshed; custom owner-written copy and stored descriptions remain untouched. Missing GBP shows no stale USD total. No cart/order arithmetic is replaced. Verification: ops/OWNER-PRICING-ACCEPTANCE.md.
