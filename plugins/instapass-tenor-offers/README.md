# Instapass Tenor Offers 0.1.1

A tenor is a separately priced simple product listing. Link the same product and plan by entering an identical readable name in General > Same product / other tenors. Existing Access duration, USD/GBP prices, discounts, stock and restock controls remain per listing. Keep drafts until terms/prices are confirmed. Product families are explicit; names are never automatically matched.

Published, catalog-visible family offers with an access duration appear as duration choices on product pages. Priced in-stock choices add directly to native checkout and preserve other basket items. Sold-out and quote-only offers show availability without a buy button. Hidden/draft listings stay absent. Duration is displayed in cart and retained in order-item metadata. Current block theme's product excerpt renders the choices.

Bundle counting is family+normalized tenor, with independent simple product ID fallback when family/duration is unset. Different tenors qualify separately. Repeated quantities, duplicate same-tenor listings and1year/12months aliases count once. Different families/plans stay distinct. This extends the theme's 5%/7.5%/10% native coupon validation without changing amounts, stacking, currencies, accounts or payment matching.

Owner guide: Products > Subscription Tenors. Use native WooCommerce Duplicate to prepare another tenor as draft, adjust title/duration/prices/stock/terms, then publish deliberately. No new commercial tenor or price is inferred.

Rollback: deactivate this module and restore the backed-up Promotions PHP and Guest Orders PHP if undoing the new coupon wording/counting. Existing per-listing prices/orders are preserved; family metadata may remain harmlessly. Do not restore a whole production database to undo plugin code.

Current implementation uses simple listings because the live currency editor and restock module target those. It does not convert any live listing into a variable product or claim a variable-product payment/GBP/restock workflow.
