# Instapass Control Center 0.6.0

The owner controls full-term USD prices, independent fixed PKR prices or automatic conversion, independent GBP prices, bundle offers and bulk discounts through WordPress. See [the owner guide](../../ops/OWNER-PRICING-GUIDE.md) for screen-by-screen instructions.

- Instapass > USD to PKR rate: editable validated rate; automatic PKR units round to nearest100 before quantity multiplication. Fixed overrides and historical orders are preserved.
- Products > Edit > Product data > General: normal native USD prices; independent GBP regular/sale/discount fields; optional fixed PKR regular/sale/discount fields. Blank PKR means automatic conversion, blank GBP means unavailable.
- Instapass > Pricing & Discounts: global or selected USD/PKR/GBP discounts, random25-30%, Excel/CSV upload, explicit preview/apply/history/guarded undo, restore automatic PKR for selected products. USD amounts accept exact cents; generated .99 sale rounding is optional and off by default.
- Products > Promotions: editable bundle codes, percentages, thresholds and enabled state. Conditional one-code-per-order behavior is retained.

## Spreadsheet

Download the template with one row per SKU/currency. Current amounts and PKR source are reference columns. Editable columns are Regular price, Discount % OR Sale price, optional PKR mode. USD/PKR/GBP are accepted. On PKR rows, price/discount edits create a fixed override. PKR mode auto with blank amount fields restores conversion. Mixed USD and PKR proposals derive PKR from the reviewed proposed USD values regardless of file row order. Blank rows preserve prices; Discount %0 removes a sale. No formulas or duplicate SKU/currency pairs. Maximum500 rows and2MB.

Preview/raw-price fingerprints prevent stale saves. Converted PKR proposals also guard the USD/rate basis; explicit fixed prices are independent. Before each write the journal records the pending row. Partial saves remain inspectable; guarded undo restores raw metadata absence for automatic PKR. Existing sale schedules are retained. The lock is not automatically stolen. Controls do not change stock, visibility, orders or payment configuration.

PKR percentage discounts retain minor units at checkout. The customer sees exactly USD, PKR, GBP. Country defaults: GB=>GBP, PK=>PKR, unknown/elsewhere=>USD; manual choice wins. Missing GBP stays unavailable with a USD switch. New orders retain currency/amount/rate snapshots.

The bundled SheetJS0.20.3 reader is Apache2.0; assets/SHEETJS-LICENSE.txt. Existing provider eligibility restrictions remain; no payment recipient or method is enabled by this release.

## Verification

Release requirements and acceptance receipts: ops/OWNER-PRICING-REQUIREMENTS.md and ops/OWNER-PRICING-ACCEPTANCE.md. Test controls and customer money behavior in an isolated clone with intercepted email; do not use real customer orders to test.
