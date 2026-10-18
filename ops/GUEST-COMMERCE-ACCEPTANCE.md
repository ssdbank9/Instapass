# Guest commerce acceptance record - 2026-10-19 (Asia/Karachi)

User scope: fix guest checkout, promo code, order-number-and-email tracking, manual payment matching, and the identified email/price gaps. New emails may use first-order promos. Accounts remain optional. Keep intentional Price on request listings unpriced.

| Requirement | Implementation / evidence | Status |
| --- | --- | --- |
| Guest order without account | Real classic checkout in clone; customer_id=0, on-hold | Passed; live fields/settings also verified |
| Receipt email and optional different activation email | Checkout fields, order metadata, owner view and receipt | Passed isolated order and owner browser |
| Optional accounts | My Account registration; emailed password setup; auto-auth disabled | Passed isolated signup and live field check |
| Existing promo works for guests | TOOLKIT5, two products; native percentage arithmetic | Passed isolated and live anonymous cart |
| First-order rule per receipt email | Native coupon flag, paid-order lookup, native usage limit 1 | Passed integration, checkout and actual owner editor save |
| New email remains eligible | Different guest emails reuse first-order coupon after prior purchases | Passed; no IP/device/alias identity rule added |
| Order number plus email status | /track-order/, limited status, POST/nonce/throttle/no-cache | Passed wrong/correct email, state changes and live negative lookup |
| Private order/payment link | Emailed only to saved receipt email; not rendered in public tracking | Passed; no keys/reference/screenshots in tracking result |
| Payment matching | Private-key form, reference/date/method/optional screenshot, owner notification | Passed actual HTTP form; pending until owner verifies |
| Duplicate transfer protection | Global normalized reference across methods, parallel requests, safe retry | Passed sequential and simultaneous requests; one winner, no duplicate email |
| Owner payment confirmation | Owner action updates processing/paid state and sends receipt | Passed simulated clone only, no real financial transaction |
| Currency-appropriate proof methods | USD Binance/crypto, configured PKR-only method rejected for USD | Passed UI and forged POST rejection |
| Unconfigured receiving methods | Bank/EasyPaisa/NayaPay receiving details absent; GBP methods absent | Awaiting owner input; kept disabled |
| Email identity and replies | Generic wc_mail now defaults Reply-To support, preserves explicit headers | Focused 2 tests pass; actual Gmail inbox verifies From orders, Reply-To support, SPF/DKIM/DMARC pass |
| Catalog and historical orders preserved | Original product/order fingerprints match pre/post | Passed: 20 priced, 65 intentionally unpriced, 3 original orders |
| Public health and phone layout | Tracking, optional signup, populated checkout, coupon, contact link, Wasooli | 16 live browser checks pass; phone screenshots inspected |
| Actual funds and license provisioning | No real payment submitted; no license activated | Outside this verification; owner must verify real receipts |
| Recovery | Fresh backup hashes, full DB restoration and integrity, targeted code copies | Passed 58-table isolated restoration; full application restore not claimed |

Evidence: outputs/guest-commerce-browser-verification.json (29), guest-commerce-concurrency-verification.json (9), guest-commerce-coupon-editor-verification.json (4), server integration receipt (11); 53 isolated checks total. Header regression has 2 additional focused checks. Live public browser checks: 16. Sanitized Gmail header evidence: guest-commerce-email-verification.json. Source/deployed hashes compared separately.

Backup: /opt/instapass/recovery/pre-commerce-20261018T184849Z. Targeted deployment recovery: guest-commerce-deploy-20261018T185600Z. Reply-To follow-up: guest-commerce-mail-20261018T190003Z. All are root-protected and excluded from Git. Tests used an internal Docker network, intercepted all fixture mail, read-only production files and ephemeral logs/uploads. No live order/payment/account was submitted; only anonymous cart/coupon checks and two clearly labeled internal test messages to the owner.

No new live first-order promotion amount or code was invented: TOOLKIT5 remains unchanged. The owner can mark any chosen native coupon First order per email.
