# Instapass Control Center 0.5.0

Owner dashboard with an editable USD-to-PKR rate, independent GBP prices, customer currency selection, and guarded bulk-price previews. Requires WooCommerce and PHP 8.1+. USD remains WooCommerce's base price.

## Owner controls

- **Instapass → USD to PKR rate:** positive amount, up to two decimals. Saving requires `manage_woocommerce`, a nonce, and an unchanged settings fingerprint; the last 30 changes retain owner ID and time.
- **Products → Edit → Product data → General:** independent GBP regular and sale prices, plus optional one-time discount percentage. GBP is never converted from USD. Blank means unavailable; it is distinct from an explicit zero sale/100% discount.
- **Instapass → Pricing & Discounts:** preview global fixed discounts, random 25–30% discounts for all or selected products, or import up to 500 Excel/CSV rows. USD operations update WooCommerce base prices. GBP operations update only `_instapass_gbp_regular_price` and `_instapass_gbp_sale_price`. PKR normally derives from USD and the saved rate. Optional fixed PKR regular/sale prices and a percentage action are available in each product editor; these are not changed by USD/GBP bulk actions.

Every batch is previewed before Apply. The owner-scoped history records row-level before/after values and supports guarded undo while the current prices still match the batch. Partial or ambiguous saves enter manual review. Existing sale dates are preserved; scheduled prices may not start immediately.

### Spreadsheet format

Columns: SKU, Currency, Regular price, Discount %, Sale price. Currency is set per row and must be USD or GBP. A SKU may occur once in USD and once in GBP; duplicate SKU/currency pairs are rejected. Enter a discount or sale price per row, not both. Blank price rows are skipped; Discount % set to 0 removes the sale. Formula cells, unknown SKUs, unsupported products/currencies, malformed amounts, and stale previews are rejected. Keep values to two decimals and omit currency symbols.

USD regular and manually entered sale prices normally end in .99. A product editor checkbox, Allow exact USD prices, permits owner-approved amounts such as173.33,833.33 and75.00 for selected listings. Generated USD sale prices use a .99 ending when it fits the selected discount rule; otherwise cents are retained. GBP uses exact cents. Random discounts remain within an actual 25–30% range.

The bundled SheetJS 0.20.3 workbook reader is distributed under Apache License 2.0; see `assets/SHEETJS-LICENSE.txt`.

No gateway or recipient is enabled by the bulk-pricing feature. Friend-owned personal Wise/Revolut collection remains disabled pending provider eligibility.

## Customer behavior

- The storefront offers exactly USD, GBP, and PKR. First visit: UK/GB → GBP, Pakistan → PKR, other/unknown → USD. A secure cookie remembers the choice for 30 days.
- GBP requires independent owner-entered prices. Missing GBP listings remain unavailable rather than converted or free.
- Derived PKR product units round half-up to nearest Rs100 before quantity multiplication. A valid fixed PKR override takes priority and remains independent of exchange rate and USD/GBP changes.
- New orders store their original currency and PKR rate/policy when applicable. Separate currencies must be reconciled separately in sales reporting.
- Bulk pricing changes only price and sale metadata. It does not change stock, visibility, product descriptions, orders, or payment settings.

## Verification and recovery

The 0.3 currency regression suite and a bulk-pricing integration suite are acceptance gates before production installation. Run the isolated WooCommerce clone and browser checks; never use a real order to test payment. Keep the existing pre-0.3 backup/rollback and preserve newer orders and GBP metadata.
## Fixed PKR override (0.5.0)

Products > Edit > Product data > General includes optional fixed PKR regular/sale prices and a one-time percentage. Leave both blank to restore conversion. Saving requires owner capabilities, a nonce and an unchanged price fingerprint; unrelated imports/quick edits do not clear values. Bundle percentage coupons still apply. A fixed PKR product has its own sale status. Invalid overrides fail closed. The selected amount is saved on the order; historical orders are never recalculated.
