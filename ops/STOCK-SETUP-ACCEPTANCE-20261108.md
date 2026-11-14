# Stock management setup acceptance — 2026-11-08

Owner requested: everything shows as available (no backorder noise), owner-entered stock that diminishes per order, and a "Limited stock available" indicator when stock is under 10.

## Configuration (data-only change to all 52 priced/published offers)

- Baseline: all 52 offers ran on unmanaged stock (unlimited, backorder display possible — the review saw "Available on backorder" in the mini-cart drawer for Cursor).
- Applied to every priced published simple offer (IDs 29–32, 55, 63, 138–188): `manage_stock = yes`, `backorders = no`, `stock_status = instock`, initial `stock_quantity = 25` (no offer had an existing positive quantity). Per-offer stock is editable by the owner in Instapass → Products & Offers (or native WooCommerce product Inventory).
- WooCommerce low-stock notification threshold raised 2 → 10, so the owner also receives a low-stock admin email when any offer drops to 10 — matching the display threshold.

## Display change (launch-catalog mu-plugin, presentation-only)

- `instapass-launch-catalog.php` `ipl_card()`: when an offer manages stock, is in stock and has 1–9 units, the tile and product-detail quote render `<p class="ipl-stock-note">Limited stock available</p>` before the buy buttons.
- `.ipl-stock-note` styling added to `instapass-launch-catalog.css` (amber pill; contrast bumped after visual review).
- Recovery: `/opt/instapass/recovery/stock-setup-20261108T182000Z/` (previous PHP + CSS). Rollback of data: set offers back to unmanaged stock.

## Verification

- 8/8 storefront checks (`ops/stock-verify.cjs`): all tile buy buttons active; with Cursor temporarily at 5, its tile AND detail page show "Limited stock available" while Claude Pro (25) shows none; mini-cart drawer renders Cursor as a normal purchasable item with no backorder text; buy-now loads checkout with the item; no JS errors. Cursor restored to 25 afterwards and the note disappears at ≥10.
- Live decrement proof — owner-authorized dummy order #192 through the full checkout (2× Claude Max 5× monthly, TOOLKIT5, 1+1 split, country US): 22/22 checks pass (`ops/live-dummy4-stock-check.cjs`, evidence `outputs/live-dummy-order-20261108-stock.json`). Product 31 stock **25 → 23** while the order was live, restored to **25** after the order was cancelled and marked TEST ONLY; coupon usage and all historical orders preserved. This run also re-proved the full UX release (autofocus, chip, collapse, step strip, copy buttons, primary CTA, date hint, sticky amount).
- Evidence: `outputs/site-review-20261108/stock-verify.json`, `stock-cursor-tile-only.png` (badge screenshot).

## Behavior now

- Every purchase decrements the offer's stock; cancelled/failed orders restore it automatically (WooCommerce native, proven above).
- Stock 1–9 → tile + product page show "Limited stock available"; owner gets a low-stock email at ≤10.
- Stock 0 → existing "Out of stock" disabled button (already implemented).
- Owner edits quantities per offer in Products & Offers; nothing else changes.

No prices, orders (other than cancelled test-labelled #192), payment settings or other product data were changed.
