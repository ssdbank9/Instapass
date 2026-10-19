# Claude Max5x annual70/month base - 2026-10-19

Owner accepted a70USD monthly equivalent for the annual plan,840paid upfront. Native bundle rules stay2different offers/5%,3/7.5%,4/10%,one code per order. Annual equivalents:66.50monthly/798upfront at5%;64.75/777 at7.5%;63/756 at10%. This supersedes the earlier833.33/62.50target. No automatic monthly billing.

Scope: only product140,verified SKUinstapass-claude-max-5x-annual and release marker. Update its USD regular/current price and its two public price-copy fields. Preserve monthly75, six-month432, Plusannual173.33/fixed47500PKR, Pro/Max20, stock, GBP fields and PKR override fields. PKR derives235200annual at rate280,19600/month; GBP stays unpriced until owner enters it.

Requirement/acceptance: guarded idempotent apply; capture before fields and protected87product/order/settings hashes; isolated native coupon/cart/order persistence; live USD/PKR/GBP display and checkout; historical order preservation; current executable price fixtures updated, historical dated receipts retained.

35isolated checks passed, including actual annual line totals798/777/756 under each native coupon. Backup:pre-commerce-20261019T072112Z,58restored tables/database integrity passed. Full app disaster recovery not claimed. LIVE.31phone/desktop live checks passed:70monthly840upfront;66.50/798,64.75/777,63/756tiers; native four-offer checkout1368.30; selected-currency and tax-covered behaviour preserved.87other product records and all3historical orders/options/gateways/coupon data retained exact hashes. Recovery:/opt/instapass/recovery/max-annual-seventy-20261019T072416Z. No real customer order/payment/email. Current pricing browser receipt:outputs/monthly-pricing-live-verification.json.
