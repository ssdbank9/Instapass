# Account count and email inputs — 2026-10-19

Published Guest Orders0.3.1 plus FAQ. Four runtime SHA256 values match candidate. Recovery:`/opt/instapass/recovery/account-emails-deploy-20261019T145926Z`.

| Requirement | Verification | Result |
| --- | --- | --- |
| More accounts show more email fields | Explicit custom count;2/3/4selected accounts produce2/3/4visible inputs | Pass |
| Every selected account has an activation destination | Required email fields; server validation for missing/invalid/duplicate emails and declared-count mismatch | Pass |
| Preserve work when changing count | Increase2→3→4, decrease4→2, restore4; typed addresses retained | Pass |
| Exclude hidden rows | Disabled inputs absent from FormData; only chosen rows stored | Pass |
| Keep unit allocation consistent | Balanced defaults plus manual3+1,2+2,2+1+1; exact positive-unit sum required | Pass |
| Preserve saved fulfilment instructions | Five synthetic guest orders retain every selected email/type/month allocation | Pass |
| Preserve tracking privacy and discounts | Tracking has nonpersonal count/split; four-unit total remains270USDwithTOOLKIT10 | Pass |
| Mobile, desktop, checkout updates, no-script form | Browser workflows and phone screenshot inspection | Pass |
| Preserve business records | Before/after and post-live product/order/options/coupon/gateway hashes identical | Pass |

Evidence:71native checks in`outputs/account-emails-native.json`,24currency regressions in`outputs/account-emails-currency.json`,53isolated browser checks in`outputs/account-emails-isolated.json`,38live anonymous checks in`outputs/account-emails-live.json`. Live tests blocked checkout submission; actual saved-order tests used an isolated clone with external mail/HTTP blocked. A final copy-only change moves fallback instructions into a noscript element; final PHP lint/hashes and live browser checks cover that candidate. No allocation logic changed after native acceptance.

Current commercial audit retains89catalog records with hash`f4f3f92077910a822b42a6dd1edabf2ca708596fe2b08d938483b778418f1064` and3orders with hash`b61bd1d6d99c384a91ebfdd1e0b910d71d28e36f5355802ad3c958735e5d72af`; rate/settings/gateways/coupon hashes unchanged. Source publication changed no product prices, customer records or historical instructions.

Exact-name cleanup removed the current fixture containers/internal network/media volume/tmpfs DB, remote/local disposable credentials and SSH tunnel. Only production WordPress/database containers remain. No real order, funds transfer, provider activation or customer email submitted.

Rollback: restore only the four original runtime paths from the recovery manifest. Preserve all new orders and their private account-allocation metadata; do not restore an old database. Owner guide:`ops/ACCOUNT-ALLOCATION-OWNER-GUIDE.md`.
