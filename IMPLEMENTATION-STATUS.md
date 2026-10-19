# Instapass implementation status

Updated 2026-10-18. User authorized step-by-step implementation. Existing site, customer orders and Wasooli must be preserved. This file distinguishes implemented, verified and pending work.

## Acceptance checklist

| Requirement | Acceptance evidence | Status |
| --- | --- | --- |
| Host on rdi-tracker 4 OCPU / 24 GB, retain Wasooli | HTTPS and service checks | Previously verified; baseline repeated today |
| Recoverable changes | Fresh database/files/config backup plus isolated database restore | Passed: 56 restored tables and integrity checks; full application restore remains pending |
| USD master prices ending .99 | Owner pricing preview; no silent rewrite of catalog | 20 existing priced products preserved; 65 unpriced products still await owner/catalog pricing |
| PKR = USD x 280, nearest 100 per unit, then quantity/sum | Boundary and cart integration tests | Owner rate setting, unit rounding, cart/order and browser price checks passed |
| Owner control panel | Capability checks, actual WordPress render, browser inspection | Signed-in dashboard read verified; GBP browser save workflow remains pending |
| UK defaults GBP, Pakistan PKR, others/unknown USD; remembered override | Browser tests, country lookup/simulated UK request and private/no-store responses | Implemented; physical UK-origin browser not tested |
| Immutable currency/rate/payment instructions on orders | Checkout and old-order regression tests | New-order snapshot and historical-order regression checks passed in isolated clone |
| Owner product/price/stock controls; global/selected/Excel discounts | Existing controls audited, live admin workflow tested | Control Center 0.4.0 live; bulk integration/browser checks passed; no live price batch applied |
| Payment methods enabled/disabled without code | Owner settings; currencies/countries/recipient/instructions | Partial existing manual gateways |
| Guest checkout and private on-site payment reports | Existing private order flow regression | Existing; verify with new currencies |
| Payment reference/date/actual method, optional receipt; cross-order duplicates | Concurrent submission tests; actual receipt requires owner verification | Existing implementation; regression pending |
| Email receipts and stock alerts | Verified transport and inbox delivery | Existing Gmail connection configured; notifications module installed paused; live test and inbox receipt pending |
| Inventory, restock ETA and back-in-stock signup | Owner and customer workflow tests | Implemented: 43 isolated integration checks and 19 browser checks; outgoing alerts await inbox confirmation |
| Private digital delivery, activation within 45 minutes | Verified paid event and private delivery tests | Pending |
| Customer accounts and verified claiming of guest orders | Ownership/security tests | Pending |
| Refund requests and actual refund tracking | Separate request/approval/money-transfer states | Pending |
| Renewals/subscriptions | Reminders and provider/manual distinctions | Pending |
| Verified buyer reviews and moderation | Moderation/ownership tests | Pending |
| Mobile shopping, retention and accessible UI | Phone/desktop browser acceptance | Pending |
| Sales/revenue/payment/activity/conversion dashboard | Reconcile to orders; fee/gross/net separately | Pending |
| Owner automation and optional n8n | Exact preview, confirmation, audit/undo; no added charges | Local deterministic implementation exists; deployment pending |
| Support @Instapass0408 | Rendered contact links | Corrected and verified live homepage footer and Contact page on 2026-10-18 |
| Exactly USD, PKR and GBP; no EUR | GBP regular/sale prices and discounts entered independently by owner; no USD conversion; blank means unavailable in GBP | GBP editor and classic currency flow installed; GBP receiving methods/prices and bulk GBP controls pending |
| Friend-owned personal Wise/Revolut accounts disabled | Do not expose unsupported commercial collection setup | Required |
| Keep costs within approved setup | No new paid services; R2 usage overages previously authorized | Required |

## Release sequence

1. Recovery baseline and isolated database restore.
2. USD/PKR commerce module with admin settings, unit rounding and full checkout/order snapshots.
3. Owner catalog/discount controls and payment-method settings integration.
4. Verified email, inventory/restock alerts, fulfilment and refunds.
5. Accounts, renewals, reviews, retention, dashboard and owner training.

