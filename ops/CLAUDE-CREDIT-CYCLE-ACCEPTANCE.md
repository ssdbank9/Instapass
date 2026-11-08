# Claude expiry pricing and full-month ChatGPT credits — 8 November 2026

## Approved policy and implementation

The owner's latest instructions supersede the earlier flat-credit draft and the proposed fixed28-day cycle. Claude Credits use an automatic calendar-month batch starting on day1 by default. The next monthly boundary is its expiry. The owner can change the monthly start day, pause Claude purchases and edit the five percentages without code. Short months clamp start days29–31 to the month's last day. All boundaries use the WordPress site timezone, displayed in the dashboard and expiry labels. Remaining days round upward; a newly renewed monthly batch uses the top rate.

| Remaining time | Selling price as a share of credit face value | Example for100USD credits |
|---|---:|---:|
| 22 or more days | 40% | 40USD |
| 15–21 days | 35% | 35USD |
| 8–14 days | 30% | 30USD |
| 3–7 days | 25% | 25USD |
| 1–2 days | 20% | 20USD |

ChatGPT Credits remain valid for one full month and use40% of the selected balance. Both families retain the50–500USD-face slider in25USD steps. The owner explicitly kept credit packs eligible for TOOLKIT5, giving a further5% on qualifying baskets of2+eligible quantities, including repeated offers.

The approved stored top-tier base is40% of face value in USD; independent GBP/EUR prices have the same numbers. Claude's remaining-time factor scales those base prices. Automatic PKR converts the newly calculated USD unit and then applies the existing nearest100 rounding. It does not scale an already rounded PKR amount. Fixed PKR overrides, if later entered by the owner, remain independent and scale with the remaining-time factor. Existing owner price controls remain effective; the schedule does not prevent intentional later base-price changes.

The credit tiles show a one-time purchase price rather than a/month rate. Claude shows the actual batch expiry and current percentage; ChatGPT states one full month. The FAQ explains the difference, all five bands, upward whole-day rounding and monthly renewal. No customer-selectable duration packages were added.

## Checkout and saved orders

The cart hash includes the Claude policy fingerprint, batch start/expiry and current rate. The existing signed checkout quote therefore changes when the rate or expiry changes, including a date-only change within the same price band. Checkout requires the updated quote. Order-line metadata stores the actual purchased expiry, start, remaining days, rate and timezone; subsequent schedule changes leave saved orders unchanged. Disabling Claude purchases blocks its prices/purchasability without changing ChatGPT. This manages website prices and order disclosures; provider-side credit delivery and expiry enforcement remain separate fulfilment work.

## Verification

- Initial annual release: Cursor Pro reused draft63 as a new annual tile with fixed45,000PKR and160.71USD/GBP/EUR; Canva55 uses fixed7,000PKR and25USD/GBP/EUR. Seventy-four native assertions and56 live browser checks pass, preserving all credits on hold during policy clarification.
- Revised credit migration:319 assertions pass on the isolated clone and again on production. It prices38 credit offers, approves all52 backend offers across8tiles, seeds only the new cycle option and preserves exact other-product/order hashes, stock and existing rate/promotion/payment settings.
- Isolated policy integration:3,274 assertions pass across every credit face amount, four currencies and seven representative remaining-time points. Covers thresholds, calendar renewal, ordinary/leap February clamping, editable start-day validation, exact coupon totals, one-time presentation, pause behaviour, unchanged ChatGPT pricing, order expiry snapshots and checkout quote invalidation after an expiry-only change. The first reference test omitted PKR rounding and was corrected; a synthetic checkout hook needed its fourth unsaved-order argument and quantity field. No production failure came from these fixture issues.
- Isolated Chrome:699 assertions pass via Desktop Commander, including all slider amounts/currencies, preserved annual and AI prices, expiry labels, mobile/desktop widths, actual guest coupon totals and six simulated date states. The fake clock is confined to the isolated fixture and is not installed on production. Report:outputs/credit-cycle-preview-browser.json.
- Desktop/mobile screenshots inspected. Claude and ChatGPT show distinct expiry terms and one-time prices. Cursor logo and annual prices remain verified from the earlier release.
- Live Chrome:687 checks pass in USD, GBP, EUR and PKR, covering all slider values, current percentages, exact expiry versus full-month labels, actual guest coupon totals, signed quotes, annual/AI price preservation, responsive widths and the expiry FAQ. Report:outputs/credit-cycle-live-browser.json. The later copy-only simplification has a separate preview/live check; it does not change these financial rules.
- Exact test cleanup complete: both superseded/current temporary clone containers and networks, plus their exact local SSH tunnels, were removed after acceptance. Protected recoveries retained.

No real order, customer details, payment or email submitted. Native snapshot checks use unsaved order objects. No new paid service, account, API key or payment gateway.

## Owner steps

1. Open [Instapass → Claude Credit Pricing](https://instapass.online/wp-admin/admin.php?page=instapass-credit-cycle).
2. Change Monthly start day from1to another day if needed. The page shows the current start, expiry, timezone and price percentage.
3. Edit the five percentage fields if your commercial rules change, or untick Enable Claude credit purchases to pause them.
4. Save Claude credit pricing. Permission, nonce and stale-fingerprint checks protect the write. Prices update on subsequent requests; existing orders retain their original expiry and amounts.

For base USD/GBP/EUR prices, stock and tile visibility, keep using Products & Offers, Product Tiles and Bulk / Excel pricing. Cursor/Canva are fixed PKR prices; credit PKR stays automatic unless the owner explicitly sets an override.

## Files and recovery

Runtime:mu-plugins/instapass-credit-cycle.php1.0.0 and instapass-launch-catalog.php1.3.0. The earlier annual release also added scoped credit typography to instapass-launch-catalog.css. Scripts and manifests are underops/credit-cycle-* and outputs/credit-cycle-source-manifest.json. Source hashes are guarded before/after deployment. A new protected full database snapshot plus scoped credit-product/launch-map/settings before-state support rollback; old databases must never overwrite newer orders.

Annual recovery:/opt/instapass/recovery/cursor-credits-release-20261108T110454Z.
Cycle clone recovery:/opt/instapass/recovery/credit-cycle-test-20261108T132757Z.
Live cycle recovery: /opt/instapass/recovery/credit-cycle-release-20261108T133724Z. Migration and both runtime source hashes verified;319 live native assertions pass. Superseded flat-credit and final cycle preview containers/networks/tunnels are removed. The later display-only update is live with grouped-catalog1.3.1; its four source hashes and unchanged product/order/options audit are verified in ops/TILE-COPY-ACCEPTANCE.md.
