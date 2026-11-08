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

## Owner-approved domain sender policy — 2026-10-18

Owner approved segregated domain addresses and requires replies to use the correct business domain rather than the personal email identity. This amends the interim Gmail sending setup; it is a target policy, not a claim that domain sending is active.

| Message / action | From | Reply-To / owner reply identity | Acceptance |
| --- | --- | --- | --- |
| Order confirmation, activation instructions, payment status, delivery and refund updates | Instapass Orders <orders@instapass.online> | support@instapass.online | Place disposable order; inspect received headers and customer reply path |
| Customer correspondence and every owner reply | Instapass Support <support@instapass.online> | support@instapass.online | Retain order number/thread; support handles customer conversation; payment confirmation remains owner-verified |
| Restock confirmation and availability alert | Instapass Support <support@instapass.online> | support@instapass.online | Confirm signup and receive one restock alert with authenticated domain sender |
| General support conversation | Instapass Support <support@instapass.online> | support@instapass.online | Incoming and outgoing support round trip |
| Owner reply to any business email | Instapass Support <support@instapass.online> | support@instapass.online | Owner requires replying from instapass0408@gmail.com interface; verify actual From, Reply-To, Sender and received authentication |
| Owner reply to support mail | Instapass Support <support@instapass.online> | support@instapass.online | Same checks; no personal address in customer-facing identity/signature |

DNS/MX inspection: authoritative DNS remains Namecheap hosting; MX records point to mx1/2/3-hosting.jellyfish.systems. After renewed owner sign-in, the Namecheap hosting page lists instapass.online on Stellar with ACTIVE status and expiry10November2026. cPanel Email Accounts shows30Available/0Used; neither orders nor support has been provisioned. Existing forwarders/catch-all remain unverified. Preserve these existing records until inventory is complete. Cloudflare R2/account presence does not establish email routing or DNS authority.

Cost constraint: no new paid service or automatic paid upgrade. Existing Stellar mailboxes may have no extra mailbox fee while that hosting plan remains active, but this is not a permanently free hosting entitlement. Do not require retaining/renewing a paid hosting plan without the owner's decision. Independent free forwarding/sending remains an alternative if required.

Durable owner replies: avoid relying on personal Gmail's third-party Send-as feature. Google announces its removal in January2027, with new configurations restricted earlier. Prefer actual business webmail or an IMAP/SMTP client with the correct business account selected automatically. Forwarding to Gmail alone does not configure outbound domain identity.

Activation gate: confirm mailbox/SMTP availability and domain authentication; preview sender configuration; test both identities and a reply round trip to an approved recipient; inspect raw headers. Do not declare delivery/reply identity verified merely because wp_mail returned true or a display name changed. Keep personal credentials and SMTP secrets outside Git/logs. Domain sender configuration remains pending these checks.

Primary sources checked: https://support.google.com/mail/answer/17101213?hl=en ; https://www.namecheap.com/support/knowledgebase/article/10112/22/shared-hosting-plans-comparison/ ; https://www.namecheap.com/support/knowledgebase/article.aspx/9142/2186/general-configuration-for-mail-clients-and-mobile-devices/ ; https://developers.cloudflare.com/email-service/platform/pricing/ .

### Latest owner clarification — overrides per-address manual replies

- All incoming business emails, including orders@instapass.online and support@instapass.online, must reach one Gmail inbox: instapass0408@gmail.com.
- Order confirmations and product activation emails/instructions come from orders@instapass.online. Customer correspondence and every manual owner reply use support@instapass.online.
- Owner explicitly wants to reply from that Gmail interface, with customer-visible From and Reply-To support@instapass.online for every manual response. Do not substitute a separate app/dashboard or retain per-address reply identities without agreement.
- Automated order confirmation still uses From orders@instapass.online; Reply-To support@instapass.online routes customer replies to the shared support path. Independent domain-authenticated outbound SMTP is required; changing a display name or forwarding mail alone does not satisfy the requirement.
- Native Gmail third-party Send-as support has an announced January2027 removal, and Google may already restrict new configurations. Inspect this specific Gmail account before promising current availability. This exact native-Gmail workflow cannot be represented as a permanently guaranteed free setup under that published policy.
- If current Send-as setup is available, configure authenticated external SMTP, default From support, default Reply-To support, appropriate business signature, and verify actual received headers/round trip. Never claim personal-identity suppression without received-message proof. Delivery and native-Gmail settings remain pending sign-in/provider verification.


