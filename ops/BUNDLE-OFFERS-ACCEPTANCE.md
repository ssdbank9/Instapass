# Bundle offers acceptance - 2026-10-19

Owner requirement: replace first-order promotions with two coupons: 5% for two products and 10% for four products in one order. Existing distinct-product counting is preserved. Codes are TOOLKIT5 and TOOLKIT10; thresholds are minimums, apply to the current basket prices, and never combine.

| Requirement | Implementation / evidence | Status |
| --- | --- | --- |
| Two products, 5% | Promotions slot 0; two 10.99 fixtures discount 1.10, total 20.88 | Isolated integration passed |
| Four products, 10% | Promotions slot 1; four 10.99 fixtures discount 4.40, total 39.56 | Isolated integration passed |
| Same transaction | Native WooCommerce basket/order calculation | Isolated order persistence passed |
| Guests and returning customers | Removed first-order controls; unlimited per-customer bundle use | Seeded paid-email integration passed |
| No stacking | Individual-use native coupons; only one coupon remains after replacement | Integration passed |
| Minimum distinct products | One product rejects 5%; quantity four of same product and three distinct products reject 10% | Integration passed |
| Owner self-service | Products > Promotions has two independent forms, enabled/code/percentage/threshold fields, nonce/capability/stale-state protection | Browser verification pending |
| Preserve existing checkout/tracking/payment flow | Guest Orders 0.2.0 only removes first-order restrictions and links to bundles | Source diff reviewed; live checks pending |
| Preserve catalog and historical orders | Compare product/order fingerprints before and after deployment | Pending |

The previous first-order coupon fixtures under guest-commerce are historical evidence for the superseded policy. Current coupon acceptance uses ops/bundle-offers-integration.php and ops/bundle-offers-browser.cjs. Clone tests have no external network and intercept mail. No real payment or live order is required for this change.

Backup: /opt/instapass/recovery/pre-commerce-20261019T022242Z; archive hashes and restored 58-table database integrity passed. This is backup validation, not a claim of a full application restore exercise.

Rollback: restore only the two backed-up PHP files and the prior managed-offer settings/coupon configuration from protected recovery artifacts. Disable the newly created second offer if reverting. Preserve all subsequent customer orders, product edits and other settings; do not restore the full database to undo code. Keep root-only recovery files and synthetic fixture credentials out of Git.

## Final verification

- Published successfully with recovery copies at /opt/instapass/recovery/bundle-offers-deploy-20261019T023125Z. Installed theme Promotions and Guest Orders 0.2.0 source bytes match reviewed local uploads.
- All 16 isolated native WooCommerce integration checks and 14 actual browser checks passed. Owner disable/re-enable saves persisted independently; disabled offer disappeared from advertising. Phone screenshot inspected at 390px with no horizontal overflow.
- All 11 anonymous live browser checks passed. Two real product lines: subtotal23.98, discount1.20 with TOOLKIT5. Four real product lines: subtotal248.96, discount24.90 with TOOLKIT10, total224.06. Three distinct products reject TOOLKIT10. One coupon remains; guest signup stays optional. Storefront and Wasooli return200. No live checkout submission, account, payment or email was created.
- Product fingerprint before/after: 9b30817dd5cc513d7730eda3b03dc2d21e9605adaf8e157d619d5c57d719832c; 20 priced and 65 intentional Price on request listings. Three historical orders retain fingerprint0887a8418822bd718190c8ea1449481db1472341168f2993356d3ad884a1694a.
- Initial Pending rows above are resolved by these final checks. Sanitized browser receipts: outputs/bundle-offers-isolated-verification.json and outputs/bundle-offers-live-verification.json; phone screenshots alongside them.
- Dedicated isolated containers/network and SSH tunnel removed; temporary local/server synthetic credentials removed. Root-protected backup/test/deployment records retained. Existing unrelated infrastructure was preserved.
- No new provider cost. This verifies USD coupon operation and the changed owner controls; it does not certify actual fund receipt, provider settlement, license delivery or enable any unconfigured payment method/GBP price.

## Any product, any tenor clarification

Owner clarified that any product and any tenor may qualify. Live read-only verification on 2026-10-19 confirms both coupons have no product/category inclusion or exclusion lists and do not exclude sale items. Installed promotion source SHA256 ee0c8b0ec676b56d5b54b67352153a4c76706afe31631cbb152f2f4e600733fc matches reviewed local code: eligibility counts purchasable in-stock parent product IDs and never tests subscription duration. Customers can mix any available tenors. Existing thresholds remain two/four distinct products; quantities or multiple variations of one parent count once. No checkout setting, price, order or coupon was changed for this verification.
