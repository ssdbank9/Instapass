# Instapass Email & Stock Alerts 0.1.0

WordPress/WooCommerce module for the current simple-product catalog. Reuses WordPress wp_mail and the existing FluentSMTP connection. No new paid mail or hosting service.

## Owner operation

Open Instapass → Email & Stock Alerts. Send a test to the WordPress administrator address, verify receipt in that inbox, select “I received the test email”, then enable alerts. Do not attest receipt based only on a successful send response. Changing the sending configuration invalidates the attestation; normal OAuth access-token rotation is excluded.

Edit Products → product → Product data → Inventory to set expected restock date and a short public message. These are estimates. The actual product must be published, in stock and have a positive USD price before an alert is submitted. Independent GBP prices and the USD/PKR rate are untouched. Existing orders and payment email templates are untouched.

Guests submit an email on an out-of-stock product page. They must confirm within 48 hours with an explicit button. A verified signup receives one alert when available. Either email contains an unsubscribe link; opening a link alone does not mutate consent. Further requests are separate opt-ins. Admin can pause sending, change a 1–500 daily attempt budget (default 100), cancel individual alerts, and browse all subscriptions in pages of 100. WordPress privacy exporter/eraser includes these records.

## Queue and limits

Jobs persist in the existing WooCommerce Action Scheduler, group instapass-notifications. A two-minute systemd timer processes only that group, five jobs per run. The normal WooCommerce scheduler is preserved. The application daily budget uses UTC and counts only this module's mail attempts, including confirmations. It does not cap WooCommerce order email and does not state a Gmail service allowance.

Atomic database claims prevent parallel workers from claiming the same unsent record. Known send failures retry at 15-minute intervals, at most three attempts. The queue does not automatically clear uncertain in-flight locks after a crash: inspect FluentSMTP's log and cancel the request if necessary. External email delivery is not exactly-once guaranteed; SMTP acceptance is distinct from inbox delivery. Stock and price are rechecked before submission; availability can change afterward.

No marketing mailing list, recurring newsletter, SMS, push notifications, variable-product alerts or provider account setup is included in this installment. Do not deploy any fixture scripts or test mail interceptors into production.

## Verification and recovery

See EMAIL-RESTOCK-REQUIREMENTS.md and the dated implementation record for integration, browser and live evidence. Tests use an isolated copied database, intercepted mail, example.invalid fixture addresses and a localhost SSH tunnel. The live plugin activates paused. Rollback: stop/disable instapass-notifications.timer and deactivate instapass-notifications with WP-CLI. Keep subscription tables if recovering data; no uninstall deletion occurs automatically. Database backups contain private data and stay outside Git.
