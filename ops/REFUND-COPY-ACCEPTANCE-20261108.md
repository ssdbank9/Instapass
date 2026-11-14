# Refund copy alignment acceptance — 2026-11-08

Owner approved the exact replacement wording after the full-site review (`ops/SITE-REVIEW-20261108.md`, finding 1). The storefront promise previously said "any issue → full refund, no questions asked", contradicting the Refund Policy amended 2026-10-20 (delivery/access/activation-timing/listing-mismatch only).

## Live scope confirmed before editing

A full-site text scan found the old promise rendered on exactly three surfaces: homepage hero (`patterns/hero.php`), homepage confidence section (`patterns/studio-confidence.php`), and all product pages (`templates/single-product.html`). Shop, FAQ, Contact, How-it-works and the Refund Policy page were already clean.

## Changes (exact strings)

| Surface | Before | After |
| --- | --- | --- |
| Homepage hero footnote | Any issue? Full refund, no questions asked. | Delivery or access issue? Replacement or full refund. |
| Homepage confidence section | Any issue with your order? A full refund, no questions asked. | Delivery or activation issue? Full refund within the 45-minute guarantee window. |
| Product delivery note | No account to create. Any issue at all: full refund, no questions asked. | No account to create. If delivery, access or activation ever goes wrong, you get a replacement or a full refund. |

## Deployment

- Local source updated in `theme-v1.9.0/instapass/` (three files).
- Pre-deploy diff against live files pulled from the server: `hero.php` and `single-product.html` identical; `studio-confidence.php` differed only in the Telegram href (live file `t.me/AJNF48` vs local `t.me/Instapass0408`). Local was synced to the live `AJNF48` value so this deployment could not silently revert server source; rendered hrefs are unaffected because `mu-plugins/instapass-contact.php` normalizes all rendered t.me links to `@Instapass0408` (verified unchanged: 3 homepage links all `t.me/Instapass0408` before and after).
- PHP lint of both edited PHP files passed in the pinned server WordPress image.
- Server-side backup before replacement: `/opt/instapass/recovery/refund-copy-20261108T180500Z/` (hero.php 3284 B, studio-confidence.php 3299 B, single-product.html 4295 B — byte sizes equal the pre-deploy pulled originals).
- Uploads hash-verified (SHA-256 local = transfer = installed): hero.php `1ca57b60…b853`, studio-confidence.php `eb55f5fa…5767`, single-product.html `46f141a4…b77c`.
- Installed with `www-data:www-data` ownership, mode 644. Temporary transfer/pull files removed.

## Verification — 12/12 browser checks pass

Homepage hero and confidence wording updated; both old strings gone; product pages (Claude Pro and Claude Credits) show the new delivery note while retaining the 45-minute delivery sentence; all rendered Telegram links unchanged (`t.me/Instapass0408`); mobile 390px renders with no horizontal overflow; no JS page errors on any probed page. Screenshots: `outputs/site-review-20261108/after-home-hero.png`, `after-product.png`, `after-mobile-home.png`; machine evidence: `outputs/site-review-20261108/refund-copy-after.json`. Verify harness: `ops/refund-copy-verify.cjs`.

## Out of scope / pending owner decision

The Terms of Service page (`/terms-of-service/`) still contains "If there is any issue with your order, you receive a full refund, no questions asked." This is a legal document and was not part of the approved wording set; left unchanged pending explicit owner approval.

No products, prices, orders, payment settings, plugins or database content were changed by this deployment. Rollback: restore the three files from the dated recovery folder.

## Owner follow-up — Terms alignment and canonical Telegram handle (same day)

Owner approved Terms alignment and confirmed the canonical Telegram handle is `https://t.me/Instapass0408`.

Terms changes (rendered live):
- Section 3 Payment (later revised at owner request to drop chargeback terminology — final wording): "If there is an eligible issue with your order as described in the Refund Policy, contact us and we will provide a replacement or a full refund." No chargeback/dispute wording remains anywhere in the theme or mu-plugins (checked); the only occurrence was this sentence.
- Section 6 Refunds: "If there is any issue with your order, you receive a full refund, no questions asked…" → "If your order is not delivered within 45 minutes, does not work, or does not match its listing, we will provide a replacement or a full refund. Contact support with your order number and the refund is returned to the original payment method. Full details are in the Refund Policy."

Canonical handle / dormant-pattern alignment (source hygiene; rendered output was already correct through `inc/contact-update.php`): replaced live `t.me/AJNF48` and `ajnf0408@gmail.com` source values with the canonical handle and `support@instapass.online` in page-contact, faq, studio-faq, spotlight, studio-footer, studio-confidence, page-terms and README.txt; aligned the remaining dormant "no questions asked" wording (faq, studio-faq, page-faq, cta, trust-bar, trust-strip) to the same approved language so re-inserting a pattern cannot reintroduce the over-broad promise.

Deployment: 12 files total (11 patterns + README.txt). Server backup `/opt/instapass/recovery/terms-refund-copy-20261108T183000Z/` (plus README.txt.orig); local/transfer/installed SHA-256 verified for all 12. Deployment harness `ops/run-terms-refund-copy.ps1` — its first run had a broken lint gate (remote pipeline always exited 0 so the script proceeded on LINT_FAIL); all 11 installed PHP files were then linted clean individually with `--entrypoint php` and the harness gate was corrected. No stray `AJNF48` remains in the theme outside the intentional replacement map in `inc/contact-update.php`, which stays as defense-in-depth. All temporary files removed.

Verification — 29/29 live browser checks pass (`ops/terms-refund-verify.cjs`, evidence `outputs/site-review-20261108/terms-refund-after.json`): new Terms wording renders; "no questions asked" absent from home, shop, FAQ, contact, product, Refund Policy, how-it-works and Terms; every rendered Telegram link is `t.me/Instapass0408` on all pages; Contact shows support@instapass.online with no personal Gmail rendered; no JS errors. Screenshot: `outputs/site-review-20261108/after-terms.png`.

Chargeback removal (owner follow-up, same day): page-terms.php section 3 revised to remove all chargeback terminology, backed up as `page-terms.php.before-chargeback-removal` in the recovery folder, hash-verified, live-verified by `ops/chargeback-removal-verify.cjs` (4/4 checks: chargeback absent, new sentence present, refunds section intact, no "no questions asked"; evidence `outputs/site-review-20261108/chargeback-removal-after.json`, screenshot `after-terms-chargeback.png`).

Lint-gate resolution proof: the corrected gate in `ops/run-terms-refund-copy.ps1` was tested both ways — the real page-terms.php passes ("No syntax errors detected") and a deliberately broken PHP file (missing closing paren) fails with exit 255 / "Errors parsing /tmp/x.php", which makes the corrected script abort before install. Broken-file artifact: `outputs/site-review-20261108/lint-gate-broken-test.php`. A stale docker-created directory from the first failed negative-test attempt at `/home/ubuntu/lint-gate-broken-test.php` was removed; all test files cleaned from the server.

No products, prices, orders, payment settings, plugins or database content were changed. Rollback: restore the 12 files from the dated recovery folder.
