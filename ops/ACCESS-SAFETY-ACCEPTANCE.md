# Instapass access-safety notice and country-of-use checkout field

Date: 2026-10-26 (Asia/Karachi)
Production: `https://instapass.online` on `rdi-tracker` (`145.241.115.28`)

## Delivered

- A prominent, fixed bottom notice is visible throughout the public site. It tells customers not to use a VPN with ChatGPT or Claude, explains that masking location can trigger regional checks and that provider-term violations may restrict, suspend or terminate access, and links to official country guidance. The close button dismisses it for the browser session only.
- A separate required checkout selector asks where ChatGPT/Claude will be used. It appears when the cart contains ChatGPT/OpenAI or Claude offers (including Claude Credits), starts blank, accepts only WooCommerce's country codes, and is validated again on the server. It does not replace billing country and does not automatically refuse any country.
- The selected country is stored on the order as private `_instapass_usage_country` metadata, visible in the owner order view and included in WooCommerce order email metadata. It is not added to the public order-tracking result.
- Implementation is an additive must-use plugin and two assets; existing Guest Orders, product, payment, database and Wasooli code were not replaced.

## Verification

- PHP syntax: passed using the site's pinned WordPress CLI image.
- JavaScript syntax: `node --check` passed.
- Isolated WooCommerce clone: passed. The test found 10 current ChatGPT/Claude fixture offers; verified field display and required state, missing/invalid rejection, valid `US` acceptance, private order-meta storage, email metadata, and no field/requirement for a non-AI product.
- Anonymous live browser at 390 × 844: 16 checks passed. Homepage HTTP 200; banner visible, fixed, links present, mobile width fits and session dismissal works; ChatGPT Plus (29), Claude (30) and Claude Credits (144) checkout pages each return HTTP 200 and show a blank country selector with `aria-required=true`, WooCommerce `validate-required`, and PK/US/GB options. No checkout was submitted; no order, payment, account or email was created.
- Production before/after WooCommerce audit is byte-identical for the catalog, orders, coupons, currency options, gateways and tracking-page inventory. The successful additive release deployed these SHA-256 files:
  - PHP `a60d736381ae5951a7c45b528eff05330244bfd5e43b7c7a1fa6d52db822a600`
  - CSS `fd78c1ef41c481442ed35c932361b30dac5da916bed6a75e98a4637a56692a96`
  - JS `99205e802f72884d01bb356d4924e1ffea1db21f357fae7786a8be874b285dc9`
- Recovery copies: `/opt/instapass/recovery/access-safety-deploy-20261026T053556Z` and `/opt/instapass/recovery/access-safety-dismissal-20261026T053740Z`.
- The new test database and internal network were removed by their exact names. A pre-existing test web container/database were preserved, and their shared state pointer was restored to `/opt/instapass/recovery/guest-commerce-test-20261026T052426Z`.

## Corrections during verification

- The first checkout filter was later removed by another checkout-field filter. Moving this addition to priority 999 restored the field after other field normalization.
- WooCommerce's localized country label already includes the country code in this installation; removed the duplicate suffix after the isolated order-email assertion caught it.
- WordPress initially emitted the script before the banner markup, so the first live dismissal test failed. The banner footer hook now runs before footer scripts and the JS waits for DOM readiness. Live dismissal then passed.
- WooCommerce represents this select as required through `aria-required="true"` and the `.validate-required` wrapper rather than the native HTML `required` attribute; the live acceptance checks these actual semantics. Server-side validation also passed in the isolated clone.
- A first clone setup completed its eight existing checkout checks but stopped on a pre-existing `instapass-guest-web` name conflict. The new country assertions were run directly in the timestamped clone instead; no production state was touched. The first inline PowerShell/SSH diagnostic was also rejected locally due shell expansion and was replaced by a saved shell script.

Official guidance linked from the banner: [OpenAI supported countries](https://help.openai.com/en/articles/7947663-chatgpt-supported-countries) and [Anthropic Supported Regions Policy](https://www.anthropic.com/legal/supported-regions). The notice says VPN use is not allowed as the site's customer guidance; it does not claim that every VPN use automatically results in a ban.

## Remaining boundary

Actual customer activation by country, provider account enforcement outcomes, and checkout completion with real payment were not tested. No order was placed. Provider supported-region rules can change; the linked official lists are the source of current availability.
