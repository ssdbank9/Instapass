# Email and restock notifications — acceptance record

Owner scope: website controls without code, expected restock date, guest email alerts, existing free hosting and email connection. Preserve pricing, orders, payment logic and other server workloads.

| Requirement | Implementation / acceptance |
| --- | --- |
| Keep canonical log in Git | MISTAKES.md committed in 21f276f |
| Existing Gmail delivery | Configured tokens found; live test and inbox receipt pending owner recipient response |
| Guest signup | Out-of-stock simple product form in actual WooCommerce block template; no account required |
| Consent | Confirmation email, explicit POST confirmation, expiry, unsubscribe; generic signup responses |
| Restock schedule | Owner date/note in inventory editor with nonce, permission and stale-value checks |
| Delivery | Durable Action Scheduler jobs, database claim, bounded retries, pause and daily budget; no duplicates on repeated stock updates |
| Eligibility | Published simple product, in stock, positive raw USD price; rechecked before sending |
| Owner controls | Email & Stock Alerts submenu, pause/enable, test send and explicit receipt confirmation, queue counts and cancellation |
| Privacy | No email in public URLs; secure tokens; owner-only queue; erase/export via WordPress privacy tools |
| Verification | Isolated copied database, outgoing mail intercepted, meaningful lifecycle and security assertions; live checks read-only except activation of disabled module |
| Production boundary | Do not send customer alerts or declare delivery ready before actual inbox confirmation; do not change existing products/orders/prices |

Provider token presence proves configuration, not delivery. wp_mail success means accepted for sending, not inbox arrival. Application budget covers this module only, not WooCommerce order mail. No new paid service is required by this implementation.

## Verified result — 2026-10-18

- Canonical log committed as21f276f; subsequent implementation/log commit recorded in Git history.
- 43 integration assertions pass in copied WordPress/WooCommerce; all fixture mail intercepted. Includes confirmed-request retention, concurrent claim guards, expiry, token purposes, POST-only consent changes, daily budget, bounded failures, privacy operations, stale editor rejection and private-proxy rate-key isolation.
- 19 browser checks pass on the actual copied theme and WordPress admin at desktop/phone widths. Includes complete guest alert lifecycle, one restock message, unsubscribe, budget save/reload and inventory date save/reload. Screenshots inspected.
- 2 scoped-runner checks pass: own due job processed while other group preserved; empty queue succeeds. Live timer active, last service result success/exit0.
- Live owner dashboardHTTP200, title/test control verified, budget100, module paused/unverified, no subscriptions. Product/price/stock and order fingerprints unchanged:85 products/3 orders.
- Existing Gmail configuration is present, but actual send and inbox receipt remain PENDING. No real test/customer email sent by this installment. Owner can complete the test/receipt/enable steps directly in the dashboard. Customer signup is not enabled before that attestation.
- Earlier owner Telegram instruction reconciled: live footer and Contact page use@Instapass0408; contact-only mu-plugin installed without database rewrite.
- No new paid service, receiving account, OCI instance or R2 change. Existing historical orders, GBP prices, USD/PKR rate and payment methods preserved.