### Free outbound alternatives researched — 2026-10-18

Owner requested alternatives to Brevo; Brevo onboarding is not selected and no signup/configuration was submitted.

| Provider | Published free transactional SMTP allowance | Material finding |
| --- | --- | --- |
| Mailtrap Email API/SMTP | 4,000/month;150/day;1domain | No credit card required. Pricing marks free overages unavailable; FAQ describes monthly-limit SMTP rejection. One sending-limits help article still says3,500/month, conflicting with current pricing and billing FAQ4,000; confirm actual dashboard quota before activation. Recommended candidate, pending signup/domain approval and real receipt checks. |
| Resend | 3,000/month;100/day | SMTP supported; free-forever plan. Both inbound and outbound consume quota; keep inbound forwarding separate. Paid overages are opt-in on paid plans. Alternative if Mailtrap approval fails. |
| SMTP2GO | 1,000/month;200/day | Ongoing free SMTP. Public signup currently requires private-domain email and phone verification, so establish working support forwarding first. Lower monthly allowance. |
| Mailjet | 6,000/month;200/day | SMTP/API, no card at signup. Free lists1,000contacts; logo removal begins Essential. Confirm transactional recipient/contact treatment before considering2,500customers. Not preferred. |

Sources: https://mailtrap.io/pricing/ ; https://docs.mailtrap.io/email-api-smtp/help/faqs ; https://docs.mailtrap.io/email-api-smtp/setup/sending-limits ; https://resend.com/pricing ; https://resend.com/docs/send-with-smtp ; https://resend.com/docs/knowledge-base/account-quotas-and-limits ; https://www.smtp2go.com/faq/ ; https://www.mailjet.com/pricing/ .

Branding-free status for Mailtrap/Resend/SMTP2GO was not established conclusively in this research; do not promise absence of an injected footer until account terms and received mail are checked. Published free tiers are current offers, not guarantees that provider policies never change.2,500registered customers does not determine mail volume: four messages for100orders consumes400 sends, plus support/restock mail. Never silently auto-upgrade; retain quota failure visibility and queue/backoff controls.

DNS cutover hold: Cloudflare zone is pending with27draft records and assigned nameservers megan/remy.ns.cloudflare.com. Draft rootA199.188.200.15 conflicts with live rootA145.241.115.28. Correct and compare all draft records before any nameserver change; existing forwarders/catch-all inventory is still pending. No production DNS/mail changes performed during provider research.


### Provider and forwarding setup update — 2026-10-18

- Namecheap cPanel initially had no mailbox for orders@ or support@, and no email-account or domain forwarders. Added both requested forwarders: orders@instapass.online → instapass0408@gmail.com and support@instapass.online → instapass0408@gmail.com. cPanel displayed a successful creation notice for each, and the refreshed Forwarders table showed both mappings.
- No email was sent during setup. MX, root SPF, default DKIM, DMARC and nameservers remain unchanged. Existing Namecheap mail routing remains active.
- Resend domain verification is pending: the available Chrome session is signed out of Resend, so no Resend-generated DNS values, API key or SMTP configuration has been added. Owner is completing Resend sign-in directly.
- Website order mail, restock mail, authenticated support sending, Gmail Send-as identity and round-trip/raw-header verification remain pending. Do not mark email delivery ready until owner receives and confirms the authorized test message.
