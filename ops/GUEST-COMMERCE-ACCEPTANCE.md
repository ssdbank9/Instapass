# Guest commerce acceptance record - 2026-10-19 (Asia/Karachi)

User scope: fix guest checkout, promo code, order-number-and-email tracking, manual payment matching, and the identified email/price gaps. New emails may use first-order promos. Accounts remain optional. Keep intentional Price on request listings unpriced.

| Requirement | Implementation / evidence | Status |
| --- | --- | --- |
| Guest order without account | Real classic checkout in clone; customer_id=0, on-hold | Passed; live fields/settings also verified |
| Receipt email and optional different activation email | Checkout fields, order metadata, owner view and receipt | Passed isolated order and owner browser |
| Optional accounts | My Account registration; emailed password setup; auto-auth disabled | Passed isolated signup and live field check |
| Existing promo works for guests | TOOLKIT5, two products; native percentage arithmetic | Passed isolated and live anonymous cart |
| First-order rule per receipt email | Historical implementation only | Superseded by owner decision: no first-order restriction; count-based bundle offers apply to guests and returning customers |
| New email remains eligible | Historical first-order test case | Superseded; current policy does not limit discounts by first order or email |
| Order number plus email status | /track-order/, limited status, POST/nonce/throttle/no-cache | Passed wrong/correct email, state changes and live negative lookup |
| Private order/payment link | Emailed only to saved receipt email; not rendered in public tracking | Passed; no keys/reference/screenshots in tracking result |
| Payment matching | Private-key form, reference/date/method/optional screenshot, owner notification | Passed actual HTTP form; pending until owner verifies |
| Duplicate transfer protection | Global normalized reference across methods, parallel requests, safe retry | Passed sequential and simultaneous requests; one winner, no duplicate email |
| Owner payment confirmation | Owner action updates processing/paid state and sends receipt | Passed simulated clone only, no real financial transaction |
| Currency-appropriate proof methods | USD Binance/crypto, configured PKR-only method rejected for USD | Passed UI and forged POST rejection |
| Unconfigured receiving methods | Bank/EasyPaisa receiving details absent (NayaPay removed); GBP methods absent | Awaiting owner input; kept disabled |
| Email identity and replies | Generic wc_mail now defaults Reply-To support, preserves explicit headers | Focused 2 tests pass; actual Gmail inbox verifies From orders, Reply-To support, SPF/DKIM/DMARC pass |
| Catalog and historical orders preserved | Original product/order fingerprints match pre/post | Passed: 20 priced, 65 intentionally unpriced, 3 original orders |
| Public health and phone layout | Tracking, optional signup, populated checkout, coupon, contact link, Wasooli | 18 live browser checks pass; phone screenshots inspected |
| Actual funds and license provisioning | No real payment submitted; no license activated | Outside this verification; owner must verify real receipts |
| Recovery | Fresh backup hashes, full DB restoration and integrity, targeted code copies | Passed 58-table isolated restoration; full application restore not claimed |

Evidence: outputs/guest-commerce-browser-verification.json (29), guest-commerce-concurrency-verification.json (9), guest-commerce-coupon-editor-verification.json (4), server integration receipt (11); 53 isolated checks total. Header regression has 2 additional focused checks. Live public browser checks: 18. Sanitized Gmail header evidence: guest-commerce-email-verification.json. Source/deployed hashes compared separately.

Backup: /opt/instapass/recovery/pre-commerce-20261018T184849Z. Targeted deployment recovery: guest-commerce-deploy-20261018T185600Z. Reply-To follow-up: guest-commerce-mail-20261018T190003Z. All are root-protected and excluded from Git. Tests used an internal Docker network, intercepted all fixture mail, read-only production files and ephemeral logs/uploads. No live order/payment/account was submitted; only anonymous cart/coupon checks and two clearly labeled internal test messages to the owner.

The first-order rows above describe a superseded policy. Current bundle offers are TOOLKIT5 (5% at 2 distinct product/tenor selections), TOOLKIT75 (7.5% at 3) and TOOLKIT10 (10% at 4); no first-order or per-customer limit applies. See ops/BUNDLE-OFFERS-ACCEPTANCE.md and ops/THREE-PRODUCT-OFFER.md for the later acceptance records.

Final phone-copy follow-up: optional marker is rendered once; payment steps point to the private proof form; receipt/activation help is explicit. Guest-checkout.php is additionally backed up at guest-commerce-copy-20261018T190834Z. Five final source hashes match production.


## Order date added - 2026-10-19

The matched order result now includes the WooCommerce order creation date, formatted using the WordPress site date format/timezone. It remains behind the existing order-number plus billing-email match and does not expose activation credentials or payment evidence. Updated the browser regression to assert that a matched result contains an order date. Candidate and live plugin PHP syntax checks passed; the single plugin file was deployed atomically with a saved pre-change copy at `/opt/instapass/recovery/order-date-before-20261019.php`. The public `/track-order/` page returned HTTP 200 and rendered both lookup fields. No real order was submitted or read for this test. The new browser assertion remains unverified: the isolated suite stopped in its setup before browser launch because its stale fixture calls removed `ip_guest_first_order()`. Existing successful acceptance remains separate from this incomplete focused regression.


## Follow-up: order date and discount display — 2026-10-19

The matched lookup result now displays the order date and, for discounted orders, coupon code(s) plus the saved discount amount using the order's currency. Isolated successful-match browser evidence now exists: all 31 browser checks pass. The synthetic order displayed “Discount applied (qa-tracking-discount): –$3.20”; correct/wrong email, private link, refreshed payment status and phone/desktop widths also pass. The plugin was atomically deployed with backup at /opt/instapass/recovery/order-discount-before-20261019.php; runtime SHA-256 is eb8cd470bbcbed1d81ed360270adca6e934cc966a24aeb1de631c9b2969cdcb2. Live page HTTP 200 and PHP lint pass. This verifies code and synthetic behavior; no live customer discounted order was created.

A current read-only audit confirms the three configured offers (5%, 7.5%, 10%), guest checkout and optional account settings, and shows payment methods without configured bank/PKR receiving instructions remain unavailable. Restock notifications are enabled and sender verified, but there are zero subscribers and no customer delivery proof. The dedicated isolated containers/network, tunnel and disposable credentials were removed. The timestamped database/code snapshot remains at /opt/instapass/recovery/guest-commerce-test-20261019T114301Z because Desktop Commander rejected recursive deletion under its safety gate; it must be cleaned up with an approved safe removal method.