Each installment must retain its tests and deployment/rollback evidence. A passing local calculation test does not prove live checkout readiness. Do not change historical orders or apply random new prices without a reviewed, tier-matched catalog preview.

## Installment 1 delivered — 2026-10-18

- Instapass Control Center 0.1.0 is active on production. Owner URL: https://instapass.online/wp-admin/admin.php?page=instapass-control (WordPress sign-in required).
- Provides live catalog/payment/order counts, recent orders in their original currency, links to existing editing screens and a USD/PKR preview. Global/selected/Excel controls still depend on the existing theme implementation; this installment does not claim their complete acceptance.
- Baseline: 85 published simple products, 20 priced (all .99), 65 without price, 84 in stock, one out of stock, three orders on hold. Checkout uses WooCommerce Blocks. Store remains USD; taxes off; MaxMind location detection unconfigured. No product or payment-setting writes in this plugin.
- Fresh backup: /opt/instapass/recovery/pre-commerce-20261018T062204Z. SQL, files and configuration hashes passed. Restored SQL in network-isolated, temporary MariaDB with no published ports/WordPress/email; all 56 application tables restored; integrity passed. Archive listing passed; full application restore remains pending. An additional SQL/files/config copy is now retained on the owner PC under migration-transfer-20261018/pre-commerce-20261018T062204Z; all three local SHA-256 hashes match the server manifest. Scheduled offsite backups and full application restoration remain pending.
- Tests: PHP syntax; 11 arithmetic/boundary/quantity cases; 12 malformed price inputs; five invalid quantities. WordPress integration: anonymous denied, owner renders, $4.99 x 3 gives Rs 4,200, malformed input escaped/rejected, invalid nonce denied. Repeated against installed plugin after activation.
- Browser: actual WordPress render inspected in local previews at 1440 and 390 pixels with no page overflow. This is not a signed-in wp-admin browser acceptance test. No signed-in WordPress tab was available; authenticated browser acceptance remains pending.
- Live normal-browser checks: Instapass 200, Wasooli 200, new dashboard sends anonymous users to WordPress login, CSS 200. Gateway settings, product counts, price coverage and order count match the prior baseline.
- Rollback: deactivate `instapass-control-center` using WP-CLI. This version creates no custom data tables/options and changes no checkout hooks. New private migration directory is ignored by Git; only non-secret maintenance scripts are retained under ops/.
- Next implementation: full currency integration in isolated staging, including Blocks cart/checkout, payment conversion/instruction snapshots, persisted manual currency override, trusted location detection and guest-order regressions. Customer currency switching is not yet live.

## Currency clarification and account recovery — 2026-10-18

- Three currencies only: USD, PKR and GBP. GBP will use manual owner-entered prices and discounts, independently of USD. GBP input controls and customer checkout remain pending. Blank GBP prices mean unavailable for GBP purchase; no currency conversion or zero-price fallback.
- Existing sole administrator verified from live WordPress; native password-reset key issued and validated. Official HTTPS reset form opened in the separate setup Chrome window. No password was read, chosen or reset by the agent; user must save their own password and log in. No new user/role created. Recovery URL is kept outside source control, restricted to the owner Windows account and SYSTEM, and excluded from logs.
- Owner browser login and authenticated dashboard acceptance remain pending until user completes password setup.

## Installment 2: independent GBP editor — 2026-10-18

