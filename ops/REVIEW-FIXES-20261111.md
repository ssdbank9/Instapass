# Reviewer-fixes series — 2026-11-11 onwards

Owner approved the reviewer's verified findings with answers to the policy questions (2026-11-11). One item at a time; this file is the running acceptance record.

## Owner policy decisions (locked)

1. 45-minute clock: starts after payment **and order confirmation** (owner verification).
2. 30-day warranty: **full refund if the account is not working**. The "change of mind" exclusion is to be **removed entirely** — never print that phrase again.
3. Within 30 days: full refund; **after 30 days: prorated refund**.
4. After day 30 on 6/12-month purchases: if the account gets blocked, **prorated refund**.
5. Credits: warranty wording is **all-encompassing** — no credit-specific carve-outs or expiry caveats in the warranty statement.
6. Support commitment: **"Replies within 2 hours, 7 days a week"** — to appear in footer, contact page and order emails.

## Item 1 — shop price sorting (done)

`ipl_grid()` now reads `?orderby=` and sorts tile groups by their cheapest published offer price: `price` ascending, `price-desc` descending; default order untouched. Verified live (`ops/sorting-verify.cjs`): default order unchanged; ascending exactly matches the expected ladder (Pro/Plus 15 → credits 20 → Canva 25 → Max 75 → Ultra 115 → Cursor 160.71); descending correctly reversed (tied-price pairs may swap, acceptable); the WooCommerce sort control submits GET `orderby` values and now takes effect. Recovery: `/opt/instapass/recovery/sorting-20261111T075000Z/`; rollback = restore that file.

Surfaces: `mu-plugins/instapass-launch-catalog.php` only. No prices, orders or products changed.

## Item 2 — warranty/refund unification + support commitment (done)

One policy now, everywhere:

- **45-minute clock** starts at confirmed payment (payment verified + order confirmed), stated explicitly.
- **30-day account warranty**: if the account is not working within 30 days of activation → **full refund or replacement**. No credit carve-outs (warranty wording is all-encompassing). The "change of mind" phrase is gone from every surface and must not return.
- **After 30 days** on 6/12-month purchases: unused portion refunded **prorated**; provider side help still offered.
- **Support**: "Replies within 2 hours, 7 days a week" in footer, contact page and every transactional email footer.

