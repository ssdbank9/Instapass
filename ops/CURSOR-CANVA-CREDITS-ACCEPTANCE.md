# Cursor, Canva and credit pricing — 8 November 2026

Historical preparation record. Cursor and Canva annual offers were released with74 native and56 live browser checks passing. Their recovery is /opt/instapass/recovery/cursor-credits-release-20261108T110454Z. The flat Claude-credit candidate below was superseded before credit publication by the owner's automatic monthly-expiry instructions. The final credit implementation is documented in ops/CLAUDE-CREDIT-CYCLE-ACCEPTANCE.md; do not treat this original flat40% table as the current Claude policy.

## Requirements

| Owner requirement | Implementation |
|---|---|
| New Cursor Pro annual tile | Reuse existing retired annual draft63, publish as Cursor Pro, show built-in Cursor logo, annual option only, insert after Canva |
| Cursor PKR45,000 | Store fixed PKR45,000; derive USD160.71 once at280, independently set GBP160.71 and EUR160.71 |
| Canva annual PKR7,000 | Existing offer55; fixed PKR7,000; USD/GBP/EUR25.00 each |
| Credit prices40% of selected face value | Both families; all19 amounts from50 to500 in25steps, selling at20 to200 in10steps |
| Currency policy | Credit USD selling price is40% of face value; independently store same numeric GBP/EUR; automatic PKR at current280 rate with existing rounding |
| Bundle clarification | Owner explicitly retained TOOLKIT5 eligibility for credits;100credit face costs40 normally or38 when qualifying for5% on2+items |
| Claude credit duration later | Add no duration options; preserve the existing1month/monthly-expiry metadata and wording |
| Credit price presentation | Show one-time purchase price and face balance; replace the inherited monthly-price presentation; no misleading cross-currency savings comparison |
| Preservation | Existing twelve AI prices/readiness, historical orders, stock, rate, promotion settings and payment configuration unchanged |
| No-code owner control | Existing Products & Offers, Product Tiles and Bulk / Excel controls continue to manage all prices/visibility; 40% is the approved stored selling price, not a forced rule that prevents later owner edits |

Eight public tiles contain52 backend offers, all approved after this release. The Cursor annual offer already existed as a draft, so no duplicate product row is created. Only two runtime presentation sources change: grouped-catalog MU PHP1.2.0 and its scoped CSS. Credit balances are USD face values even when an independent GBP/EUR selling price is selected. The60% face-value saving is shown only for USD and its converted PKR equivalent; it is not presented as a GBP/EUR foreign-exchange comparison.

## Verification record

- Source hash guards verify both current live files before overlay/deployment. PHP lint in the pinned production CLI image and JavaScript/Python syntax checks pass.
- Isolated migration:454 assertions pass. All52 launch offers approved,40 target offers priced, four currency values/PKR sources correct, coupon eligibility intact, existing credit expiry/duration retained, other product/order hashes and stock/options preserved.
- Isolated native cart:492 assertions pass. All38 credit offers purchasable in four currencies with the40% formula; one-time price HTML has no/month rate; annual duration/full-term totals correct; one credit item fails2-item coupon minimum, two credit families pass with total76 or PKR21,280; Cursor+Canva annual bundle totals176.42 or PKR49,400.
- Original flat preview browser:737 checks passed and screenshots were inspected. These are historical prototype results; Claude now follows the later expiry schedule.
- Annual-only live verification:74 native and56 browser checks pass. Credit release and current verification are recorded separately in ops/CLAUDE-CREDIT-CYCLE-ACCEPTANCE.md.

No real order, customer details, payment or email submitted. Isolated external HTTP and mail disabled. Browser cart checks create only temporary guest sessions and do not click Place order.

## Deployment and recovery

Scripts:ops/prepare-cursor-credits.py, cursor-credits-data.php, cursor-credits-check.php, cursor-credits-release.py and cursor-credits-browser.cjs. Manifest:outputs/cursor-credits-source-manifest.json. The migration refuses changed source hashes, altered readiness/mapping/rate and a reused backup path. A protected database snapshot and scoped product/meta/launch-map before-state are taken before live writes. Failure uses a scoped rollback and original presentation sources; do not replace a production database containing newer orders with the old snapshot.

Protected original clone recovery: /opt/instapass/recovery/cursor-credits-test-20261108T105932Z. That superseded clone/network and its tunnel were removed/replaced during revised-policy testing. Annual live recovery: /opt/instapass/recovery/cursor-credits-release-20261108T110454Z.

## Deferred work

Claude credit-duration choices are explicitly deferred. Existing monthly-expiry wording is not proof of provider-side enforcement. Provider fulfilment, actual credit delivery, payment collection and a paid end-to-end order remain outside this pricing installment.