- Control Center 0.2.0 is installed and active. Simple product editors now include GBP regular price, GBP sale price and optional apply-discount %. All 85 current products are simple products. No GBP values were entered on the live catalog by the agent.
- GBP regular/sale values are separate metadata, not USD conversions. Blank regular and sale values remove GBP pricing and represent unavailable, not zero. Explicit sale 0 / discount 100% is distinct from blank. Percentage is a one-time calculation on product Update; the field resets blank after reload. Sale can be removed with discount 0.
- Save guards: product-edit + manage_woocommerce permissions, product-specific nonce, existing GBP-price fingerprint, complete string fields and monetary validation. Invalid GBP values retain prior GBP values; normal WooCommerce base product fields can still save.
- Verification: PHP syntax passes; 11 GBP cases and 18 invalid inputs pass; USD/PKR regression passes. Isolated real WooCommerce test saved/reloaded a disposable product in a separate cloned MariaDB with no external network/ports and read-only production code. Nine checks passed: discount persistence, USD preserved, manual sale, remove sale, nonce/stale/malformed rejection, absent fields preserved, anonymous denied, blank unavailable, editor renders. Existing order count and store USD unchanged. Containers/network cleaned up after test.
- Production read-only audit confirms 0.2.0 and all three GBP fields render. Public website/Wasooli checks repeated after update. Signed-in owner browser interaction with GBP editing remains unverified through this connector; no live price-save demo was performed.
- Rollback archive: /opt/instapass/recovery/control-center-before-020-20261018T064842Z.tar.gz (SHA-256 ce95b1f0cd6edabfef08788933ddaee2572c1fc7efefd40ab2663f3514c0b4ab). Initial plugin was backed up; new includes copied/linted before main entry point replaced with fresh mtime.
- User reports password saved. Live WordPress confirms the reset key is cleared and the account has active sessions. Separate setup Chrome still redirects to login; in-app authenticated dashboard acceptance has not been inspected. Do not read or request the chosen password.
- Pending: GBP storefront/checkout integration, USD/PKR customer switching/geolocation, GBP global/selected/Excel pricing, stock alerts and verified email. No customer currency switching activated in this installment.

## Owner browser, HTTPS and WPVibe verified — 2026-10-18

- Authenticated browser now renders the Instapass owner dashboard (HTTP 200), admin toolbar, heading and all six owner-control links. This confirms signed-in dashboard read access; GBP price editing/saving through the browser remains a separate untested workflow.
- Strict TLS verification succeeded for https://instapass.online (200) and https://www.instapass.online (301 to root). Both have trusted Let's Encrypt certificates valid until 2027-01-16. HTTP root redirects to HTTPS (308). REST index returns 200 with name Instapass, home/url both https://instapass.online.
- Current authoritative nameservers: dns1.namecheaphosting.com and dns2.namecheaphosting.com. No Cloudflare DNS/proxy/SSL changes were needed or made.
- WPVibe initially reported no stored credentials. Its standard browser authorization flow was completed by the user; connect_site then verified authenticated read. Follow-up site_info succeeded: site_name Instapass, site_url https://instapass.online, connected administrator ssdbank9, connection_verification.state verified. WordPress 7.1.3, PHP 8.3.33, WPVibe 1.20.3, active theme Instapass 1.9.0. Free connector plan; publishing/uploads not tested.
- Evidence: private migration-transfer-20261018/owner-browser-check.json and https-wpvibe-verification.json. Browser authorization URL/credentials are deliberately excluded from this log.

## Installment 3 acceptance checklist — 2026-10-18, in progress

- Owner can edit one positive USD/PKR rate (maximum two decimals) through the Instapass dashboard; native permissions/nonce/stale-setting checks; recent change history.
- PKR unit prices use that rate and nearest Rs 100 rounding. Existing order totals/rate/instructions stay frozen.
- UK IP defaults GBP, Pakistan PKR, other/unknown USD; remembered manual selection overrides location. GBP values remain owner-entered, never converted.
- Frontend selector offers exactly USD, GBP and PKR. Missing GBP values stay unpriced, with a USD switch link; switching a nonempty basket to an unsupported currency preserves it.
- Checkout/order currency must match basket currency. Crypto remains USD/USDT only; domestic methods PKR; bank transfer currencies are owner-configured. No unsupported GBP payment is enabled automatically.
- Test actual rendered checkout: the page contains a Blocks placeholder, but the existing guest-checkout theme replaces it with the classic WooCommerce shortcode. Earlier checklist assumed Blocks at runtime from page markup alone; this is corrected. Cart Store API prices still need verification.
- Isolated tests before publication; live catalog/order data untouched during fixtures. Scope limitations and geographic simulation versus actual UK-origin proof must be explicit.

