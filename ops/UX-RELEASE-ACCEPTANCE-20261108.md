# UX friction-release acceptance — 2026-11-08

Owner approved the full batch ("fix everything") from the review's best-solutions list (`ops/SITE-REVIEW-20261108.md`). All changes are presentation-only and additive: one new mu-plugin (`instapass-ux.php` + `instapass-ux.css` + `instapass-ux.js` in `wp-content/mu-plugins/`). No checkout-processing, pricing, order or settings logic was touched. Rollback = delete the three files.

## Delivered

Checkout (A):
- A1 receipt-email autofocus on arrival, retrying past WooCommerce's notice focus and yielding to any user interaction
- A2 one-tap "Apply TOOLKIT5" chip inside the checkout promo box (reveals the coupon form, fills and applies in one click)
- A3 optional fields (different activation email, phone, renewal consent) collapsed behind an "Optional details" toggle; auto-opens if a hidden field fails validation

Order-received page (B):
- B1 tap-to-copy chips for the Binance ID and the exact amount (clipboard API with visual confirmation); Direct Crypto already renders a QR via the existing gateway `QR code:` line (verified present in gateway settings)
- B2 "Payment made" button restyled as a full-width filled primary CTA using the theme accent
- B3 compact Pay → Submit → Verified step strip; the duplicated "Payment reference: order #N…" paragraph in the instructions card is hidden
- B4 "Pick the date you sent the payment" hint under the date field
- B5 the exact-amount line is sticky at the top of the payment form on mobile

Shop tiles (C):
- C1 "From $15.00 /month"-style compact price chip under every tile title, following the selected duration or credit balance (8/8 tiles)
- C2 PRO / MAX 5× / ULTRA 20× badges over the three visually identical Claude tiles
- C3 typography verified already correct at source (5×/20× in stored captions); no data change was needed — the earlier "5x" reading was a console-encoding artifact

Promo exposure (D):
- D2 the shop-page promo box is hidden (CSS, archive pages only); tiles and the checkout chip remain the two exposures

Accessibility (E):
- E1 currency pills and the navigation toggle ≥ 44px touch targets
- E2 tax-assurance and announcement notice text ≥ 13px
- E3 VPN banner regression re-verified visible after all deployments

D1: the "1-click checkout" label is kept everywhere; the flow now earns it.

## Bugs found and fixed during deployment (all in the new mu-plugin only)

1. E2 font-size rules lost the cascade to later theme styles → `!important` fix.
2. A1 autofocus was stolen by WooCommerce's add-to-cart notice container focus → retry schedule with user-interaction yield.
3. **collapseOptional DOM bug:** rows were appended into the `details` before inserting `details` into the document, making `insertBefore(details, anchor)` throw `HierarchyRequestError` (the anchor's parent had become the detached `details`). The three optional field rows ended up detached from the live form, silently removing activation email, phone and renewal consent from checkout. Fixed by inserting the empty container first, then moving rows. Full reproduction, source-level bisect and fix are recorded in MISTAKES.md entry 74.
4. C1 chip initially scraped the whole quote container (savings banner + price); corrected to read `ip-term-pricing` / `ipl-credit-pricing` with a compact amount extraction.
5. Chip listeners attached directly to radio/slider nodes were orphaned when the catalog JS rebuilt them → delegated document-level events.

## Verification

- Public + checkout suite: 24/24 live browser checks pass (`ops/ux-verify.cjs`, evidence `outputs/site-review-20261108/ux-after.json`): 8 chips with correct compact values that follow duration changes, 3 tier badges, shop promo hidden, checkout promo retained, email autofocus, collapse with country selector outside, chip applies TOOLKIT5 ($30 − $1.50 = $28.50 on 2× Pro), no JS page errors, mobile no overflow, pills ≥44px, VPN banner visible.
- Live dummy order #191 (owner-authorized, following the order-143/189 precedent): 21/21 checks pass (`ops/live-dummy3-check.cjs`, evidence `outputs/live-dummy-order-20261108-ux.json`) — checkout through the augmented UI (autofocus, chip discount $150−$7.50=$142.50, collapse), order-received renders step strip, copy buttons (click confirms), primary CTA, date hint, sticky amount; order saved correctly (guest, unpaid, unreported, unverified, 1+1 split across 2 accounts, country US); tracking correct and privacy-preserving; order marked TEST ONLY / DO NOT FULFIL and cancelled; original orders preserved; stock and coupon usage restored. Order #190 (a harness-aborted earlier run whose failure was a case-sensitive test regex against CSS-uppercased text, not a product defect) remains as a second cancelled test-labelled order.
- Screenshot reviewed: `outputs/site-review-20261108/ux-order-received.png` (step strip stacks vertically on mobile; button uses the theme accent teal — consistent with the `--accent` variable).

## Follow-up polish round (same day, owner-directed "continue fixing one by one")

1. **Single-term tile dead zone (Cursor/Canva):** `.ipl-quotes{margin-top:auto}` pinned the price block to the tile bottom for cross-card button alignment, leaving a 214px gap between the "Annual plan" label and its price on tiles without a duration fieldset. Fixed with `:has()`-scoped CSS: the term label joins the price block (gap 214px → 16px), flexible space now sits above the group. Multi-term tiles unaffected (22px). Verified desktop + mobile; tile screenshot reviewed.
2. **"Add to basket"** restyled from plain text to a full-width outlined secondary button (46px, accent border, hover fill), consistent with the primary Buy button.
3. **Tap-to-copy chips** made more prominent (larger padding/font, 12px hint).
4. **Order confirmation table:** prices aligned to row top (was middle — the "detached price" review finding) and long emails wrap tidily on mobile. Correct selectors are the WooCommerce **Blocks** order-confirmation classes; the classic `table.order_details` rule was dead code because the block theme renders order confirmation via Blocks even though checkout itself is the classic shortcode (recorded as MISTAKES entry 75).

Competitor-pattern additions (credox.shop review, owner-approved, same day): a three-step journey strip at the top of checkout (Your details → Pay → Get access) and an amount-on-the-button CTA ("Continue to payment — $30.00 →") that follows cart total, coupons and currency. Verified 7/7 (`ops/cta-strip-verify.cjs`): USD $30.00 → $28.50 after the coupon chip, PKR ₨4,200, strip renders on both viewports, no JS errors. Screenshot: `checkout-cta-strip.png`.

Verification: 6/6 tile checks (`ops/tile-fix-verify.cjs`), full 22/22 live dummy regression (order #196, test-labelled/cancelled, stock/coupon restored) covering the chips in their live state, and 4/4 table checks on both viewports via order #196's key (`ops/order-table-verify.cjs`). Screenshots: `cursor-tile-fixed.png`, `order-table-fixed.png`.

## Corrections to the original review

- Finding #10 (cart icon has no item badge) was an empty-cart artifact: the WooCommerce mini-cart already renders a quantity badge and a contents drawer (items, subtotal, checkout links) for non-empty baskets. A4/C4 required no work.

## Not changed / owner decisions pending

- The former "Available on backorder" drawer issue for Cursor Pro was resolved by the same-day stock setup (managed stock, backorders disabled) — see `ops/STOCK-SETUP-ACCEPTANCE-20261108.md`.
- All 11 mu-plugin deployments were hash-verified transfers; the retained harness gate (lint with positive/negative proof) guarded the only PHP file.

No products, prices, historical orders (beyond cancelled test orders #190/#191), payment settings or database content were changed. Recovery: delete the three `instapass-ux.*` files (new files; no pre-existing server copies).
