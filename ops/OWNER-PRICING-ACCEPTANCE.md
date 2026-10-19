# Owner pricing authority - live acceptance, 2026-10-19

Control Center **0.6.0** and Tenor Offers **0.2.3** are live. The owner controls exact full-term USD prices, an editable USD-to-PKR rate, automatic or independent fixed PKR prices/discounts, and fully separate GBP prices/discounts. No commercial prices or payment configurations were changed by this controls release.

## Evidence

| Check | Result / boundary |
| --- | --- |
| Native controls | 44 passed in network-isolated MariaDB clone: authenticated routes, exact USD, selected/global/random discounts, mixed three-currency imports, PKR overrides/reset/undo, stale USD/rate refusal, journal/partial failure/lock handling, native PKR discounted order, monthly best configured offer. |
| Currency/checkout regression | 24 passed: country defaults, manual USD override, independent GBP, PKR rounding/rate, current quote guard, native orders and immutable historical amounts. |
| Generated description helpers | 14 passed, including owner-edited prices, fixed PKR, blank GBP, preservation of custom prose and attributed excerpt classes. |
| Actual wp-admin browser | 27 passed using a synthetic administrator in an isolated HTTP clone: Excel download/upload/apply/undo, PKR auto/fixed mode, native product editor discount save/clear, actual rate form save/revert, unsupported input/nonce rejection, phone/desktop layout and no uncaught JS errors. |
| Live owner readback | 10 passed: version, 88-row catalog, Max840/automatic235200PKR, Plus fixed47500PKR, blank GBP, actual rendered owner controls, native fields and rate panel. No live price batch was applied. |
| Live anonymous storefront | 36 passed after excerpt repair: annual70/month840upfront, all bundle tiers, full-term checkout1368.30, guest checkout, one code, taxes-covered copy, fixed PKR, missing GBP, current descriptions, phone/card bounds and exact public JS hash. No order/payment/email was submitted. |
| Financial/catalog audit | All 88 full product records, three historical full orders, rate/coupon/tax/registration options, coupons and gateway settings byte-identical before/after source deployment and excerpt repair. |
| Backup restore | pre-commerce-20261019T074512Z: 58 tables restored, integrity passed, application archive readability passed. Full application recovery is a separate acceptance check. |

The country mapping/resolver/manual override were verified in the native clone. We did not claim a live session physically located in the UK or Pakistan. Runtime currency selector and selected-currency storefront behavior were verified live.

## Recovery and source integrity

- Main controls recovery: /opt/instapass/recovery/owner-pricing-20261019T074752Z.
- Excerpt repair recovery: /opt/instapass/recovery/owner-pricing-copy-20261019T075352Z.
- Isolated test receipt: /opt/instapass/recovery/owner-pricing-test-20261019T074142Z.
- Seven old production source hashes were checked before any install; uploaded new files were hash-verified and staged PHP linted. Final runtime files match their staged source. The initial0.2.2 description hook missed the theme's attributed excerpt;0.2.3 repairs that hook, verified by the final36-check live run.
- Full unchanged catalog hash: d2788503d7d15798f924972c22dc22d757e0087bd12df949cc1114294d228dbb.
- Full unchanged historical order hash: b61bd1d6d99c384a91ebfdd1e0b910d71d28e36f5355802ad3c958735e5d72af.

## Scope and operating notes

Enter full-term prices; the website calculates monthly equivalents and current eligible bundle figures. PKR batches create fixed overrides and say so before Apply; Restore automatic PKR removes those overrides. Exact owner USD amounts are accepted without a listing exemption. Optional generated .99 rounding can change the displayed actual discount percentage, visible in preview. Existing USD sale schedules are retained.

GBP has no exchange-rate fallback. Its prices remain owner-entered and currently unfilled where previously blank. Existing UK default/manual USD selector remains. Existing bundle coupon thresholds remain2/5%,3/7.5%,4/10%; these are editable in Products > Promotions. Coupon discounts apply to current effective product prices, including sales, and do not stack with another coupon.

Historical descriptions/receipts retain their dated evidence. The latest Max annual decision is840USD,70monthly base and756/63at eligible10%, superseding the earlier62.50target. This release does not reprice those offers.

This is acceptance of pricing controls and the stated customer flows, not a whole-store launch or payment-provider eligibility assessment. Existing unrelated Wasooli TLS finding is unchanged; its service and Caddy were not modified. No real customer messages or payment transactions occurred. Owner guide: ops/OWNER-PRICING-GUIDE.md.

## Closeout

The scoped test containers and internal network were removed; only restricted recovery receipts remain. The synthetic browser credential was removed from its exported server location and local workspace, and the verified19058test tunnel was stopped. No source/plugin changes were made after the final live36checks apart from documentation. Code remains a scoped local Git release; no remote push is implied.

A final Git whitespace check removed one extra EOF blank line from monthly-pricing.php. The same source was installed only after verifying rtrim-equivalence with runtime, PHP lint and SHA256 readback; no executable behavior changed. The initial manifest is the initial deployment input; the guarded excerpt repair and EOF receipt record the two subsequent scoped source updates.
