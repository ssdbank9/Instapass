# Account allocation acceptance — 2026-10-19

Published Guest Orders0.3.0, Control Center0.7.1 and the scoped FAQ/payment confirmation wording. Recovery: `/opt/instapass/recovery/account-allocation-deploy-20261019T135938Z`. Eight runtime SHA256 values match the reviewed candidate.

| Requirement | Evidence | Result |
| --- | --- | --- |
| Four monthly units split4,3+1,2+2,2+1+1,1+1+1+1 | Native validator and real guest checkout for every partition | Pass |
| Existing/new accounts and separate activation destinations | Per-row selection/email; real saved order/private item instructions | Pass |
| Positive whole units; exact sum; valid distinct emails | Under/over/zero/negative/fraction/duplicate/invalid/type/missing rejection checks | Pass |
| Cart or duration change requires review | Signed per-line snapshot rejection checks | Pass |
| Quantities retain coupon eligibility and price | Every synthetic four-unit order:300subtotal,30discount,270total | Pass |
| Optional site account; separate receipt email | Synthetic guest orders customer_id0; separate activation fallback | Pass |
| Owner controls supported offers | Real wp-admin save enabled→disabled; fresh cart omits form | Pass |
| Scope stays with selected product/tenor | Default only confirmed original monthly Max5x; mixed live basket has one allocation block | Pass |
| Private plan persistence; limited tracking privacy | Full saved instructions on private order; summary/discount in tracking, no activation emails | Pass |
| Alternate checkout cannot bypass instructions | Store API POST rejection | Pass |
| Mobile and no-JavaScript fields | 390pixel checkout, screenshot review, no-JavaScript server form | Pass |
| FAQ published | Live200, opened answer contains all examples | Pass |
| Original commercial records preserved | Before/after/full post-browser audit hashes identical | Pass |
| Real provider activation or future month scheduling | Instructions recorded for manual fulfilment; no vendor automation implemented | Outside scope |
| Actual outgoing receipt delivery for split orders | Email item rendering covered by WooCommerce hook; no real customer mail sent | Not separately inbox-tested |

Evidence: `outputs/account-allocation-native.json`63checks; `outputs/account-allocation-currency.json`24currency regressions; `outputs/account-allocation-browser.json`60isolated browser checks; `outputs/account-allocation-live.json`14live checks. Browser tests submitted synthetic orders only in a network-isolated database clone. Live checks used anonymous FAQ/cart/checkout only and did not submit an order. Phone screenshots inspected.

Live four Max5x one-month units still total270USD withTOOLKIT10 regardless of split. Live mixed basket with two Plus monthly units totals296.98with the same eligible coupon. One Max monthly unit retains the ordinary activation flow. Prices, rates, gateways and coupon configuration were not changed by publication.

Commercial audit:88original catalog records (published/draft/private/pending/future as applicable) and3orders; original product, order, option, gateway and coupon hashes unchanged. Temporary allocation-test containers/internal network/media volume, disposable credentials, tmpfs database and SSH tunnel removed. No persistent production database export was created by this fixture. Existing older recovery directories were not removed.

This is acceptance of account allocation, not a complete-site launch or proof of third-party subscription fulfilment. Owner instructions: `ops/ACCOUNT-ALLOCATION-OWNER-GUIDE.md`.