## Installment 3 delivered — editable rate and currency selection, 2026-10-18

- Control Center 0.3.0 is installed/active. Owner dashboard has a USD-to-PKR rate form; save was exercised through the signed-in browser using the approved 280.00 value, and server option confirmed 280.00. Rate accepts positive values/two decimals, uses permission/nonce/stale-form checks and retains 30 audit entries. Duplicate legacy rate/estimate controls are removed while plugin is active.
- The storefront toolbar offers exactly USD, GBP and PKR. UK/GB first visits default GBP, PKR defaults for Pakistan, other/unknown USD; a secure 30-day cookie remembers manual choice. Country lookup is server-side HTTPS country.is, two-second timeout, 12-hour hashed-IP country cache; failures fall back USD. Caddy currently supplies the client IP; future Cloudflare proxy setup needs correct trusted-proxy configuration. Shared page caching is disabled/private/no-store for currency-bearing pages.
- GBP stays independently entered, never converted. Live catalog currently has zero GBP-priced products, so GBP listings show 'GBP price coming soon' and a USD link. A basket is preserved rather than switched to an unpriced currency. Current 85 products are simple; non-USD variable/grouped products remain unsupported.
- PKR unit conversion uses the owner rate, exact integer arithmetic and nearest-Rs-100 half-up rounding before quantity. The underlying currency retains two minor units for fractional discounts/refunds; whole-rupee formatted amounts omit .00. Positive prices rounding to zero are unavailable, never accidental free products.
- GBP basket and classic checkout/order totals were tested with configured dummy GBP instructions in the isolated clone. Live GBP receiving methods remain unconfigured; no gateway/recipient was automatically enabled. Crypto/Binance remain USD only, EasyPaisa/NayaPay PKR only, bank transfer exposes accepted-currency settings. Live USD checkout still renders its available methods. Fixed-amount coupons, physical shipping and extra fees currently require USD; percentage coupons are supported.
- New orders retain currency, PKR rate where used, rounding policy and receiving instructions. Relevant quote changes cause classic checkout to refresh/review before placing an order. Direct non-USD Store API purchases are blocked; the current website uses classic checkout. Store API cart/product reads carry selected currency correctly.
- Verification: 24 isolated real-WooCommerce checks passed in a temporary MariaDB clone (no external route/ports), including actual GBP/PKR fixture orders, coupons, fractional paisa, native cart API, USD restoration and the original three orders untouched. Pure money tests pass 13 cases including fractional-rate boundaries, 12 malformed inputs and five invalid quantities; GBP tests pass 11 cases and 18 invalid inputs.
- Live browser checks cover saved rate, GBP/ USD controls, remembered USD, product API PKR conversion, basket preservation, classic USD checkout quote field/payment choices, no page overflow at 1440/390 and Wasooli HTTP 200. Actual phone screenshot inspected. No live order submitted. UK default passed a loopback HTTP simulation using a UK IP plus an actual server-side country lookup returning GB; physical UK-origin browser traffic was not tested.
- Recovery: fresh backup /opt/instapass/recovery/pre-commerce-20261018T071122Z; all archive hashes and isolated 56-table database restore/integrity passed. Plugin rollback archive /opt/instapass/recovery/control-center-before-030-20261018T071145Z.tar.gz, SHA-256 23cb1853fc44ecf5ff86a1082bf83bc60e940ebe9805cc590ea9ad7d2acc742f. Previous raw rate was 0; it is separately recorded in recovery/pkr-rate-before-030-20261018T071145Z.txt. Do not overwrite newer orders with an old database just to roll back plugin code.
- Pending: actual GBP prices, configured GBP/PKR receiving methods, verified email/stock notifications, GBP global/selected/Excel operations, and a reconciled dashboard grouped by currency (native combined Woo revenue must not be treated as converted revenue).

## Next release step preflight — pricing and owner controls, 2026-10-18

