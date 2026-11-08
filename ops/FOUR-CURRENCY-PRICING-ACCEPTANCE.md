# Four-currency pricing release — 8 November 2026

Status: live on https://instapass.online. Owner supplied the commercial amounts, explicitly chose EUR as a fourth currency and confirmed 5% for two or more eligible items. Existing tile styling retained; scoped typography fixes make savings and monthly equivalents readable. No paid service or payment method was enabled.

## Requirement and result record

| Requirement | Implemented and verified |
|---|---|
| Claude Pro and ChatGPT Plus1month | 15 USD /15 GBP /15 EUR, paid upfront; automatic PKR4200 |
| Both products6months | 14.50 monthly equivalent;87 USD/GBP/EUR upfront; automatic PKR24400 |
| Both products12months | 14 monthly equivalent;168 USD/GBP/EUR upfront; automatic PKR47000 |
| PKR policy | Editable280 PKR/USD; round full-term product unit to nearest100; coupon money keeps minor units |
| Fourth currency | Independent stored EUR regular/sale prices; explicit selection; missing EUR unavailable, no USD fallback |
| Owner controls | EUR added to Products & Offers, native Woo General fields, bulk catalog, preview/apply/undo and spreadsheet template/import |
| Bundle | TOOLKIT5,5%, minimum2eligible units; repeated products count;3/4+units stay5%; guests qualify |
| Retired tiers | TOOLKIT75 and TOOLKIT10 disabled and rejected; coupon records retained |
| Publishing boundary | Exactly6approved price options; other45 options held; seven visible families retained |
| Defaults | Existing USD default outside UK and independent GBP default for UK retained; saved manual choice wins; EUR manual |
| Historical data | Migration verifies exact original order hashes and all non-target product hashes; no repricing of saved orders |
| Site appearance | Desktop/mobile screenshots inspected through Desktop Commander; logos, duration controls and credit sliders retained; no horizontal overflow |
| Copy | Shop/home advertise only active5% code; retired7.5% FAQ sentence and hardcoded guest-admin tier help removed; main currency help includes EUR |

## Tests and evidence

- 199 isolated native WooCommerce assertions: six prices across four currencies, approval boundary, repeated quantities 1/2/3/4, mixed products/tenors, actual coupon calculations, inactive-code rejection, held offers, independent EUR sale/parser validation, unsaved EUR order snapshot, owner editor save/restore, spreadsheet preview/Apply/Undo and exact EUR restoration. External email and HTTP blocked in the clone. Result: outputs/four-currency-native-check.json.
- 190 isolated Chrome browser checks via Desktop Commander: currency selector, all confirmed durations/upfront amounts/monthly presentation, logos, held offers, unchanged credit range, desktop/mobile widths and signed guest checkout quotes. Browser coupon totals for two repeated 15-unit products: 28.50 USD/EUR or 7,980 PKR.
- 190 identical public live browser checks pass; no Place order action, customer details, order, payment or email submitted. Reports: outputs/four-currency-preview-browser.json and outputs/four-currency-live-browser.json.
- 42 live read-only native price/config assertions pass during release.
- 12 final homepage/shop/FAQ checks pass: public 200 responses, no retired coupon text, correct current monthly prices, seven tiles and fresh local visitor USD default. Report: outputs/four-currency-public-check.json.
- PHP lint in the pinned production CLI image; JavaScript syntax and scoped diff whitespace checks pass. All initial ten source hashes verified after release; four follow-up copy-only source hashes verified after their guarded update. Thirteen distinct runtime source paths changed across both steps.
- Production homepage/shop and cohosted Wasooli health probes pass. Existing payment settings and other products/stock were not changed. Exact clone containers, internal network and local SSH tunnel removed; protected recovery retained.

Screenshots:outputs/four-currency-live-default-tiles.png, outputs/four-currency-live-desktop.png, outputs/four-currency-live-usd-mobile-card.png and outputs/four-currency-live-mobile-card.png. Readable mobile card screenshots supplement the long full-page capture.

## Recovery

- Primary: /opt/instapass/recovery/pricing-release-20261108T044147Z contains protected full database backup, targeted product/option state and original sources.
- Copy cleanup: /opt/instapass/recovery/pricing-copy-20261108T044627Z contains previous copy sources.
- Scoped scripts: ops/four-currency-release.py, four-currency-migrate.php, four-currency-rollback.php, four-currency-check.php and four-currency-copy-release.py. Migration refuses already-approved/drifted catalogs; do not rerun it blindly. A rollback restores only this release's touched offer fields/options/sources, never a whole production database over newer orders. A targeted rollback after follow-up owner edits requires fresh review.

## Remaining commercial work

Max5×, Ultra20×, Canva and both credit-selling prices are unapproved. Credit balances50–500 are face values, not sales prices. Provider fulfilment/expiry enforcement and paid checkout were not tested. Payment methods, receiving accounts and currency collection still require the separate owner-approved payment phase. The retained disabled promotion slots remain editable; enabling them would change the current single-discount policy. This release does not claim a full site/security/provider-eligibility audit.
