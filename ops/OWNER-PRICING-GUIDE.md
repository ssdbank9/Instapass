# Managing Instapass pricing

Open WordPress > Instapass > Pricing & Discounts. You control the stored prices. Enter the full-term amount; the site calculates monthly equivalents from the confirmed duration.

- **USD:** enter the full-term regular price and optional sale price. Exact amounts such as 840.00 or 173.33 are accepted. You can optionally select .99 endings for generated USD sales.
- **Automatic PKR:** leave fixed PKR fields blank. USD regular and sale prices convert at the saved exchange rate, rounded to the nearest 100 rupees per product unit. Percentage coupons retain minor units at checkout.
- **Fixed PKR:** enter PKR regular/sale values on a product or apply a PKR bulk price/discount. Later USD/rate edits leave these fixed values unchanged. Select products and use Restore automatic PKR to return to conversion.
- **GBP:** enter separate GBP prices and discounts. There is no GBP conversion rate. UK visitors default to GBP when location is available and can choose USD. Without a GBP price, that offer stays unavailable in GBP and offers a USD switch.

To change the exchange rate, open Instapass > USD to PKR rate. This affects new automatic PKR quotes; fixed PKR prices and existing orders retain their amounts.

To apply a bulk discount, choose currency, select products if needed, enter the percentage and create a global or selected preview. Review the exact before/after amounts and PKR mode, then Apply. A 0% batch removes a sale. Sale prices and bundle coupons can combine: bundle percentages apply to the current effective price. Unpriced products are skipped.

To use Excel, download the template. Each SKU has USD, PKR and GBP rows. Current columns are reference only. Edit Regular price plus either Discount % or Sale price. Leave unused rows blank. A PKR price/discount row creates a fixed override. A PKR mode auto row with blank price fields restores conversion. Mixed USD/PKR rows derive automatic PKR from the proposed USD price in that preview. Formula cells, duplicate SKU/currency pairs and ambiguous inputs are rejected. Upload, review, Apply. Upload alone does not change live prices.

Recent changes records reviewed rows and the save result. Undo restores original prices, including automatic PKR status, unless a newer edit has superseded the batch. Refresh the preview when prices or the rate have changed. Existing USD sale schedules are retained, so scheduled sales can start later.

Change bundle codes, percentages, required offer counts and enabled states in Products > Promotions. Current defaults are TOOLKIT5 at 2 offers / 5%, TOOLKIT75 at 3 / 7.5%, and TOOLKIT10 at 4 / 10%. One code per order; guests and returning customers can use them. Different durations of one product can count separately; extra copies of one duration count once.

Monthly equivalents, upfront amounts and eligible bundle figures follow current prices and enabled offers. The three original generated tenor price paragraphs also refresh from the selected-currency price. Your custom product prose stays editable.

Previously placed orders keep their recorded amount and currency. These controls do not enable payment methods or create customer orders.