- Live authenticated WPVibe read confirms the active theme is Instapass 1.9.0 and active plugins include Instapass Control Center 0.3.0, but not the local Instapass Automation or the local bulk-pricing page implementation. The initial conclusion that no legacy pricing route existed was incorrect: isolated browser verification rendered the theme's existing pricing/stock page at that slug. Candidate 0.4.0 now uses the unique `instapass-control-pricing` page and its actual returned WordPress hook.
- The legacy theme source has a USD-only bulk price engine with global fixed discount, selected fixed/random 25–30% discounts, Excel/CSV preview, audit, and guarded undo. It updates WooCommerce base prices only. It has no GBP bulk-price path; random discounts are limited to selected products. The active dashboard already conditionally links to the page only when the theme registers it.
- Local Control Center source is now prepared as version 0.4.0 with an Instapass → Pricing & Discounts page, USD/GBP global and selected discount previews, random 25–30% preview for all or selected products, per-row USD/GBP Excel/CSV import, user-scoped audit history, and guarded undo. USD updates target WooCommerce base prices; GBP updates only independent GBP metadata; PKR remains derived from USD. Spreadsheet rows can include both currencies for the same SKU. No stock, visibility, product text, orders or payment settings are written by these controls.
- Verification completed after corrections: 26 bulk integration checks, 21 isolated real-browser workflows, 24 currency/checkout regressions, PHP/JS syntax and pure money/GBP cases pass. The owner clarified that no product will be USD 1.99 and dismissed that hypothetical case as a release blocker. No extra price-floor rule or catalog mutation was introduced. Production installation and 13 live browser checks have passed; final details are below and in outputs/CONTROL-CENTER-0.4.0-VERIFICATION.md.


## Installment 4 delivered — USD/GBP bulk pricing, 2026-10-18

- Control Center 0.4.0 is installed on the existing 4/24 rdi-tracker server. The owner dashboard links to Instapass → Pricing & Discounts at /wp-admin/admin.php?page=instapass-control-pricing. Global/selected fixed and random 25–30% previews, per-row USD/GBP Excel/CSV import, downloadable template, history and guarded Undo are available.
- Fresh backup /opt/instapass/recovery/pre-commerce-20261018T085637Z passed hashes, file archive readability, a network-isolated 56-table database restore and integrity checks. Previous plugin archive: /opt/instapass/recovery/control-center-040-20261018T085702Z/control-center-before-040.tar.gz (SHA-256 dd7a5daefe93cdee3f626012fca1d796e4380ab002c40f2c18b7b61c68ac00ef).
- Before installation, immediately after it and after live browser verification, catalog/order fingerprints are identical: 85 products, 20 USD-priced, no GBP-priced products, three orders, base USD and PKR rate 280.00. No Apply, import save, order, refund or payment submission was performed on the live site.
- Signed-in live browser checks pass for all 85 catalog rows, the 170-row two-currency template, global/random preview and cancellation, expected missing-GBP handling, access restrictions, desktop/phone-size layouts, PKR arithmetic and public Instapass/Wasooli health. Live screenshots and JSON evidence are in outputs.
- Final visual review identified stale prior-error messaging after a currency switch. It was reproduced in a network-isolated focused browser test, corrected by always clearing the prior status on input changes, and published with JavaScript asset build 0.4.0.1. The plugin remains version 0.4.0; pricing calculations and persistence code did not change. The focused test passes three assertions. Pre-UI-follow-up archive: /opt/instapass/recovery/control-center-before-040-ui-20261018T090404Z.tar.gz (SHA-256 f6d2f8faee39ba1a593414b4d30ab8d89c0c0db6c4bbacb55922bbf69f10c850).
- Remaining site work includes owner-supplied GBP prices, verified receiving methods, email/stock alerts and currency-separated revenue reporting. This installment does not certify the entire site launch checklist as complete.

## Email and restock installment — 2026-10-18

