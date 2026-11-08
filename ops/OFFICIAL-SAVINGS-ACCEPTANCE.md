# Official savings and Pro annual acceptance — 2026-10-19

LIVE Control Center0.7.2, Tenor Offers0.3.1 and scoped FAQ. Six runtime hashes match candidate. Code recovery:`/opt/instapass/recovery/official-savings-deploy-20261019T143333Z`. Approved Pro catalog recovery:`/opt/instapass/recovery/pro-offer-20261019T143248Z`.

| Requirement | Evidence | Result |
| --- | --- | --- |
| Savings percentages nearest whole number | Half-up boundary, actual offer/card checks; other regular-price savings also rounded | Pass |
| Savings above offer prices | Native HTML prefix and mobile/desktop DOM order checks | Pass |
| Correct official100/20/20USD monthly benchmarks | claude.com/pricing; OpenAI Plus help6950777; confirmed SKU/family mapping | Pass |
| Do not apply Max5x reference to20x | Native exclusion check | Pass |
| Annual comparison uses equal-duration monthly payments |12times monthly list; source/period/annual-offer distinction visible | Pass |
| Exact prices and7.5%coupon retained | Native rate check, currency regressions and live Max4unit total270 | Pass |
| Dynamic PKR reference and independent GBP | Fixed/autoPKR plus owner rate edits; GBPUSDreference only, no inventedGBPpercentage | Pass |
| Tax absorption and additional savings | Product tax note and opened FAQ; no assumed tax rate included in saving percentages | Pass |
| Pro1month and1year | Owner-confirmed monthly metadata; new annualSKUclaude-pro-annual, liveID142 | Pass |
| Pro annual matches Plus |173.33USD,47500PKRfixed, GBPblank; after eligible10%156USD | Pass |
| Existing Pro monthly prices preserved |11.99regular/8.99sale unchanged; owner informed annual costs more than12current-sale purchases | Pass |
| Empty image field remains editable | Normalize empty getter to0; clone/save native regression | Pass |
| Preserve unrelated catalog/orders/settings | Approved catalog step changes onlyPro30metadata plus annual142; code step preserves all89records and3orders | Pass |
| No live order/payment/email for verification | Browser route blocks checkout submission; tests use product/card/FAQ/cart only | Pass |

Evidence:60native checks in`outputs/official-savings-native.json`;24currency regressions in`outputs/official-savings-currency.json`;59isolated browser checks in`outputs/official-savings-isolated.json`;59live checks in`outputs/official-savings-live.json`. Mobile/desktop screenshots inspected. The isolated database used tmpfs, an internal network and blocked outbound mail/HTTP. Current test containers/network/media volume/remote synthetic credentials/tunnel removed; no fixture credentials were copied to the PC.

Current USD savings before coupon / with eligible TOOLKIT10: Max5xmonth25%/33%; Max6months28%/35%; Maxyear30%/37%; Plusmonth25%/33%; Plusyear28%/35%; Promonth55%/60%; Proyear28%/35%. Percentages are rounded display values, money remains exact. Four eligible items required forTOOLKIT10; repeated quantities count. Tax savings are additional only where provider checkout would add tax and are excluded from these percentages. Official provider annual offers may differ from the monthly-payment comparison.

Commercial audit after code deployment and all live checks:89catalog records, hash`f4f3f92077910a822b42a6dd1edabf2ca708596fe2b08d938483b778418f1064`;3orders hash`b61bd1d6d99c384a91ebfdd1e0b910d71d28e36f5355802ad3c958735e5d72af`; original rate/settings/gateway/coupon hashes unchanged. The Pro setup wrapper separately compares every original product except explicitly approvedPro30 and permits exactly one new annual listing. It verifies monthly prices and all order/option/gateway/coupon hashes remain unchanged.

Rollback: restore the six original runtime files from code recovery. To undo the approved Pro catalog addition, retain any new orders and review the protected before/result receipts; restore only Pro30duration/family metadata and retire annual142 instead of restoring an old database. This release does not establish full-site launch acceptance or provider activation/tax remittance evidence.

Sources verified2026-10-19: https://claude.com/pricing ; https://help.openai.com/en/articles/6950777-what-is-chatgpt-plus .
