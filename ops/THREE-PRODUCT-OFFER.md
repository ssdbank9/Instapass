# Three-selection bundle offer - 2026-10-19

Owner requirement: add7.5%off for three products alongside existing5%for2and10%for4. Existing rules remain: distinct product/tenor selections, same-family/same-tenor repeats count once, guests and returning customers qualify, one coupon per order.

| Requirement | Implementation / acceptance |
| --- | --- |
| Three selections receive7.5% | TOOLKIT75, native percentage coupon, minimum3 distinct selections |
| Existing5%/10% unchanged | Preserve existing option identities, coupon IDs, settings and usage; configure only new third slot |
| Owner can edit/disable | Three independent forms under Products > Promotions, including decimal percentage |
| Helpful display order |2,3,4selection banners and owner forms; established slots0/1 retained, new slot2 |
| No stacking | Native individual-use coupons; eligibility revalidated on cart changes |
| Correct money and persistence | Native isolated cart/order tests, two-selection rejection, guest and returning email usage |
| Verified publication | Fresh restore-checked backup, source guard, scoped recovery, product/order fingerprints, anonymous live cart/browser check |

Status: LIVE. TOOLKIT5 gives 5% for at least two distinct selections, TOOLKIT75 gives 7.5% for at least three, and TOOLKIT10 gives 10% for at least four. One coupon per order; these are not first-order offers.

Verification:
- 31 native isolated integration checks and 28 isolated browser checks passed, including persisted owner disable/enable and decimal percentage saves.
- Eight focused public live checks passed: the middle offer rejects two selections, applies 7.5% to three, and can be replaced by the four-selection offer without stacking. Three-product subtotal $98.97, discount $7.42, total $91.55. Guest checkout and 390px layout passed.
- The broader health report retains a failure: fresh Chromium with TLS validation enabled reports ERR_CERT_AUTHORITY_INVALID for the separate Wasooli cohost. No Wasooli or Caddy changes were made. This is not a claim that the cohost passed.
- Both established coupon configurations and native usage data were preserved. All 85 product price/stock records and three historical orders retained their exact pre-deployment fingerprints.
- Fresh pre-commerce-20261019T033727Z database backup restored with 58 tables and integrity checks; this does not prove a full application disaster recovery.
- Guarded source deployment matched staged bytes; recovery: /opt/instapass/recovery/three-product-offer-20261019T042805Z.
- Dedicated isolated resources, synthetic credential file and tunnel removed; remaining test listeners: zero.
- No live order, payment, account or customer email created.

Evidence: outputs/three-product-live-verification.json and outputs/three-product-live-390.png (local QA artifacts). Rollback restores the three source files from the scoped recovery directory and disables only the new managed offer; preserve actual orders and coupon usage rather than restoring the production database.