- Instapass Email & Stock Alerts 0.1.0 installed. Owner page: https://instapass.online/wp-admin/admin.php?page=instapass-email-alerts. Authenticated live browser returns200, shows heading, test-send control, daily limit100 and paused/unverified state. No live test/customer email sent; existing Gmail OAuth connection is configured but actual inbox delivery is unverified.
- Owner controls: send test to site administrator, confirm actual receipt, enable/pause, daily attempt budget1–500(default100), paginated subscriptions and individual cancellation. Product Inventory gains estimated restock date and message with nonce, capability and stale-value checks. Variable products are outside this installment; all85 current products are simple.
- Customer flow: guest out-of-stock signup, 48-hour confirmation, explicit POST confirmation/unsubscribe, one-time alert only after publication/in-stock/positive raw USD price, then submitted state. Signup throttling hashes identifiers and avoids grouping every customer behind a private reverse proxy. No customer account or paid email service added.
- Existing WooCommerce Action Scheduler stores jobs. A systemd timer runs only instapass-notifications every two minutes, batch5. Tested runner processes its own due probe, preserves another group's due probe, and exits cleanly with no group/jobs. Live timer active and last service result success/exit0. Default WooCommerce scheduler is preserved.
- Known limitations: wp_mail success proves submission, not delivery; SMTP cannot guarantee exactly-once external delivery. Known failures retry up to3 times. Crash-interrupted locks require owner mail-log review and cancellation rather than blind resend. Daily budget applies only to this module, not native order email. In-stock availability can change after a notification. Mail readiness remains pending actual inbox receipt.
- Tests: 43 isolated WordPress/WooCommerce integration checks,19 actual HTTP/browser checks at1440/390px,2 isolated queue-boundary/empty-queue checks. All fixture mail intercepted; internal Docker network prevents outside traffic. Screenshots inspected; mobile admin table overflow corrected. Outbound service/inbox acceptance remains a separate pending check.
- Deployment backup: /opt/instapass/recovery/notifications-010-deploy-20261018T094014Z/pre-notifications.sql with SHA256 manifest. Product/price/stock fingerprint6f1543dc85476da8518affae01809d40c34d60811642df2f9a3e0e307a222314 and order fingerprint1964da07e0a4af757420a0731a5f60cb251cbe7493740b7ea6e6dbd1da7a62ac match before/after:85 products,3 orders. No historical order/product changes.
- Live contact reconciliation: mu-plugins/instapass-contact.php corrects old Telegram rendering in saved templates. Live footer text and href and both Contact-page Telegram hrefs match@Instapass0408. No database rewrite/theme replacement. Rollback: remove only that mu-plugin.
- Notifications rollback: stop/disable instapass-notifications.timer, deactivate instapass-notifications. Preserve own tables for recovery/privacy handling. Full database backup restoration is not required merely to disable the feature. Secrets, backups and fixture credentials are excluded from Git.

- Final module SHA256: fd1263c4bf4d0bfd9e6a83da111564962d7b0eae83e2a40d8f808adcad36056a. Local/installed sources match. All temporary notification test containers/networks and SSH tunnels stopped; disposable local fixture credentials removed. Root-protected recovery artifacts retained.

## Guest checkout, coupons, tracking and manual matching - 2026-10-19

- Guest Orders 0.1.0 is active. Customer tracking: https://instapass.online/track-order/; owner guide: admin.php?page=instapass-guest-orders. Guest checkout remains account-free; optional My Account signup sends password setup and does not authenticate an unverified address.
- Receipt/tracking email is required. An optional separate activation email is saved privately, shown to the owner and in receipt metadata; blank uses receipt email. Existing orders fall back to their original billing email.
- Native coupon editor gains First order per email, paid-order eligibility and a native usage limit of 1. Guests using another email deliberately remain eligible. Existing TOOLKIT5 5%/two-distinct-product offer is unchanged. No unspecified live first-order percentage/code was invented.
- Guest order-number/email lookup exposes limited status, items and total, with no-cache/noindex headers and throttling. Private payment links are emailed to the saved receipt address; no private order key/payment reference/screenshot appears in tracking results.
- Updated live payment theme files include private email return links, clearer report/verified states and order-currency proof-method restrictions. Sequential and simultaneous duplicate references are rejected across orders/methods. Same-order retries do not duplicate owner notification. Only owner receipt verification marks payment received; simulation stayed in an isolated clone.
- Production wc_mail test reached the correct Gmail from orders@instapass.online. It exposed missing Reply-To on generic custom notices; Guest Orders now supplies support@instapass.online when absent. A second actual received message verifies From orders, Reply-To support and passing SPF/DKIM/DMARC. No credentials changed.
- Validation: 53 isolated integration/browser/concurrency/editor checks, 2 focused header checks and 18 public live browser checks pass. Phone tracking/checkout screenshots inspected. No live order, payment or customer account submitted; 3 existing orders and all product/price/stock fingerprints unchanged. Intentional 65 unpriced Price on request listings preserved alongside 20 priced products.
- Backup/restore: pre-commerce-20261018T184849Z; 58 application tables restored and integrity passed. Code/option rollback records: guest-commerce-deploy-20261018T185600Z; Reply-To patch recovery guest-commerce-mail-20261018T190003Z. Preserve new orders when rolling back code.
- Remaining owner input: real EasyPaisa/bank receiving instructions and any GBP receiving setup/prices. They remain unavailable until configured. Automatic license provisioning and actual fund receipt are not certified by simulated checks. Full acceptance details: ops/GUEST-COMMERCE-ACCEPTANCE.md.

