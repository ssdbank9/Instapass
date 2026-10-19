# Instapass Guest Orders 0.2.0

Guest checkout requires no login or account. Receipts and tracking use the required billing email. An optional different activation email is stored privately on the order; blank uses billing email. Owner order details and receipt metadata show the activation destination.

The optional My Account registration uses WooCommerce's emailed password setup and disables automatic authentication on registration. Checkout remains guest-first. No automatic license provisioning is added here.

Track Order at /track-order/ accepts POSTed native numeric order number and billing email, with a nonce, throttling and no-cache/noindex headers. Matching shows limited order items, total and payment state. It never renders a private order key, transfer reference or screenshot. The customer can ask for the private order/payment link to be emailed to the saved billing address. This is protected by a three-request/hour per-order send limit. The public status lookup follows the owner's order-number-plus-email requirement; access to activation secrets remains outside that lookup.

Bundle offers are managed under Products > Promotions: TOOLKIT5 gives 5% for two or more different products; TOOLKIT10 gives 10% for four or more. The owner can edit or disable each separately. Both accept guests and returning customers without first-order or per-customer limits; one coupon applies per order. Multiple copies/variations of one parent product count once. Percentage coupons work across supported currencies; fixed coupons retain the site's USD restriction. Version 0.2.0 removes the earlier First order per email checkbox and validation as explicitly requested. Historical guest-commerce first-order fixtures document the superseded policy; use bundle-offers integration/browser acceptance for current coupon rules.

Payment methods remain owner-configured in WooCommerce Settings, Payments. This plugin adds no receiving account and no paid service. The companion reviewed theme updates supply private payment-return links, clear report/verified states, currency-appropriate proof methods and the activation destination in the confirmation receipt. Reporting a payment never marks it paid.

Rollback: deactivate this plugin and restore only the backed-up theme PHP files. Restore the exact before-values for the six setup options from the protected deployment receipt if required. Keep new customer orders and activation metadata; never overwrite the live database with a prior snapshot simply to undo code. The tracking page can be kept as a draft during rollback. Recovery artifacts and fixture credentials stay outside Git.

Generic WordPress payment notices do not use WC_Email::get_headers. The plugin supplies Reply-To support@instapass.online when no Reply-To is present, preserving explicit headers. Production wc_mail inbox verification confirmed orders@ From and support@ Reply-To with passing SPF/DKIM/DMARC.
