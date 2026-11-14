# Cart compatibility and checkout presentation — 9 November 2026

## Requirements and outcome

| Requirement | Evidence | Outcome |
| --- | --- | --- |
| Explain screenshot warning | Installed Direct Checkout3.6.10 declares cart_checkout_blocks=false; Cart page8 contained woocommerce/cart | Confirmed administrator-only compatibility warning |
| Resolve incompatibility without hiding notices | Protected original Cart content backup; replace Cart block with editable native WooCommerce classic-cart shortcode | Live; WooCommerce CartCheckoutUtils::is_cart_block_default() nowfalse; Direct Checkout remains active |
| Recover unsupported GBP/EUR checkout | New checkout-only no-method fallback with one USD switch, explicit repricing and review-total guidance | Both currency flows pass, basket quantity and TOOLKIT5 retained after switching |
| Improve payment selector | Additive MU PHP/CSS with native radios, brand badges, selected/focus styling and touch-sized labels | Live; USD Binance/crypto and PKR EasyPaisa pass |
| Research and propose product checklists | Credox first-party product/terms pages compared; contradictions recorded | Draft only in PRODUCT-CHECKLIST-DRAFT-20261109.md; no new product/warranty copy published |
| Preserve commercial data | Exact product/order/commercial-option hashes before release, after release and after browser checks | Unchanged |

## Verification

PHP source lint passes. Native checks confirm classic Cart, actual WooCommerce block detection=false, Direct Checkout active and three decorated payment methods. Desktop Commander Chrome runs85browser assertions across USD/GBP/EUR/PKR: classic cart, quantity2, no compatibility notice, guest TOOLKIT5 coupon and checkout persistence; both GBP/EUR fallback links/repricing/disabled unsupported checkout; quantity and coupon preserved on USD switch; only configured methods, brand badges, one selected radio, native label selection, selected border, keyboard focus, enabled configured CTA and desktop/mobile checkout widths. No checkout/order request attempted; no order/payment/email submitted. Native audits after all browser checks match the pre-release hashes.

Screenshots: outputs/checkout-polish-usd-clean.png and checkout-polish-pkr-clean.png visually inspected; scoped VPN-dismiss button is used only in these disposable visual-test sessions to obtain unobscured images. The public VPN banner remains unchanged. Inputs and no-account checkout behavior remain native.

First browser attempt asserted the final selected-card border colour during an existing150ms transition and failed; waiting for the final computed colour passed all85checks without a CSS/source change. Initial unrelated-source reads were truncated and were narrowed. A direct web fetch of the store Refund Policy was unavailable; draft policy wording uses local policy source/acceptance and still awaits owner review. This is not a new general accessibility, full-store or real payment acceptance claim.

## Recovery and source

Recovery:/opt/instapass/recovery/checkout-polish-20261109T080026Z (private Cart content and commerce audits). Source:mu-plugins/instapass-checkout-polish.php/css. Runner:checkout-polish-release.py; native/recovery helper:checkout-polish-data.php; final read-only audit:checkout-polish-final.py; browser/visual harnesses:checkout-polish-browser.cjs and checkout-polish-visual.cjs. Source-baseline/upload hashes guard deployment. The runner is one-time and refuses existing targets; do not blindly rerun it.

To reverse this release, review the protected Cart backup, preserve any newer owner edits and restore only Cart page content plus remove these two additive MU files. Do not restore an old database or change gateway settings. Earlier unrelated working-tree changes remain outside this release. Current source/docs have not been committed in this installment.