## Two bundle offers - 2026-10-19

- Owner superseded first-order coupons: TOOLKIT5 gives5% for two or more different products; TOOLKIT10 gives10% for four or more in one order. Copies/variations of one parent count once. Both guests and returning customers qualify with no first-order/per-customer limits, one code per order, based on current basket prices.
- Live Products > Promotions now has two independent owner forms for status, code, percentage and eligibility threshold, with nonce/capability/stale-setting protection. Guest Orders0.2.0 removes prior first-order controls; optional accounts, activation/receipt separation, tracking and private payment matching remain.
- Validation:16 isolated native WooCommerce checks,14 browser checks including owner saves,11 anonymous live checks all pass. Live two-product discount1.20; four-product discount24.90,total224.06. Phone screenshots inspected; no live order/payment/account/email created.
- Installed source matches reviewed upload. Catalog product/price/stock hash and all3 historical order hashes unchanged;20 priced plus65 intentional unpriced listings preserved. Instapass and Wasooli healthy.
- Verified backup pre-commerce-20261019T022242Z; guarded code/offer recovery bundle-offers-deploy-20261019T023125Z. Isolated test containers/network/tunnel and synthetic credentials removed. Full current acceptance:ops/BUNDLE-OFFERS-ACCEPTANCE.md. Historical first-order test records above are superseded for coupon policy.

## Separate tenor offers - 2026-10-19

- Owner amended same-parent counting: different tenors of one product are separately addable and can qualify separately. Tenor Offers 0.1.0 is installed; Products > Subscription Tenors explains native product duplication/drafting, same-product family linking, exact Access duration and independent price/stock controls. Current simple-product setup preserves USD/PKR/GBP editing and restock compatibility.
- Published visible linked tenors appear together on product pages with price/availability and add action. Cart preserves separately added tenors and displays Duration; native order lines retain it. Draft/hidden offers stay private; sold-out/unpriced offers have no buy button. Repeated same-family/tenor quantities/listings count once, including 1 year/12 months aliases.
- Bundle amounts remain5% at 2 selections and10% at 4; source terminology now product/tenor offers. Guest Orders 0.2.1 only updates this copy/version. Guests/returning customers qualify, one code per order. Previous same-parent tenor exclusion is superseded.
- Validation:22 native integration+22 isolated browser+11 live anonymous browser checks passed. Phone product/checkout screenshot inspected, owner native family save persisted. No live order/payment/customer email created. All85products/prices/stock,3 historical orders and coupon audit values unchanged. Instapass/Wasooli healthy. Source upload/install comparisons pass.
- Backup pre-commerce-20261019T023921Z restored 58 tables with integrity pass; deployment recovery tenor-offers-deploy-20261019T024515Z. Dedicated fixture resources/tunnel/synthetic credentials cleaned. Current acceptance:ops/TENOR-OFFERS-ACCEPTANCE.md.
- Remaining commercial input: owner must supply actual higher-tenor durations/prices and desired stock/access terms. No new public commercial tenor was invented or published; the support framework is live and tested using synthetic offers only in the isolated clone.
- Expanded integration acceptance: 24 native checks now pass, including two independent tenor prices (10.99 and20.99), subtotal31.98,5%discount1.60,total30.38. Runtime code is unchanged since browser/live acceptance. Owner confirmed rollout products: ChatGPT Plus, Claude Pro, Claude Max; exact longer terms/USDprices and Max5×/20×scope remain pending. No additional commercial offer was published.