Rewritten: Refund Policy (new lead, "When the activation clock starts", "30-day account warranty", "After the first 30 days"; the old "Subscriptions after 7 days" section removed); FAQ guarantee answer + contact block (now support@instapass.online, WhatsApp, 2-hour promise — live FAQ renders from the DB page? confirmed rendering after file update); Terms §6 refund clause; product delivery note ("ever goes wrong" replaced by the 30-day warranty line); Contact page and footer (DB: page 22 and footer template part 132 updated — revert = delete the inserted "Replies within 2 hours…" paragraph blocks); WooCommerce email footer text set to the branded support line (with logo header #7c3aed branding from the same day).

Verified live 7/7 (`ops/warranty-verify2.cjs`): refund page (clock, full/30-day, prorated, no change-of-mind, no 7-day section, 2-hour promise), FAQ (unified answer, support@, no old Gmail), Terms, product page, contact page, footer. Recovery: `/opt/instapass/recovery/warranty-unify-20261111T081500Z/` for the six theme files; DB revert instructions above. Note: contact/footer are DB-rendered (page 22 / template part 132) — theme pattern edits alone do not affect them.

## Item 3 — credit-page heading follows the selected balance (done)

The H1 no longer goes stale: on Claude and OpenAI credit product pages the heading is normalised to "… — $N credits" from the selected slider value, updating live on every slider move (delegated listener), with the server-rendered base title cached in a data attribute and dash/whitespace normalised so the label stays single-dash. Verified live (`ops/credit-heading-verify.cjs`): Claude API Credits $100 default → $250 → $50 all match the slider; OpenAI API Credits $50 default → $500 matches. File: `mu-plugins/instapass-ux.js` only.

## Items 4 & 5 — stale homepage message and empty categories (done)

- The spotlight "Products will appear here as they are added / Your next upgrade is on its way" block is now suppressed whenever the grouped catalogue exists (`ipl_groups()`), and still renders in a genuinely empty catalogue. Homepage verified free of both strings.
- Category chips now skip product-category terms with zero published products (`term->count < 1`): the row went from 9 chips to 5 (All tools, AI tools, Productivity, Creativity, Development). Verified every remaining chip returns 200 AND actually shows tiles (7 / 7 / 1 / 5 tiles respectively, no "No matching products").
- Files: `theme …/inc/catalogue.php`, `patterns/spotlight.php`. Recovery `/opt/instapass/recovery/cats-spotlight-20261111T090000Z/`. Evidence: `ops/cats-spotlight-verify.cjs`, `ops/cats-content-verify.cjs`.

## Batch items 6–13 — reviews, tracking, related, promo, credits, chip minimum, a11y, mobile header/VPN, nav (done, 13 Nov)

- **Reviews (0) hidden**: the WooCommerce reviews tab link is hidden while the count is zero (returns automatically once a review exists).
- **Tracking page**: duplicate H2 removed (page H1 kept); the "Track order" button now uses the site's primary pill styling.
- **"You may also like"**: related grid now shows exactly 3 tiles (server-side slice on product pages); main product card excluded from chips.
- **Contextual TOOLKIT5**: promo box gains a status line — with 1 item "Add one more eligible item to unlock 5% off with TOOLKIT5"; with 2+ "TOOLKIT5 is available — apply it above in one tap"; applied "TOOLKIT5 applied — you saved $X". Updates on every checkout refresh.
- **Credit pages**: quick-select buttons $50/$100/$250/$500 beside the slider plus a live summary "You receive $250 credits · You pay $87.50 · Expires 1 Dec 2026" (pay reflects the current cycle rate; verified live).
- **"From" chip → true minimum**: now computed across ALL terms, not the selected one — verified: Claude Pro "From $14.00 /month" (annual equivalent), Max $69, Ultra $105, ChatGPT $14, Canva $2.08, Cursor $13.39, Claude Credits $17.50 (current-rate minimum), OpenAI Credits $20.00; selecting 12 months leaves the chip unchanged. Chip intentionally omitted on the main product card (full pricing shown there).
- **Accessibility**: focusable descendants inside aria-hidden containers removed from tab order; muted-copy contrast darkened; tax-strip text darkened.
- **VPN notice compacted** (still site-wide, still dismissible): short headline "Use without a VPN — your country must match where you will use the service." with "Why this matters" disclosure holding the full text and provider links. Height 227px → 126px on mobile.
- **Mobile header compression**: tax strip hidden on ≤600px and announcement/currency bands tightened; header height ~356px → 158px, 44px tap targets retained, no horizontal overflow.
- **"Track order" added to the main navigation** (theme header pattern; confirmed rendering live).

Files: `mu-plugins/instapass-ux.js`, `instapass-ux.css`, `instapass-launch-catalog.php`, `instapass-access-safety.php`, theme `patterns/studio-header.php`. Recovery: `/opt/instapass/recovery/reviewer-batch-20261113T073000Z/`. Evidence: `ops/reviewer-batch-verify.cjs`, `ops/chip-minimum-verify.cjs`, `ops/chip-debug.cjs`. Two nav/test runs hit transient QUIC/connection-reset errors from the local network; verification scripts now launch Chrome with `--disable-quic`.

## Homepage products upfront — categories section removal + compaction (done, 13 Nov)

Owner judged the "Find your kind of upgrade" section (four Licences/Subscriptions/Invite keys/Bundles cards + category pills) redundant — legacy of the 85-listing catalogue; two of the four cards had no matching products — and asked for products visible on site load.

Changes:
- Removed the `instapass/categories` pattern from `front-page.html` and `patterns/home.php`. Homepage flow is now hero → product grid directly. The pattern file remains for rollback/other use.
- Removed the homepage category-pill injection (`instapass_home_tool_nav` filter in theme `inc/catalogue.php`); Shop and category archives keep their functional pills.
- Homepage-scoped CSS compaction: hero padding/heading/lead tightened, hero artwork capped at 300px and hidden on mobile, products section head compacted (kicker + "Browse all products" link hidden on mobile home), 44px touch targets kept.
- **Bug found and fixed during verification**: the credit-heading enhancement was appending "— $50 credits" to the HOMEPAGE hero H1 (the grid's credit tile has a slider too). All three credit enhancements (H1 sync, quick-selects, summary) are now gated to `single-product` pages only. Credit product page re-verified intact ("Claude API Credits — $100 credits", 4 quick-selects).
- Mobile VPN bar slimmed (75-76px) with the provider links moved inside the "Why this matters" disclosure and the summary made inline; still site-wide, dismissible, no overflow.

Measured results (before → after, first product tile top position on load):
- Desktop 1440×1000: 1237px (invisible) → **758px** — 135px of the first tile visible above the banner; hero 723px → 403px.
- Mobile 390×844: 1326px (invisible) → **654px** — tile badge + logo visible above the dismissible VPN bar; hero 738px → ~319px.
- Final screenshot visually inspected: clean hero headline (bug gone), tile present, banner tidy, no clipping/overlap.

## Compact currency bar + new geo currency policy (done, 13 Nov)

Owner asked to shrink the top currency bar and change first-visit defaults to: Pakistan → PKR, Europe → EUR, everywhere else → USD.

- **Currency bar compaction**: tight single-row strip — desktop 42px, mobile 54px (label hidden on phones; the active pill shows the currency; pills 38px on touch devices). Tiles-upfront positions improved again (desktop 758→722, mobile 654→621).
- **Geo default policy** (`instapass_cc_country_currency`, Control Center `includes/currencies.php`): PK→PKR; EU-27 + EFTA (IS/NO/LI/CH) + AD/MC/SM/VA + AL/BA/ME/MK/RS/XK/UA/MD → EUR; GB keeps its independent GBP default (owner to confirm if UK should join the EUR bucket); everything else/unknown → USD. Manual selection and the 30-day cookie still win; existing baskets keep their currency.
- **Verification**: 22 server-side mapping checks pass (PK, 11 European, GB, 10 rest-of-world/unknown). Live EUR rendering confirmed (€15.00 quote, "From €14.00 /month") and PKR confirmed (₨4,200 quote). Physical PK/EU-origin browser traffic not tested — mapping verified via server eval, consistent with prior releases' scope notes.

Recovery: `/opt/instapass/recovery/currency-policy-20261113T083500Z/`. Evidence: `ops/currency-map-check.php`, `ops/currency-bar-verify.cjs`. Note: the PKR annual "₨3,916.67 /month" figure is the site's existing monthly-equivalent presentation (full-term ÷ months), not a new calculation.

## Smaller pricing banner, single tax line at top, Ultra renamed (done, 13 Nov)

Owner requests: make the pricing banner even smaller; remove "20x" from the Ultra name; show the tax sentence only once, at the top.

- **Tax text once at the top**: `ip_tenor_tax_message()` now returns the owner's exact wording ("We absorb applicable taxes—no extra tax at checkout. Any tax saving is additional to the discount percentage shown."), rendered only by the `wp_body_open` strip. The per-quote `.ip-offer-tax` line was removed from `ip_offer_official_savings_html` (offer-manager.php), and the tax sentence was removed from the checkout prepaid note (term explanation kept). Mobile no longer hides the strip (34px, visible; 20px desktop). The FAQ's explanatory tax answer is unchanged documentation.
- **Pricing banner smaller**: `.ip-offer-savings` 0.76rem→0.66rem (measured 10.56px), saving lines ~0.7rem, official reference 0.6rem with tighter line-height — plus the removed tax line.
- **Ultra renamed** (data change via `ops/ultra-rename.php`, before/after recorded): group name "Claude Ultra 20×"→"Claude Ultra"; group caption "Our Claude 20× tier offering…"→"Our Claude Ultra tier offering…"; product titles 32/149/150 dropped " 20×" (e.g. "Claude Ultra — 12 months"). Slugs, SKUs, prices, stock, meta untouched; historical orders keep their own stored names. FAQ contains no 20× references; Max retains its correct 5× naming.
- **Verification**: Ultra tile reads "Claude Ultra"; product H1 "Claude Ultra — 12 months"; savings banner shows Save/coupon/official-reference with no tax line; the new tax sentence appears exactly once (top strip) on shop/home/product/FAQ; banner font measured 10.56px; mobile strip visible at 34px. Incidental confirmation of the new geo policy: this test connection was detected as PK and rendered PKR defaults (₨ quotes, Rs 280 reference note).

Recovery: `/opt/instapass/recovery/banner-tax-ultra-20261113T090000Z/`. Evidence: `ops/ultra-rename.php`, `ops/banner-tax-ultra-verify.cjs`.

## Shop pills removed, green saving enlarged, 25% usage highlight, FAQ copy update (done, 13 Nov)

- **Shop category pills removed** (All tools / AI tools / Productivity / Creativity / Development) — the eight tiles are the catalogue; removed the query-title nav injection and the dead filter in `inc/catalogue.php`. Verified live: `.ip-tool-tabs` count 0.
- **Green saving line enlarged**: `.ip-offer-saving-now` → 15.2px / weight 800 (verified rendered value).
- **25% usage highlight pills** added to Claude Pro and Claude Max 5× tiles: "✦ 25% more usage than standard plans". Ultra has no pill (not eligible).
- **"20×" removed from Ultra** tile badge (now "ULTRA") — shop scene contains no "20×"/"20x" anywhere.
- **Pricing banner smaller**: `.ip-offer-savings` 0.76rem → 0.66rem with tighter line-height.
- **FAQ "Are these official accounts…" answer replaced** with the owner's exact wording, verified live: "These are official provider plans and are activated on the email you supply, with full private access. Claude Pro and Claude Max 5× offers include 25% additional usage versus the standard plan, so Pro gives 1.25× and Max 5× gives 6.25× usage. Check each listing for its plan, duration and activation details." Backup of page 17: server `/opt/instapass/recovery/faq-copy-20261113/` and local outputs.

Recovery: `/opt/instapass/recovery/five-fixes-20261113T093000Z/`. Evidence: `ops/five-fixes-verify.cjs`, `outputs/site-review-20261108/shop-five-fixes.png`.

## Ultra and Max caption update (done, 13 Nov)

Owner directive: Ultra caption should use the standard Pro wording as the base; position Ultra for maximum limits/coding; sweeten with monthly API credits mention; position Max for advanced users at 6.25×.

Live now:
- **Claude Ultra**: "An AI assistant for writing, analysis and everyday work — for maximum limits and heavy coding. Every account includes monthly API credits (limited time offer)."
- **Claude Max 5×**: "For advanced work — 6.25× usage and heavier loads."
- **Claude Pro**: unchanged — "An AI assistant for writing, analysis and everyday work."

Data-only change in `instapass_launch_groups` option (group captions); backups `instapass_launch_groups_before_20261113_captions` / `..._captions2` stored on the server before writing. Verified live with Playwright (three tiles read the new captions). No prices, stock, slugs, SKUs or other copy changed.

Note: Max mentions 6.25× — owner-stated usage multiplier; not independently provider-verified (same status as the previously documented "25% additional usage" claim).
