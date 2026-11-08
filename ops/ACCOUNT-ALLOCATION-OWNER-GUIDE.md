# Account allocation

Open [Products & Offers](https://instapass.online/wp-admin/admin.php?page=instapass-offers), select an offer, and use **Repeated units: account allocation** under Product & duration.

- **Default** enables the confirmed original Claude Max 5x one-month offer only.
- **Allow consecutive terms or separate accounts** enables a private offer with a whole-month/year duration. Enable this only where you can fulfil consecutive terms for that product.
- **Disable** preserves the ordinary activation flow. Changing this does not rewrite existing order instructions.

For two or more units of an enabled offer, checkout requires **all months on one account**, **one account per purchased unit**, or **custom split**. Custom split has a **Number of accounts** selector: two accounts show two activation-email fields, three show three, and four show four. A balanced distribution of purchased units is proposed automatically and can be edited, including3+1,2+2or2+1+1for four monthly units. Reducing the count hides/disables extra rows; increasing it restores their entered emails. Without JavaScript, fill the chosen number of rows and leave the others blank. Each active row has existing/new and an activation email. Single-account blank email uses the order's optional activation email or receipt email. Split accounts need distinct email addresses per offer; different products may use the same email. New-account selection means the supplied email should receive activation, not that the website creates a vendor account automatically.

All purchased units count for an eligible coupon regardless of the account split. The split does not choose a different tenor price or pool units across products. Checkout cannot accept an incomplete, fractional, excess or stale split. No passwords are requested. More than100 destination accounts for one offer requires support.

Open a WooCommerce order and read **Account activation instructions** under each item. The saved line records unit duration, quantity, months, account type and email. Follow these item instructions in preference to the default order-level activation email. Confirm the payment through the existing verification flow before fulfilment. This feature records the customer's request; it does not activate subscriptions or schedule provider renewals.

The private order page and normal order receipts show account instructions. Limited order-number/email tracking shows only the duration split and account count, alongside the existing total/discount information. Receipt destination stays the billing email. Full destination details are not shown in the limited tracking result.

The [FAQ](https://instapass.online/faq/) explains all five partitions of four one-month units. Guest checkout and optional site accounts remain available.

Rollback is code-only: restore the eight original runtime paths from the recorded timestamped recovery directory, remove newly introduced module/assets only if the original manifest says absent, and preserve all new orders and their private allocation metadata. Do not restore an old database to undo this feature.