## Claude tile additional limits copy - 2026-10-19

- Added owner-supplied "25% additional limits" to the existing brief on Claude Pro30 and Claude Max5x31 only. Max20x32 unchanged. Preserved prior copy; prices, stock and order hashes unchanged. No runtime theme/plugin deployment or checkout/customer mail.
-8 live shop tile/width checks pass at1440/390px; both phone screenshots inspected. Per-field rollback/audit:/opt/instapass/recovery/claude-tile-copy-20261019T031306Z. Current evidence:ops/CLAUDE-TILE-COPY.md. This records requested copy, not independent provider entitlement verification.

## Three-selection 7.5% bundle offer - 2026-10-19

LIVE: TOOLKIT75 adds 7.5% for at least three distinct product/tenor selections, between existing TOOLKIT5 (two/5%) and TOOLKIT10 (four/10%). Three independent editable owner forms preserve existing configurations and coupon usage.31 native,28 isolated browser and8 focused live checks pass.Three-item live total98.97 minus7.42 =91.55.Guest checkout preserved; no real order/payment/email created.Product/order fingerprints unchanged.Test resources and tunnel removed.Recovery:three-product-offer-20261019T042805Z.See ops/THREE-PRODUCT-OFFER.md for acceptance and rollback.A separate Wasooli health check fails Chromium TLS trust (ERR_CERT_AUTHORITY_INVALID); its service and Caddy were unchanged.

## Approved tenor pricing and fixed PKR override - 2026-10-19

LIVE: Plus29one month14.99; new138Plusannual173.33USD/fixed47500PKR; Max5x31one month75; new139sixmonths432upfront; new140annual833.33upfront.Native eligible10% coupon reaches approximately156Plus/750Maxannual, with one-cent basket allocation preserved.Three new offers are separate simple products, linked by plan family.Coupon eligibility remains2/5%,3/7.5%,4/10%, one code per order.Control Center0.5.0adds validated owner PKR regular/sale/percentage fields and per-listing exact-USD bulk-import permission.GBP remains independent and blank.Native pricing29andcurrency24checks passed;17live pricing and18live copy/layout checks passed.Copy corrected new listings to confirmed term/payment details and puts25%additional limits first on Pro/Max5x tiles; Max20x excluded.Recovery:approved-prices-20261019T045109Z and approved-prices-copy-20261019T045337Z.All historical orders and payment/coupon/rate settings preserved.No live order/payment/email.New Claude Pro tenors remain pending owner prices.See ops/APPROVED-TENOR-PRICES.md.

## Monthly price presentation and tax policy - 2026-10-19

LIVE Tenor Offers0.2.1: known linked month/year subscription offers show monthly equivalents, full upfront term amounts and explicit bundle qualification. Max5xannual retains833.33USD,display69.44base/65.97at5%/64.24at7.5%/62.50at10%. Owner retained62.50target rather than proposed65. Full-term native checkout/order/StoreAPI amounts remain unchanged. Tax policy displayed:Applicable taxes are covered by Instapass.No extra tax at checkout.Existing disabled tax/no rates preserved; no remittance claim.30isolated native and31live browser checks pass at390/1440, including fixedPKR and missingGBP. Phone card clipping from native max-content sizing was fixed and element bounds checked; final screenshot inspected. No real order/payment/email; original financial audits unchanged; test containers removed. Recovery:monthly-pricing-20261019T050834Z and monthly-card-wrap-20261019T051211Z. Current receipt:ops/MONTHLY-PRICING-ACCEPTANCE.md.
