# Receiving methods — PKR EasyPaisa acceptance — 2026-11-08

Owner supplied receiving details: PKR via EasyPaisa account 0319-7497826; USD via Binance/USDT (already configured); GBP/EUR via Wise (owner will supply link + account ID — pending).

## Configured

- `instapass_easypaisa` gateway: enabled, title "EasyPaisa", PKR-only (`instapass_currencies=['PKR']`, and the Control Center restricts EasyPaisa to PKR carts), receiving instructions: EasyPaisa mobile account transfer to **0319-7497826**, exact order total in PKR, order number in the transfer note, then submit the TID through the website proof form or the chat buttons. The gateway was previously dormant with empty settings (before-state recorded as empty; rollback = restore the empty option).
- USD unchanged: Binance user-to-user (ID 1263957528) + direct crypto wallet, USD-only.
- GBP/EUR: still unavailable by design — the Bank transfer gateway (`instapass_transfer`) remains unconfigured until the owner supplies the Wise link + account ID. NayaPay also remains dormant unless the owner requests it.

## Verification

- Live PKR dummy order #198 (owner-authorized, test-labelled, cancelled): 11/11 checks (`ops/live-pkr-check.cjs`, evidence `outputs/live-pkr-order-20261108.json`) — PKR pricing renders (Rs 4,200 for Claude Pro 1 month), PKR checkout offers **exactly** EasyPaisa, order saved with currency PKR / total 4,200 / method instapass_easypaisa / guest / country-of-use PK; order-received shows the account number and the TID proof instructions; chat buttons present; original orders, stock and coupon usage preserved after cancellation.
- USD regression: fresh USD checkout still offers exactly Binance + direct crypto (no EasyPaisa leak) — `ops/usd-method-regression.cjs`.
- Visual review of the PKR order-received screenshot: instructions bold and legible; step strip, proof form, chat buttons all render (`outputs/site-review-20261108/pkr-easypaisa-order.png`). (The dummy receipt address in the screenshot is the test alias; the payment-date field defaults to the current Karachi date.)

## Pending owner input

1. ~~EasyPaisa account holder name~~ — supplied 2026-11-08: **account title "Noor"** added to the instructions under the account number, with a line noting the EasyPaisa app confirms the account title before sending. Settings readback verified (title + number present, still PKR-only, enabled).
2. **Wise link + account ID** for GBP/EUR — will be configured on the Bank transfer gateway restricted to GBP+EUR.
3. ~~NayaPay decision~~ — owner decision 2026-11-08: **keep NayaPay off**.

No products, prices, historical orders (other than cancelled test-labelled #198), or other settings changed.
