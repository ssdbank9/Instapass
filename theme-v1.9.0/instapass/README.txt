=== Instapass Aurora Studio ===
Version: 1.9.0
Block theme for WooCommerce shops selling digital licences, subscriptions,
invite keys and bundles. Requires WordPress 6.5+, PHP 7.4+ and WooCommerce.
Local design verification: WordPress 7.1.2, WooCommerce 11.1.2, PHP 8.5.10.
Catalogue verification for 1.9.0: PHP 8.3; native WooCommerce CSV importer.
Licence: GPLv2 or later. Outfit font: included SIL Open Font Licence.

== Install or update ==
Appearance > Themes > Add New > Upload Theme. Choose the supplied ZIP and
install it. Replace the installed Instapass theme when WordPress offers that
option, or activate it if this is a first installation. Keep a site/database
backup and the old ZIP using your normal backup workflow.

Saved Site Editor templates/parts/styles can override theme files. Review
the Front Page and Header/Footer customisations if the old design persists.
Export/save wanted custom content before resetting those particular items.

== Homepage ==
Version 1.9.0 adds purpose-based category navigation, brief tool descriptions,
public unpriced listings labelled Price on request, and backend Enabled toggles.
Products > Pricing & Discounts accepts a regular price plus either discount
percentage or final sale price. Save changes publishes selected edits.
Unpriced enabled listings remain visible; WooCommerce prevents purchasing
until a price is supplied. Disable a product to retain it as a draft.

The new front page uses five editable patterns: Aurora Studio Hero,
Categories, Catalogue, How It Works and Quick FAQ. The hero uses original
generated glass-key artwork with transparent alpha. Text/buttons remain HTML.
Typography and artwork are hosted by the site; no Google Font request.

The category tiles resolve real WooCommerce product category links. Create
slugs licences, subscriptions, invite-keys and bundles to use those links;
a category that is not configured falls back to the configured Shop page.
The catalogue uses actual products, prices, stock and discount data.
An empty catalogue displays a clear message and a real Telegram support link.
No demo products, fake reviews, sales totals or payment-network badges are
installed by this theme.

== Existing features retained ==
WooCommerce product templates, product collections, cart integration,
Buy now button labels, genuine discount badges, real stock/sold indicators,
delivered-order counter block, optional PKR note/settings, starter-page seeder,
brand marks/icons and style variations are retained from v1.6.1.
The homepage no longer includes a large stats strip; the existing counter
block remains available in the editor.

== Starter pages and support ==
Missing Contact, FAQ, Refund Policy, Terms of Service and Privacy Policy pages
are created on activation/the next administrator dashboard visit. Existing
published pages are preserved. Review their wording separately when updating.
Support email: ajnf0408@gmail.com, linked with mailto on Contact and in the footer.
Telegram: https://t.me/AJNF48, retained from the previous theme.
WhatsApp is not displayed until the owner supplies a number. An Instagram
handle has not been confirmed, so no Instagram address has been added.
Saved page content/footer customisations may override these pattern updates.
Displaying the support email does not configure WooCommerce order email or SMTP.

== Changes in 1.7.1 ==
Contact page and footer now show the owner-supplied support email. Removed
the unused WhatsApp placeholder from both places. The Aurora design, artwork,
catalogue and checkout behaviour are unchanged from 1.7.0.

== Changes in 1.8.0 ==
Products > Pricing & Discounts lets managers edit regular prices, sale prices
or percentages. Calculations update as you type; Save prices commits selected
rows to WooCommerce. Refresh open storefront pages to see saved changes.
Percentage calculations round to the nearest .99 by default; untick this
option for exact percentages. Directly entered prices remain as typed.
Badges show the actual rounded discount. Scheduled sales retain their dates
unless Start now is selected. Variable parents derive their child prices.
Stale edits are rejected rather than overwriting newer price changes.

Product > General now includes Tile artwork and Tile colour. Uploaded product
photos take priority. Automatic artwork uses distinct vibrant palettes and
original generated images; prices and product names remain real WooCommerce
text. Supported catalogue SKUs show locally hosted brand logos from Simple
Icons and vendor favicons. Their sources are recorded separately.
ChatGPT details retain trademark attribution and the short not-affiliated line.

== Operational configuration ==
The theme changes presentation; configure payment methods, guest checkout,
SMTP and key delivery in WooCommerce/plugins. Activation within 15 minutes
and the full-refund promise come from the supplied owner brief. Local layout
and cart checks do not prove live payment, email delivery or fulfilment.
Check WooCommerce store visibility when you are ready to accept orders.
Review product edition, region, duration and activation instructions yourself.

== Design verification ==
Local WordPress activation and registered blocks checked; all PHP theme files
parse. Desktop/mobile layout, product search, cart drawer/addition, FAQ,
category and shop links, and empty/populated catalogue checked. These checks
use disposable demo products in a separate local preview outside this ZIP.
No production server, payment, order email or live customer data was used.

== Artwork ==
assets/img/aurora-key.webp is the original generated composition converted
to WebP at its full 1536x1024 resolution (231,876 bytes, transparency retained).
Created with the built-in ChatGPT ImageGen tool. Its interface did not expose
a verified backend model version or model selector.
