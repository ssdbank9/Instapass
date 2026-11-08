# Managing Instapass pricing

Open WordPress > Instapass > Pricing & Discounts. You control the stored prices. Enter the full-term amount; the site calculates monthly equivalents from the confirmed duration.

For a simple product-by-product editor, use [Product Manager](https://instapass.online/wp-admin/admin.php?page=instapass-product-manager). Expand a product, change duration and currency fields, review the live preview and Save this duration. Default entry is the full-term total; Monthly rates multiplies new inputs by the selected month/year duration. Add a duration creates a mapped option on the same tile. The page also prepares retired products as drafts. Current prices are preserved when installing these controls.

Claude Credit Pricing now edits calendar/fixed-day tenure and the minimum-day thresholds as well as percentages/start day/pause. ChatGPT credit duration and group percentage are editable on its Product Manager card. Customer expiry dates omit the clock/timezone; stored order snapshots retain precise timestamps. Generated tenure descriptions follow later edits so a shortened annual plan does not keep the generated full-year claim.

- **USD:** enter the full-term regular price and optional sale price. Exact amounts such as 840.00 or 173.33 are accepted. You can optionally select .99 endings for generated USD sales.
- **Automatic PKR:** leave fixed PKR fields blank. USD regular and sale prices convert at the saved exchange rate, rounded to the nearest 100 rupees per product unit. Percentage coupons retain minor units at checkout.
- **Fixed PKR:** enter PKR regular/sale values on a product or apply a PKR bulk price/discount. Later USD/rate edits leave these fixed values unchanged. Select products and use Restore automatic PKR to return to conversion.
- **GBP:** enter separate GBP prices and discounts. There is no GBP conversion rate. UK visitors default to GBP when location is available and can choose USD. Without a GBP price, that offer stays unavailable in GBP and offers a USD switch.
- **EUR:** available as the fourth currency from8November2026. Enter separate EUR regular/sale prices or discounts in Products & Offers, native product General fields, or Bulk / Excel pricing. No exchange-rate conversion applies. Blank EUR means unavailable in EUR. Excel templates include EUR rows and import preview/Apply/Undo support it. Payment collection requires a separately configured EUR method.

Current Pro/Plus full-term prices are15/87/168 in USD, GBP and EUR, for1/6/12months respectively. The six- and twelve-month monthly equivalents are14.50 and14; customers pay87 or168 upfront. PKR at280 rounds full-term units to4200/24400/47000. Only TOOLKIT5 is active:5% for2or more eligible quantities, including repeats; no3/4-item higher discount applies. This release supersedes older numeric examples and promotion tiers below.

Max5x is also approved:75/72/69 monthly equivalents for1/6/12months, charging75/432/828 upfront in USD/GBP/EUR; PKR21,000/121,000/231,800. Ultra20x uses115/110/105 monthly equivalents and115/660/1260 upfront; PKR32,200/184,800/352,800. All of these retain the single5% bundle rule for2+eligible units.

Cursor Pro annual is160.71USD/GBP/EUR and fixed45,000PKR. Canva annual is25USD/GBP/EUR and fixed7,000PKR. Those PKR-led annual prices remain fixed if the conversion rate changes; their USD/GBP/EUR prices were derived once at280.

ChatGPT Credits use40% of the selected credit face value for one full month. Claude Credits automatically use40/35/30/25/20% when22+/15–21/8–14/3–7/1–2days remain. Manage the monthly start day (default1), percentages and purchase pause under Instapass > Claude Credit Pricing. This follows calendar months in the WordPress timezone, with short-month day clamping. Credit PKR is calculated from the current USD unit and rounded to the nearest100. Existing orders keep the recorded expiry and price. TOOLKIT5 remains eligible. See ops/CLAUDE-CREDIT-CYCLE-ACCEPTANCE.md.

To change the exchange rate, open Instapass > USD to PKR rate. This affects new automatic PKR quotes; fixed PKR prices and existing orders retain their amounts.

To apply a bulk discount, choose currency, select products if needed, enter the percentage and create a global or selected preview. Review the exact before/after amounts and PKR mode, then Apply. A 0% batch removes a sale. Sale prices and bundle coupons can combine: bundle percentages apply to the current effective price. Unpriced products are skipped.

To use Excel, download the template. Each SKU has USD, PKR and GBP rows. Current columns are reference only. Edit Regular price plus either Discount % or Sale price. Leave unused rows blank. A PKR price/discount row creates a fixed override. A PKR mode auto row with blank price fields restores conversion. Mixed USD/PKR rows derive automatic PKR from the proposed USD price in that preview. Formula cells, duplicate SKU/currency pairs and ambiguous inputs are rejected. Upload, review, Apply. Upload alone does not change live prices.

Recent changes records reviewed rows and the save result. Undo restores original prices, including automatic PKR status, unless a newer edit has superseded the batch. Refresh the preview when prices or the rate have changed. Existing USD sale schedules are retained, so scheduled sales can start later.

Change bundle codes, percentages, required item counts and enabled states in Products > Promotions. Current defaults are TOOLKIT5 at 2 eligible items / 5%, TOOLKIT75 at 3 / 7.5%, and TOOLKIT10 at 4 / 10%. One code per order; guests and returning customers can use them. Quantities count, including repeated copies of the same product and duration. Excluded offers do not satisfy the threshold.

Monthly equivalents, upfront amounts and eligible bundle figures follow current prices and enabled offers. The three original generated tenor price paragraphs also refresh from the selected-currency price. Your custom product prose stays editable.

Previously placed orders keep their recorded amount and currency. These controls do not enable payment methods or create customer orders.
