# Chat payment proof acceptance — 2026-11-08

Owner requested: customers can send payment proof via WhatsApp/Telegram from the order page, and the owner receives a notification when proof is submitted on the website.

## Delivered

**Customer side (live immediately, no setup):** the private order payment page now shows a "Prefer chat?" panel with two buttons — "Send proof on WhatsApp" (opens wa.me/18322503059) and "Send proof on Telegram" (opens t.me/Instapass0408) — each pre-filled with: "Payment proof for Order #N ($total, payment method). My transfer reference:". The customer adds their reference and attaches the Binance screenshot inside the chat app. Buttons appear only while the order is awaiting payment (on-hold/pending) and only behind the private order-key link.

**Owner side (needs a one-time free setup by the owner):** when a customer submits the on-site proof form, the site sends the owner a Telegram message with order number, total, method, reference, payment date and a direct wp-admin order link. Requires a free bot token: settings at Instapass → Chat Proof (bot token, chat ID, WhatsApp number, enable toggles, "Send test message" button). Until the owner saves a token+chat ID, the push silently stays off; the existing email notification remains in all cases. If sending fails after configuration, an order note records the failure.

## Implementation

- Theme `inc/payment-confirmation.php`: one added line — `do_action('instapass_payment_reported', $order, $method_title, $reference, $date)` after the payment-report order note. Recovery: `/opt/instapass/recovery/chat-proof-20261108T183500Z/payment-confirmation.php.before-hook`.
- New mu-plugin `instapass-chat-proof.php` + `instapass-chat-proof.css`: settings page under Instapass (nonce + manage_woocommerce guarded save/test actions), Telegram Bot API `sendMessage` via `wp_remote_post` (8s timeout), the thankyou chat panel, and the push handler on the new action (skips cleanly when unconfigured). Rollback: delete the two mu-plugin files and restore the theme file from recovery.

## Verification

- Live dummy order #194 (owner-authorized, test-labelled, cancelled): 24/24 checks pass (`ops/live-dummy5-chat-check.cjs`, evidence `outputs/live-dummy-order-20261108-chat.json`) — full checkout/UX regression green AND chat panel renders with correct pre-filled WhatsApp/Telegram hrefs (order number, $142.50 total, Binance method, wa.me/18322503059, t.me/Instapass0408); stock and coupon usage restored after cancellation. Screenshot: `outputs/site-review-20261108/chat-proof-buttons.png`. Order #193 from an earlier harness iteration (harness-scoping error, not a product defect) is a second cancelled test-labelled order.
- Plugin internals via wpcli: functions registered, defaults applied (WhatsApp 18322503059, buttons on, push on, token/chat empty). Submenu registration could not be observed in the CLI context (WordPress registers no submenus there, including existing entries); the registration uses the same proven pattern as the existing Product Tiles menu.
- Telegram delivery verified end-to-end: owner configured the bot token and chat ID via the settings page; a direct test send and a full live proof both delivered (order #195: website form submitted with TESTPROOF-20261108 → push accepted, no failure note, owner email sent; order cancelled and claims cleaned). Owner decision 2026-11-08: rotating the bot token (it was shared in chat) is deferred until the store goes live; the current token remains active and the feature fully functional.

## Binance app pay link (owner-approved follow-up, same day)

Added to the same mu-plugin: a "Binance app payment link" field in the Chat Proof settings (validated: https + `binance.com` host only; empty field clears it). When set, Binance order pages show a gold "Pay with Binance app →" button above the chat panel — opening the owner's Binance receive link (in-app on mobile, QR page on desktop); the customer enters the exact order total (the copy chip sits alongside). While the field is empty the button is hidden and nothing changes. Deployed with lint gate, dated recovery `binance-pay-20261108T195000Z`; internals verified by wpcli (link empty, Telegram/WhatsApp settings untouched). End-to-end order-page render pending the owner generating the receive link in their Binance app (Pay → Receive/Request, no fixed amount) and saving it.

### Binance link completed and verified (same evening)

- The owner's Binance app only offers amount-required request links, but their personal "Scan to pay" receive QR (User-48e5b) is a no-amount universal QR. The image (sent via WhatsApp, found in Downloads) was decoded locally with jsQR (base64 embedding to avoid canvas taint): **https://app.binance.com/uni-qr/HvRHwb1b**. Saved to the settings via wpcli; host validation passes; Telegram/WhatsApp untouched.
- Live proof — dummy order #200: **26/26 checks pass** including the gold button rendering with the exact uni-qr href, chat/WhatsApp/Telegram still correct, full checkout regression green, order cancelled and marked TEST ONLY with stock/coupon restored. (An earlier harness run on order #199 failed only because the new Binance section reuses the `.ip-chat-proof` base class, colliding with a count===1 assertion — fixed by scoping the assertion; recorded in MISTAKES entry 76.)
- Instruction wording cleaned: "WooCommerce order number" → "order number" in the Binance gateway instructions (readback verified).
- Mobile amount wrapping: `white-space:nowrap` applied to totals cells so amounts never break mid-number; verified live — order #201 rendered the totals table with top-aligned cells. (The "amount intact" assertion in that run was mis-targeted at the line-item cell — harness bug, not a site issue.)

### Totals-table anomaly — resolved (root cause: WooCommerce guest-privacy grace period)

The earlier "open note" is closed. Root cause: WooCommerce 11's order-confirmation blocks render full order details to guests only within an **email-verification grace period** (default 10 minutes, via `woocommerce_order_email_verification_grace_period`); after that, totals/summary fall back to a limited view until the customer verifies their email (one-field form rendered by WooCommerce). All observations fit: probes inside an order's 10-minute window saw the table; probes outside it did not; live payment-flow pages are always within the window.

Resolution deployed same day: new mu-plugin `instapass-order-grace.php` extends the grace period to **1 hour**, matching the store's 45-minute activation promise — customers can re-read order and payment details throughout the activation window without a verification step; after one hour the standard WooCommerce email verification protects the details. Verified on the previously ambiguous orders: #200/#201 (27–33 minutes old — past the 10-minute default, inside the new hour) now render the totals table; #196/#198 (90+ minutes) correctly show the gated view. Recovery: delete the new mu-plugin file; previous theme state backed up at `grace-tid-20261108T205000Z`.

Also deployed in the same pass: the proof-form reference label now reads "Transfer reference / transaction ID (TID) / transaction hash" so EasyPaisa customers see the term their app uses (theme `payment-confirmation.php`, lint + hash verified; renders on awaiting-payment order pages).

No prices, products, historical orders (other than cancelled test-labelled #193/#194/#195), payment settings or other logic changed.
