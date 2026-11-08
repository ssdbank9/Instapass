# Manage Instapass products and offers

Open [Instapass → Product Manager](https://instapass.online/wp-admin/admin.php?page=instapass-product-manager) for direct duration/price controls. The [full offer editor](https://instapass.online/wp-admin/admin.php?page=instapass-offers) remains available for logos, stock, coupon eligibility and advanced details.

## Quick changes without code

1. Expand the product in Product Manager. Change the duration count/unit and its USD, GBP, EUR or PKR prices.
2. Default inputs are exact full-term totals. To enter monthly rates, choose Monthly rates before entering the new amounts; the preview shows the upfront payment. For6months at72/month, enter72 and the stored full-term amount becomes432.
3. Click Save this duration. The edited section reopens. Active and ready to buy controls that offer; deactivating one offer preserves its active siblings and order history.
4. Use Add a duration within the same product to create another option with inherited logo/access settings. Day/week/month/year choices are supported. Duplicate durations are rejected. Customer labels and signed checkout quotes follow the saved term, even when its amount stays the same.
5. To launch a retired product, use Bring an existing product into the storefront, review the prepared draft and activate it. For a new family, use Add a new product; the advanced editor now registers a genuinely new managed family as a tile automatically.

Cursor/Canva currently show a plain Annual plan label; there is no redundant12months selector box. Claude's customer expiry label shows the date only.

## Current launch storefront — 8 November 2026

The public catalog groups offers into eight product tiles. Claude Pro, Claude Max 5×, Claude Ultra 20× and ChatGPT Plus each have1/6/12-month controls. Canva Pro and Cursor Pro are annual only. Claude Credits and ChatGPT Credits each have a $50–$500 credit-balance slider in $25 steps. Other products remain drafts. All52 launch backend offers now have approved prices.

Open [Instapass → Product Tiles](https://instapass.online/wp-admin/admin.php?page=instapass-product-tiles) to edit tile names/captions, show/hide a launch tile and open each option's price/stock editor. Pro/Plus upfront USD/GBP/EUR totals are15/87/168; Max5x totals75/432/828; Ultra20x totals115/660/1260. At the saved PKR rate280, their rounded totals are4200/24400/47000,21000/121000/231800 and32200/184800/352800 respectively. Cursor annual uses160.71USD/GBP/EUR and fixed45,000PKR; Canva annual uses25USD/GBP/EUR and fixed7,000PKR. A future held option still needs Price reviewed and ready after entering its positive full-term price.

## Credit prices and monthly start day

ChatGPT Credits cost40% of the selected USD face balance, with the same numeric independent GBP/EUR base prices and automatic PKR. They provide one full month. Claude Credits use that same stored top-tier base, then reprice automatically by the remaining time in a shared monthly batch:22+days40%,15–21days35%,8–14days30%,3–7days25%,1–2days20%. The monthly cycle starts on day1 by default and expires at the next monthly start; remaining whole days round up. Short months clamp start days29–31 to their last day. Buying later does not extend the batch expiry.

Open [Instapass → Claude Credit Pricing](https://instapass.online/wp-admin/admin.php?page=instapass-credit-cycle) to change Monthly start day, choose Calendar month or Fixed number of days, edit the five minimum-day thresholds/percentages or pause Claude purchases. Fixed tenure accepts1–31days; if that batch expires before the next monthly start, sales stop until renewal. Defaults remain one calendar month from day1. Save there; no code or LLM is needed. Customer FAQ bands follow the saved thresholds. Checkout refreshes when the price, purchased term or expiry changes; saved orders retain their original terms. TOOLKIT5 still applies. Provider-side delivery/expiry enforcement remains separate fulfilment.

The ChatGPT Credits card in Product Manager changes its access duration and base percentage across every credit balance, with the same numeric USD/GBP/EUR prices and automatic PKR. That group action replaces its current base/sale prices and sale schedule. For independent per-offer/currency overrides, use Products & Offers or Bulk / Excel. Claude expiry percentages scale the stored40% top-tier base, so deliberate owner changes to those bases remain effective. Credit face value is separate from its one-time purchase price.

## Create a monthly, annual or combination offer

1. Choose **New offer**, or select an existing offer under **Start from an existing offer** and choose **Prepare a new draft**. Copying starts a separate draft and does not overwrite the original. In the catalog, use **Add monthly**, **Add 6 months** or **Add annual** to start a new tenor with the same family/logo and blank prices.
2. Enter its name, product/plan family and access duration. Each tenor is a separate backend offer with its own price/status/stock. A genuinely new managed family now gets a tile automatically. For another option on an existing family, prefer Add a duration in Product Manager; that also updates its tile mapping. Unlisted legacy products can be prepared through the existing-product picker. Plan details and activation instructions remain editable.
3. Choose a primary logo and optional second logo. **Choose / upload image** opens the WordPress media library. An uploaded image takes priority; **Clear image** restores built-in logos. Edit the product-card caption and product-page descriptions separately.
4. Enter each tenor's **base price for the full term**, not the monthly equivalent. For an annual price of $840, enter 840. Add a sale total or discount percent; the product tile calculates and displays the price saved. The storefront calculates $70/month and keeps the $840 upfront total explicit.
5. Use the **Active / Deactivated / Private** radio choice and the **In stock / Out of stock** radio choice. Quantity tracking is optional; when enabled, enter the available count. The catalog also has direct **Activate**, **Deactivate**, **Mark in stock** and **Mark out of stock** actions, plus selection checkboxes to activate/deactivate several offers together. Deactivation saves the offer as a draft and keeps its order history. Set any restock date/message and coupon eligibility before saving.

## Prices and scheduled sales

- **USD:** exact regular total and optional sale total. Alternatively enter a discount percentage; on Save it replaces the sale total. The percentage field then clears while the computed sale price remains.
- **PKR:** choose Automatic to use USD × the editable rate, rounded to the nearest Rs 100 per unit. Choose Independent fixed PKR to enter a separate regular/sale price or percentage discount. Changing USD or the exchange rate preserves fixed PKR prices.
- **GBP and EUR:** enter independent regular/sale amounts or a discount for each currency. No USD conversion is performed. A blank regular price keeps that currency unavailable for the offer until priced. The native WooCommerce product editor also has separate EUR fields.
- Optional sale start/end dates apply to sale prices across all four currencies. Times use the WordPress timezone shown on the form. Blank start means immediate; blank end means no expiry.
- For many products at once, use **Bulk / Excel pricing**. Its existing preview, Apply, history and guarded Undo remain available. That screen preserves sale schedules; edit dates in Products & Offers.

## Coupon rules

Open [Products → Promotions](https://instapass.online/wp-admin/edit.php?post_type=product&page=instapass-promotions), or use **Coupon rules & dates** in the editor.

Each of the three bundle slots has an editable code, percentage, status, minimum item count, subtotal rule, start/end dates and eligible-product selection. Leave eligible products unselected for all offers, or select specific offers. The per-offer **Allow coupon discounts** control can exclude an offer from every coupon.

Quantities count. Two units of ChatGPT Plus for one month are two eligible items. The only active bundle is TOOLKIT5: 5% off two or more eligible items, including three, four or more. TOOLKIT75 and TOOLKIT10 are disabled and rejected; their records remain for history. Guests and returning customers qualify. Only one coupon applies to an order. A code discounts eligible lines only; excluded products do not satisfy its item threshold. The three editable slots are retained; re-enabling an old slot would change the current one-bundle policy.

## Customer pricing

New visits default to USD everywhere except the UK, where they default to GBP. A saved manual currency choice takes priority. PKR and EUR are selectable. Independent GBP/EUR prices must be entered for each offer; checkout also needs a payment method explicitly configured for that currency. Adding EUR does not automatically enable payment collection in EUR.

Cards and product pages show current savings against a named comparison and one compact promotion message: Additional5% off with TOOLKIT5, followed by2eligible items required. Cursor and Canva say Annual plan; the redundant lower bundle boxes and separate detail breakdown are removed. The repeated-quantity and one-code fine print is removed from tiles; enforcement still counts repeat quantities and permits one code per order. Checkout compounds sale and coupon percentages. For example, a10% sale followed by a10% coupon is a19% total reduction, not20%. Native basket rounding can change a line by one cent.

These are prepaid duration offers. Renewal availability does not create an automatic monthly charge. Existing receiving/payment instructions are managed separately under WooCommerce → Settings → Payments.
