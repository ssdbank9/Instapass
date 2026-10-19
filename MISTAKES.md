# Instapass canonical lessons log

This is the canonical Git-tracked mistakes and lessons record for the Instapass project. The owner requested this project-local canonical location on 2026-10-18. Maintain new Instapass entries here rather than in pending global-log files. Other projects' global records are outside this scope.

Read the applicable global rules and relevant entries here before implementation and handoff. Preserve prior entries, label confirmed defects separately from risks and environment limitations, and retain evidence and verification status. Never commit credentials, tokens, private order data, SSH keys or customer payment evidence.

Historical entries below preserve their original status and staging notices as of their recorded dates. Later verified follow-ups supersede earlier pending statements; historical notes are not a claim about current production.

## Consolidated source: pending-global-mistakes.md

# Pending update to the global mistakes log

Canonical file: `C:\Users\Aly Jafferani\.codex\mistakes.md`. This update is staged because that file is outside this task's permitted write roots. It has not been merged into the global log.

## INSTAPASS-20261007-15 - Automation test harness assumptions were corrected

- Date / project: 2026-10-07; Instapass owner pricing preview screen.
- Category / status: Harness assumptions corrected; one audit-history defect corrected and regression-covered. Real WordPress integration remains pending.
- Evidence / impact: Root-launched scripts first failed on cwd-relative paths. Later harness runs exposed a missing mocked `WP_REST_Server::READABLE`, stale assertions for the preview-only UI and old REST URL shape, a fake database row without schema-default nullable timestamp columns, and an incorrect expected history count. During that exercise, an actual source issue was found: an operation with unreadable stored payload was omitted from the owner history, hiding its manual-review state. One `PROJECT-DECISIONS.md` patch also failed on guessed context; no file was partially changed, and the exact section was reread before correction.
- Cause: Harness paths were based on cwd; fake WP/DB fixtures lagged behind newly registered endpoints/schema defaults; test expectations lagged behind workflow changes; history rendering assumed every stored payload decoded successfully.
- Correction / prevention: Resolve paths from `import.meta.url`; mirror route constants and nullable DB defaults in mocks; update assertions with each workflow change; localize complete REST URLs; retain a minimal manual-review history row when payload JSON is corrupt rather than hiding the audit record.
- Verification: Root-launched PHP-WASM syntax, admin/preview harness, workflow lifecycle harness and JavaScript syntax checks pass. The mocked lifecycle covers activation schema text, permissions, saved preview, explicit confirmation, duplicate confirmation, assistant product lock, expiry, stale/corrupt preview refusal, audit history, guarded undo and manual-review failure lock. Real WordPress/MySQL integration and live product data remain unverified.

## INSTAPASS-20261005-08: Stock, checkout and presentation redesign

- Date / project: 2026-10-05; Instapass v1.9.0 requested redesign.
- Category / status: Inspection and interpretation corrections; implementation and live verification pending.
- Evidence / impact: Broad rg enumeration included the persisted WordPress vendor tree and truncated output. A guessed checkout template path did not exist. Competitor product-card clicks initially stayed on the homepage while a promotion overlay appeared; fresh screenshot revealed the modal and direct navigation used the observed product URL.
- Cause / prevention: Restrict searches to theme code or named vendor files, bound results, discover template names before reads, and verify navigation rather than assuming a click opened the product. Existing global output-size lesson recurred.
- Copy constraints: Claiming only an email is collected would conflict with requested optional phone, order records and standard technical/cart data. Use no website account required and no monitoring of tool-account activity, with truthful email/optional phone/order disclosure. Owner clarified renewal is customer confirmation followed by transfer/Binance; no automatic charge, saved card or website account.
- Source exception: Adobe Creative Cloud is explicitly shared and unavailable in the XLSX. Preserve that exception rather than writing unconditional private/nonshared access. The legitimate-access promise is owner-supplied, not independent licence/authenticity verification.
- Verification: Requirement matrix outputs/V1.9-REQUIREMENTS.json created. Read original project/source metadata; current live and local checks follow separately. Canonical log synchronization remains staged.

## INSTAPASS-20261004-06: Pricing, catalogue and logo verification

- 2026-10-05 verified update: v1.8.0 was deployed successfully and 20 products imported live. All 20 regular and sale prices were compared with the prepared CSV. The original pending status below describes this entry's creation time, not the latest state.

- Date / project: 2026-10-04; Instapass v1.8.0 pricing and 20-product import.
- Category / status: Local implementation and tooling corrections; live deployment and import remain pending at this entry's creation. No production pricing changed yet.
- Evidence / impact: Initial theme patch contained multiple updates for the same path and was rejected atomically; consolidated before retry. A stale preview process ID was unavailable; confirmed the port was absent and restarted the persisted preview using its documented reuse mode. Browser save/navigation timed out after an actual save; fresh state confirmed the saved value. Reading input HTML value returned its initial value, while the live DOM property showed the new calculation.
- Cause / prevention: Use one update per path, inspect current process/port state, verify UI state after timeouts before repeating mutations, and inspect live input properties when testing calculations. Bounded output reads recurred; avoid printing entire reports merely to suppress them with a tiny output budget.
- Environment: npm shim pointed to a missing CLI. Explicit installed Node/npm succeeded; network EACCES required approved retries. The new preview reports PHP 8.5.10, rather than the old preview's 8.3.33. Three npm moderate warnings affect local QA dependencies; runtime impact has not been established and dependencies are excluded from the theme.
- Logo correction: Current icon library lacked OpenAI, CapCut and Midjourney. A provisional X icon was identified as wrong for Grok and replaced with Grok's own published favicon before packaging. Legacy OpenAI vector plus vendor CapCut/Midjourney favicons completed the set. A guessed legacy dist path failed; using the package's public export recovered. Midjourney page had no icon link; its structured logo metadata supplied the actual URL. The official OpenAI browser page later failed to load; do not claim its downloadable brand bundle was obtained.
- Import prevention: WooCommerce stores virtual/downloadable flags within Type; source's separate columns would otherwise import as physical products. Prepared Type combines simple and virtual. Original CSV is preserved. Prices ending .99 cannot maintain mathematically exact 25 percent with fixed regular prices; nearest .99 is stated explicitly and storefront badges derive actual prices.
- Native importer check: Initial update_existing=true skipped all new SKUs and the fixture correctly failed Missing SKU. WooCommerce's update mode updates existing products only; switched to normal import for the empty catalogue. Corrected native importer created and verified all 20 SKUs, prices, virtual flags and palette metadata. No duplicate fixture import was performed after success. Drafted only old local sample products, preserving their records.
- Verification: Local runtime pricing validation, stale-state guard, scheduled-sale badge and variable-child checks passed. Actual local native importer verified 20 products. Storefront, responsive and live checks are tracked separately in versioned verification reports. Global log synchronization remains staged outside writable roots.

## INSTAPASS-20261005-07: Comprehensive catalogue and tool recovery

- Date / project: 2026-10-05; Instapass v1.8.1, 65 new offerings.
- Category / status: Local import-check omission corrected; native UI and calculations verified locally. Live deployment separately verified by WordPress success; final live import checks tracked in versioned report.
- Evidence / impact: The importer defaults to a time limit and stopped before all 65 rows. Missing-SKU checks caught incomplete batches. Repeated imports skipped existing SKUs and continued; local fixture then disabled timeout checks and verified all 65, preserving existing prices and status. Production uses WooCommerce's normal batched UI importer. Never treat one importer invocation as proof of all rows.
- Interpretation: User explicitly chose public unpriced listings, superseding the earlier draft proposal. All 65 CSV rows now publish with blank prices and a 25 percent pricing default; storefront says Price on request. No reference prices or missing durations invented. Four numbered rows have no product names and were excluded; worksheet formatting extends beyond the actual list.
- Browser/tool corrections: Broad mapping snapshots were too verbose; use selected-option summaries. CDP navigation/state sometimes timed out despite completion; read fresh AX/DOM before repeating mutations. Browser viewport override affected the selected admin tab rather than the intended QA tab; scoped CDP testing was used and overrides reset. A guessed pricing.js subdirectory read failed; rg located assets/pricing.js. The mobile admin hides All Products menu links; use the already-observed product URL rather than repeated locator clicks.
- Process recovery: CLI wrapper and child both appeared in metadata. Stop-Process returned NullReference errors, so printed success was not proof. Targeted .NET termination ended the wrapper and child; replacement server subsequently bound the same port successfully. No unrelated processes targeted.
- Logo sources: Original fetch mistakenly used freelancestack.io; official search identified freelance-stack.io. Restricted network fetches were rerun with approved escalation. 60 of 62 brands have genuine packaged icons; Gamma HTTP403 and Brain.fm HTTP500 remain source limitations, with names retained instead of fabricated logos. ARTHIZE confirmed by owner and official site icon obtained.
- Verification: 27 PHP files parsed; JS syntax passed; all 65 local listings published and unpriced; existing prices/status preserved. Backend 20 regular +25 percent gives14.99, entered13.99 gives30.05 percent; save, draft toggle, unpriced re-enable and restore passed. Local All tools shows85 and VPS & Cloud shows3. Latest multi-file patch failure was atomic and corrected with exact context. Global log merge remains pending outside writable roots.
- Live completion: WordPress confirmed v1.8.1 update, native import65 new products and metadata update20. Both backend pages collected85 unique SKUs; all65 published/unpriced/default25, all20 prior regular/sale values unchanged. Public All tools count85; native cloud archive3; unpriced detail has Request a price link and no cart button. First16 live logos loaded and16briefs present. Desktop and mobile screenshots inspected;390 viewport has375client/scroll width. Backend screenshot saved, viewport overrides cleared and temporary tabs closed. Browser uploads occasionally took many minutes despite requested tool timeouts; no duplicate upload submitted. Numeric pagination role name did not match; inspected actual link URL and navigated to page2. Evidence: outputs/THEME-v1.8.1-VERIFICATION.json and v1.8.1-live-price-rows.json.

## INSTAPASS-20261004-05: Assignment purpose versus public access

- Date / project: 2026-10-04; Instapass university assignment scope clarification.
- Category / status: Confirmed interpretation error in proposed workflow; corrected before any live setting changes. Global log synchronization remains pending because the canonical file is outside writable roots.
- Evidence / impact: Assistant proposed a private preview or discouraging indexing after the owner described an educational sandbox. Owner clarified regular live appearance, access for everyone and indexing allowed. No visibility or indexing setting was changed.
- Cause: Educational purpose and transaction-testing scope were conflated with audience/access restrictions.
- Correction / prevention: Record purpose, audience, search visibility and transaction mode separately. Follow the explicit public/indexable clarification; do not introduce an access gate or unsolicited demo banner.
- Verification: WooCommerce Site visibility has Live checked. WordPress Reading Settings has Discourage search engines from indexing this site unchecked. Project setup-progress.json records this policy and keeps payment configuration unverified.

## INSTAPASS-20261004-04: Assisted deployment workflow and live editor observations

- Date / project: 2026-10-04; Instapass contact deployment and future iteration workflow.
- Category / status: Confirmed workflow misunderstanding corrected and assisted deployment demonstrated. Separate browser environment and unresolved editor observations are recorded below.
- Evidence / impact: The contact update was handed to the owner as another manual download/upload task. The owner explicitly rejected repeating this for hundreds of planned changes and asked the assistant to apply updates.
- Cause: Manual ZIP handoff was carried forward from installation guidance without offering an authenticated deployment workflow.
- Correction / prevention: Treat the owner's request as authorisation to apply the requested, prepared Instapass updates through their signed-in WordPress session. Build and check changes locally, preserve rollback files, upload/apply and verify the live result. Request one-time sign-in only when access is missing. Do not make the owner upload every iteration.
- Verification: Owner signed in; WordPress uploaded the exact v1.7.1 file and compared it to installed v1.7.0, then reported Theme updated successfully after assistant replacement. The admin now confirms active Instapass 1.7.1. Live Contact has two exact mailto links; homepage footer has the same email; WhatsApp text/links are absent and existing Telegram retained. Live screenshot inspected. Both package hashes match their reports. No credentials were printed or stored in project files. Transactional email, payments and licence delivery remain separate unverified setup stages.
- Access environment: The initial read of `.ssh` metadata was denied by the sandbox. Approved metadata-only retry succeeded and showed only known-hosts files. No matching project key was found in the bounded top-level project/Downloads checks. Browser sign-in provided the needed access instead.
- Browser recovery: A navigation call failed when the tool kernel exited during Windows sandbox ACL setup. After a reset, the old tab identifier was stale; the documented inventory located the same browser's signed-in tab under its new identifier. A Site Editor click then timed out in CDP; fresh accessibility state showed navigation had occurred and the editor later finished loading. Never claim the action failed solely from its timeout or reselect a different browser to recover a stale tab.
- Live editor finding: Opening the existing Front Page template produced repeated WooCommerce product-price block-validation errors: the save function expected `<div class="is-loading"></div>` while stored markup was empty. Self-closing product-price blocks are present in the local source. Editor behaviour requires a separate repair/check; this does not establish a frontend price failure or the cause of the export issue. No template was saved or reset during this inspection.
- Backup limitation: Clicking the editor's Export action timed out waiting for a download after 20 seconds. The action is a button without a direct download href; a bounded Downloads inspection found no fresh export. Do not claim a fresh live-theme export or database backup. The verified v1.7.0 ZIP remains available as a theme rollback package.
- Upload verification error: The documented file chooser selected the ZIP and Install Now became enabled, but an extra DOM check tried iterating e.files and raised TypeError. The selection call lasted about 356 seconds despite the requested 30-second tool timeout; the latency cause is unconfirmed. Use visible uploader state and WordPress's uploaded-filename/version comparison to verify selection, rather than assuming the read-only DOM wrapper exposes a FileList. Subsequent upload, exact comparison, replacement and live checks succeeded; the DOM inspection error did not mean the upload failed.

## INSTAPASS-20261004-03: Reusing a persisted local WordPress preview

- Date / project: 2026-10-04; Instapass v1.7.1 contact update.
- Category / status: Confirmed preview launch mistake; recovered and locally verified. No production change or theme defect established by this failure.
- Evidence / impact: Relaunching Playground with its default download-and-install mode against the already populated `.qa/local-site` emitted thousands of cannot-unzip warnings because the WordPress files already existed. The launch was stopped.
- Cause: The first launch command was reused without changing its installation mode for the now-persisted site.
- Correction / prevention: Consult the installed CLI's README and use install-from-existing-files-if-needed when reusing this preview. Never delete the persisted preview to hide a setup-mode error. Keep verification output for each theme version in separate directories.
- Verification: Corrected launch serves v1.7.1. Browser shows both email links with the exact mailto address, no WhatsApp text/links, and no horizontal overflow at 1280px and 390px viewports. Both screenshots inspected; no browser console errors/warnings returned. The ZIP passes CRC, matches the checked source, and contains only five changed theme files. The v1.7.0 ZIP SHA-256 remains unchanged. This is local verification; the live upload and transactional email remain unverified.
- Output recurrence: The warning stream exceeded the returned output budget. A follow-up combined README/source/log read also truncated its nonessential old-log tail; the needed CLI mode documentation and implementation lines were intact. Keep subsequent diagnostics short and print only the relevant tail of noisy process output. Global update remains staged outside the permitted global write root.

## Recurrence for GLOBAL-20260905-03: Oversized combined tool output

- Date / project: 2026-10-04; Instapass handover intake and theme upload guidance, Website creation.
- Category / status: Confirmed inspection workflow error; relevant evidence recovered. Global log update pending.
- Evidence / impact: The first combined tool response reported truncated output after including handover text, a lessons-log excerpt, a memory search, and a broad tool metadata listing. The memory search result was obscured. No site or theme changes occurred.
- Cause: A broad tool listing was added to already batched file reads without a sufficiently bounded output budget.
- Correction / prevention: Reran targeted project and tool lesson searches with small per-call limits. Keep tool discovery separate from source reads and output only the few matching names needed for the task. Follow up on truncation before using the evidence.
- Verification / recurrence: Targeted reruns were untruncated. The project memory search had no matches; the current workspace contained no files. Add 2026-10-04 to the existing GLOBAL-20260905-03 recurrence history when global write access is available.
- Same-day recurrence: Two responses during upload guidance also truncated because full web results exceeded the output budgets. The essential official upload instructions and search evidence were visible. Subsequent retrieval used stored results with selected excerpts. Bound the printed result, rather than relying only on a requested short web response.
- Final-tile recurrence: A 28-line pending-log read exceeded its 1,300-token output budget by 55 tokens. Reread the relevant lines with 1,100 tokens; no required evidence remains obscured. Bound dense entries by sections, not line count alone.

## INSTAPASS-20261004-01: Official source fetch rejected

- Date / project: 2026-10-04; Instapass theme upload guidance.
- Category / status: Environmental retrieval limitation; direct page fetch unavailable. Search evidence available.
- Evidence / impact: Opening the official WordPress Trac changeset 59513 returned HTTP 403. The earlier official search result described its change from Active to Installed wording in theme replacement checks. No live site access or installation occurred.
- Cause: Remote page rejected the retrieval request; exact reason unknown.
- Correction / prevention: Use the already returned primary-source search evidence for the wording distinction. Do not present the rejected page as successfully fetched or treat the response as a defect in the user's site.
- Verification: Official WordPress upload instructions were returned separately. This retrieval limitation does not block explaining the documented upload workflow.
- Final-tile recurrence: Web click using the page URL as ref_id was rejected; retry with the returned page ID resolved the link but the renderer rejected the official partnership ZIP content type. This did not retrieve an official logo asset. The delivered tile uses a generated logo rendition; do not claim exact official asset fidelity, and use an official asset when implementing the coded tile.

## INSTAPASS-20261004-02: Design review findings and environment limits

- Date / project: 2026-10-04; Instapass v1.6.1 review and visual design options.
- Category / status: Confirmed copy inconsistencies in the original theme; corrected in concepts only. Original theme and live installation not changed.
- Evidence / impact: patterns/hero.php promises unlocked in seconds alongside activation within 15 minutes. Hero and product template advertise card methods without verified configuration. spotlight.php labels unrestricted popularity as this month's picks and unrestricted date sorting as new this week. These can create inaccurate expectations.
- Cause: Display copy does not match timing/query semantics, and payment capability is hard coded instead of confirmed.
- Correction / prevention: Concepts use the confirmed-payment 15-minute promise, neutral catalogue headings and no unsupported payment network labels. In the eventual theme, align operational claims with configuration and label query periods accurately.
- Verification: Source strings and query attributes inspected directly. All three concept layouts reviewed in the browser; search filtering worked, tested viewports had no horizontal document overflow and tab logs showed no console errors/warnings. No WooCommerce checkout, email delivery or payment tested.
- Environmental limits: Initial npm registry access failed EACCES; approved retry downloaded the official local Playground runtime. Its optional fs-ext-extra-prebuilt install script was blocked; CLI help now executes but WordPress operation is not yet verified. Direct Pinterest viewing reached sign-in; public indexed references were used without copying artwork. One browser navigation stalled before its later screenshot and DOM state confirmed a rendered preview. Available ImageGen has no model selector; exact backend version cannot be confirmed. After the user repeated the explicit built-in image request, generated and inspected original transparent artwork successfully. Current official OpenAI documentation lists Image 2.5; lack of tool metadata should be disclosed without blocking authorized use of the built-in tool.
- Inspection recurrence: Additional oversized skill/source/web responses were truncated; required skill ranges and decision-relevant theme sections were reread in bounded calls. Avoid serializing multiple large reads and metadata together; retain large web results and print short excerpts. Existing GLOBAL-20260905-03 prevention applies.
- Renderer invocation error: Rerendering an existing QA wrapper without --force was rejected. Reissued with the documented --force flag against the same task-owned preview file. Preserve the editable fragment and use --force only for an intended replacement of its QA wrapper.
- Additional same-day workflow recurrence: A combined skill/source read again exceeded the aggregate result budget. Missing skill ranges were reread separately before authoring. Keep total expected output below the exec result budget.
- Font preparation: Sandbox downloads of the official Outfit font were rejected; approved retry succeeded. An optional WOFF2 conversion failed because bundled Python has no fontTools. Retain and self-host the licensed TTF instead of assuming optional modules exist. Generated artwork WebP conversion succeeded, preserving dimensions and alpha (231,876 bytes versus 2,117,185-byte original).
- Patch authoring: A patch attempted to delete and add the same file in one request and was rejected atomically. Replaced those task-owned copies using sequential delete and add patches; the original archive was unaffected. Use a single update per path or separate replacement calls.
- Local WordPress setup: The first runtime fetch failed, including an approved attempt; a diagnostic retry downloaded WordPress 7.1.2 and WooCommerce. QA setup then called wc_create_pages(), which was not available in that request. This was a preview harness error, not evidence of a theme failure. Switched to WC_Install::create_pages() and set the preview administrator explicitly before seeding. Runtime reported a WooCommerce early-translation notice and filesystem unlock warnings; subsequent runtime verification remains required. A diagnostic search also assumed a nonexistent CLI dist directory; recovered by enumerating the actual package files.
- Local verification update: WordPress 7.1.2 / WooCommerce 11.1.2 / PHP 8.3.33 now starts and activates v1.7.0. All five starter pages and four required catalogue/cart/custom blocks are present. An attempted read of the prior temporary plugin folder failed because Playground had cleaned it up; a workspace-mounted QA site preserves diagnostic files. The default WooCommerce coming-soon setting hid the cart from guests, so it was disabled in the local QA plugin only. No production settings were changed. The preview annotation initially failed because its MU-plugin directory did not exist; creating it made the demo banner visible. A new header used an unrecognised priceVisibility attribute; corrected to hasHiddenPrice after reading the installed block schema. A cart screenshot timed out; recovered using the documented browser API. Cart contents verified one demo licence at $12.00 and product search navigated to the matching demo product. Remaining responsive and empty-catalogue checks are still pending.
- Responsive defect / correction: At the 390px viewport the catalogue extended two pixels beyond the document because card padding was added to its old width rule. Explicit grid layout, auto card width and border-box sizing fixed it. At 320, 736 and 1024px viewports, scrollWidth now equals clientWidth; the shop also fits at 390px. Native mobile navigation opened/closed and FAQ disclosed its answer. The initial nested core/query-no-results block did not display the empty-catalogue message in WooCommerce; moved the message outside the collection and hide it when real product cards exist. Browser verified empty message visible with zero results, hidden with four results. A JSON QA endpoint was blocked by the browser client after producing its report; the task-owned report confirms 24 PHP files parse and no unregistered block names. These observations are local preview evidence, not live checkout or email-delivery proof.
- Handoff verification: The final v1.7.0 ZIP has 59 entries under instapass/, passes CRC, includes local font/art/styles and excludes QA scripts/plugins/database/demo catalogue. Original extracted files still match every original ZIP entry. Browser navigation back to the homepage timed out once; a subsequent snapshot confirmed the homepage and no console errors/warnings. Final responsive/card styles were checked on both the homepage and Shop. Output truncation recurred when a short prompt read unexpectedly included a long single-line prompt; no required evidence was lost and the full prompt remains saved. Global update remains staged because the canonical file is outside permitted write roots.

### INSTAPASS-20261005-09 - Preview fixture recovery and scoped checks
- Date / project: 2026-10-05, Instapass v1.9.0.
- Category / status: Local QA workflow mistakes; recovery in progress.
- Evidence / impact: apply_patch rejected a delete/add pair targeting the same CSS file (no file changed); two guessed fixture paths were absent; rg Windows path glob was invalid. Initial preview navigation timed out and redirected to login; inherited local test password differed from the blueprint login step. Counting every QA product also included historical test-only products, failing the 85-product assertion.
- Cause: Assumptions about tool patch semantics and the copied fixture state.
- Correction / prevention: Write the new version's CSS directly; inspect actual fixture filenames; use glob options on a real directory. Use the existing Playground test login. Verify exactly the 85 known baseline SKUs and their prices, excluding historical QA fixtures.
- Verification: Browser test login succeeded with the default local fixture credential; runtime acceptance is still pending. No production credentials, catalogue, or prices were changed by these checks.
- Recurrence / follow-up: Failed local price comparison came from earlier QA price edits in the copied site; the isolated fixture was aligned once to the recorded live baseline. The source-feature assertion then caught an actual Adobe copy defect: blank activation text inherited private own-email wording. Fixed by preserving blanks from the Excel and applying private defaults only to legacy offerings absent from the source. Runtime checks subsequently passed (588 before final coupon revalidation check). PHP command-line SQL quoting failed without any database change; use saved scripts for multiline SQL. Browser DOM reads occasionally raced navigation and timed out; the native accessibility state recovered the successful save. A combined disable/stock test first returned draft/out-of-stock; restoring via the inspected form confirmed enabled/in-stock with prices still blank. No claim of atomic combined-stock behavior based on that first result.
- Verified recovery: The inconsistent combined edit was a browser-emulation interaction issue. After clearing the screenshot-only CDP device override and reloading, both controls changed, the row auto-selected and unsaved status appeared correctly. Future viewport emulation is limited to read-only inspection/screenshots; reset it before form actions. The restored stock/status save is captured in the local diagnostic report.

### INSTAPASS-20261005-10 - Upload tab crash before installation
- Date / project: 2026-10-05; Instapass v1.9.0 deployment.
- Category / status: Browser environment limitation, recovery in progress.
- Evidence / impact: Native file chooser accepted the 2.44 MB package, then the original in-app upload tab crashed before the Install Now step. DOM inspection and navigation were blocked because the browser crash page used a data URL. No WordPress installation or replacement was confirmed.
- Cause: Browser renderer crash; exact cause unverified.
- Correction / prevention: Read documented browser troubleshooting, close the completed brand-reference tab and create a fresh tab in the same browser/session. Use the normal WordPress uploader again; verify installed/uploaded versions before replacement and success afterward. Do not treat chooser success as deployment proof or bypass a crash/error page policy.
- Verification: Fresh WordPress upload page opened signed in; installation still pending.

### INSTAPASS-20261005-11 - Windows path escaping in browser upload
- Date / project: 2026-10-05; Instapass v1.9.0 deployment.
- Category / status: Upload workflow error; resolved and verified.
- Evidence / impact: The first retry passed a Windows path with backslashes inside a JavaScript string. JavaScript consumed escape sequences (including `\v`), producing an invalid relative path and delaying the upload. The package was not changed.
- Cause: A filesystem path was embedded without forward-slash normalization for the browser automation runtime.
- Correction / prevention: Use an absolute forward-slash path in CUA file-chooser calls and verify the Install Now control becomes enabled before submitting.
- Verification: `instapass-theme-v1-9-0.zip` (628,759 bytes) was accepted, WordPress reported uploaded v1.9.0 versus installed v1.8.1, and the replacement page reported “Theme updated successfully.”

### INSTAPASS-20261005-12 - Live verification scope after replacement
- Date / project: 2026-10-05; Instapass v1.9.0 deployment.
- Category / status: Verification record; resolved for the requested sandbox/live demo scope.
- Evidence / impact: Live `/shop/`, `/faq/`, `/privacy-policy/`, guest `/checkout/`, Pricing & Discounts, and Promotions pages were inspected after replacement. Checkout correctly reports transfer/Binance methods unavailable until receiving instructions are configured.
- Cause: Deployment needed fresh post-replacement evidence rather than relying on local preview evidence.
- Correction / prevention: Recheck public catalogue count, logo cards, category filters, private/renewal copy, responsive width, guest checkout fields, admin pricing/stock separation, and promotion controls after every theme replacement.
- Verification: Live shop shows 85 products with real logo tiles and 1–16 pagination; mobile 390px verification reports `scrollWidth === clientWidth` (375); checkout shows activation email, optional phone, renewal confirmation, no account/saved card, and the honest unconfigured-payment message.

### INSTAPASS-20261007-13 - PHP CLI unavailable; WASM syntax checks passed
- Date / project: 2026-10-07; Instapass owner price-command automation.
- Category / status: Environment limitation; source syntax was verified using PHP-WASM. Real WordPress execution is still unverified.
- Evidence / impact: `Get-Command php` found no PHP CLI, so `php -l` could not run on the new read-only WordPress preview endpoint. The first inline PHP-WASM lint invocation also failed in Node parsing because PowerShell altered its quote escaping; it failed before loading the PHP source.
- Cause: PHP CLI is unavailable, and embedding nested JavaScript/PHP string delimiters in a PowerShell command was brittle.
- Correction / prevention: Use the saved `.qa/lint-instapass-automation.mjs` with the already available PHP-WASM runtime and run focused offline endpoint checks before staging integration. Do not call the endpoint live-verified or deploy it based on source/mocks alone.
- Verification: PHP-WASM syntax checks passed for the current plugin. Mocked WordPress/WooCommerce route and workflow checks are recorded in INSTAPASS-20261007-15. Real WordPress integration remains unverified; no live endpoint or product data was touched.

### INSTAPASS-20261007-14 - GitHub initialization and public-repository setup
- Date / project: 2026-10-07; Instapass Git/GitHub setup.
- Category / status: Setup friction; recovered. Curated source published after the user clarified that other LLMs should use the public repository.
- Evidence / impact: Git initialization ran under the Codex sandbox SID; an elevated `git remote add` was then blocked by Git's dubious-ownership guard. A subsequent local Git config write and a 2026-10-07 ordinary `git add` attempt were denied by workspace permissions; explicit escalation recovered both required repository writes. A broad `git status` also emitted an unexplained missing-directory warning (`' /'`), which recurred during post-push status verification on 2026-10-07; the command still reported `main...origin/main` clean. In the GitHub form, an accessible-label locator matched both the name field and suggested-name button; no form value was changed by that failed interaction. The first credential-pattern scan treated a leading hyphen pattern as a flag and failed before scanning.
- Cause: Mixed sandbox/Windows account ownership, read-only `.git` policy, ambiguous form labels and command-line option parsing.
- Correction / prevention: Apply `safe.directory` only per Git command, never globally; use GitHub role/name locators based on a fresh DOM snapshot; pass regexes with `rg -e`; stage a curated source set and exclude private handoff/payment records and local setup state. The missing-directory warning remains unexplained; Git status and push completed despite it.
- Verification: `ssdbank9/Instapass` is public, `main` includes `5941432` (initial curated source), `cef9e04` (preview UI), `9f685c6` (confirmed pricing workflow), and `7874b94` (default-off CommandCode adapter); the local `origin` points to its HTTPS URL and the latest push returned success. Post-push Git status reported `main...origin/main` with no tracked changes. Corrected credential-pattern scans found no API/private-key token matches, wallet address or demo-server IP in staged files. Internal handoff/decision/payment-verification logs, local setup state, old themes and product spreadsheet were excluded.

### INSTAPASS-20261007-16 - Playground integration environment not yet suitable for WooCommerce workflow proof
- Date / project: 2026-10-07; Instapass owner pricing assistant verification.
- Category / status: Local verification setup recovered; native MySQL locking remains an environment limitation; no live changes.
- Evidence / impact: The installed CLI's first direct help invocation returned no output; invoking it with no subcommand printed usage and exited 1; the documented `server --help` then worked. One launch included unsupported `--skip-browser` and was rejected before launch. A short-lived local health check timed out while Playground booted; the mounted existing WordPress/WooCommerce fixture then returned HTTP 200 on `wp-login.php` and a ready message on localhost:9401. `--login` eventually became ready after a longer wait, but a `/wp-admin/` check hit a redirect limit and a later check timed out; this does not prove login mode works. Inspecting only package assets initially missed `.qa/local-site`, the actual fixture; that incorrect assumption was corrected after listing workspace files. Source inspection confirms the assistant uses MySQL `GET_LOCK`/`RELEASE_LOCK`, while the fixture uses SQLite, so it cannot verify the lock behavior without adaptation or another database runtime. One command-level REST probe failed before process creation due to sandbox setup refresh; one narrow retry failed the same way. The session stop tool then returned `Unknown process id`, so whether the final local server process exited is unverified. The first printed config filter produced malformed labels, but database credentials were redacted and no secret value was emitted.
- Cause: Help/launch option syntax was not initially checked against installed CLI help; startup takes longer than a short health timeout; fixture search was too narrow; SQLite is not the target MySQL-compatible lock engine; the unified execution helper failed during sandbox setup.
- Correction / prevention: Read installed CLI help before invocation; allow sufficient boot time and verify readiness before HTTP checks; inspect existing local fixtures before concluding none exist; avoid printing configuration values; test SQLite compatibility explicitly and use a disposable MySQL-compatible runtime for lock verification. After repeated helper setup failures, stop direct retries and report verification/cleanup as unconfirmed. Do not start downloads or write outside authorized roots without an approved route.
- Verification: Existing local fixture is mounted, WooCommerce files are present, and WordPress served HTTP 200 on localhost:9401. A secret-safe config scan confirms placeholder DB settings; `GET_LOCK` appears only in a WooCommerce comment within its local plugin files. The local pricing plugin was not activated, no product was modified, and no live WordPress state was touched. Global log synchronization remains pending outside writable roots.

### INSTAPASS-20261007-17 - Portable pricing lock and malformed-history guard
- Date / project: 2026-10-07; Instapass owner pricing assistant.
- Category / status: Confirmed source defect corrected; focused checks pass. Real WordPress database integration remains unverified.
- Evidence / impact: The earlier `GET_LOCK`/`RELEASE_LOCK` and `UTC_TIMESTAMP()` calls prevented meaningful verification against the available SQLite Playground. Replaced them with a WordPress options-table lease using a unique option name, compare-and-swap reclaim after 300 seconds, and release matching the current token. The first full regression run then exposed a separate source defect: JSON-decodable but incomplete stored payloads bypassed the `is_array` guard, emitted missing-key warnings, and caused history validation to fail. Added `valid_payload()` checking before rendering history and kept malformed rows visible as manual review. The follow-up first failed because the test expected `corrupt_record` while the operation correctly persisted `manual_review`; the assertion now accepts either safe fallback state. A broad normal `git status` failed Git's dubious-ownership check; the repository's previously observed sandbox/Windows ownership mismatch recurred. Normal `exec_command` also repeatedly failed before process startup with sandbox-helper setup-refresh errors.
- Cause: Locking/expiry relied on MySQL-only SQL; history checked JSON shape but not required fields; test assertion did not reflect the stored manual-review state; command sandbox setup remained intermittent and Git needed the established per-command safe-directory override.
- Correction / prevention: Keep locks on WordPress database methods and guard rendered payloads with the same structural validator used by the writer. Cover active-lock rejection, release, expired-lock reclaim, malformed history, and stale expiry in regression tests. Do not change global Git configuration; use a per-command `safe.directory`. Use the approved local test runner outside the sandbox only when its helper prevents ordinary startup.
- Verification: PHP-WASM syntax, full plugin workflow harness, preview/admin harness, JS syntax, 16 deterministic pricing tests, and the offline n8n demo check all pass. `git diff --check` passes with only Git's expected CRLF normalization warnings. Commit `b3b5446` was pushed to public `main`; final Git status is clean (`main...origin/main`). Git still emits the unexplained `could not open directory ' /'` warning. Port 9401 had no listener at the post-test check. No plugin activation, product write, live-site change, or external/paid service call occurred. Global log synchronization remains pending outside writable roots.

### INSTAPASS-20261007-18 - Local WordPress integration and Playground rerun instability
- Date / project: 2026-10-07; Instapass owner pricing assistant local integration verification.
- Category / status: One full local WordPress/WooCommerce workflow run completed in a disposable fixture; later Playground reruns failed without PHP diagnostics. Live-site behavior and MySQL-specific locking remain unverified.
- Evidence / impact: The initial fixture copy took over a minute and was interrupted; a resumed copy had a higher file count than the source because an earlier retry created a nested duplicate. The mounted root nevertheless contained WordPress, WooCommerce, the current Instapass theme and plugin. Playground rejected a Windows drive-letter mount until the documented two-argument `--mount-dir` syntax was used, rejected combining `--mode` with `--wordpress-install-mode`, and `run-blueprint` rejected the installed v1 blueprint format under its v2 handler. Switching to the installed CLI's `server` command with the isolated copy completed a full check without a PHP failure: plugin activation/schema, REST route registration/capability gate, preview persistence, 25% WooCommerce sale price (`20.00` regular / `14.99` sale), database audit history, and undo restoration. The first integration assertion incorrectly treated `WP_REST_Response` values as arrays; normalizing `get_data()` fixed it. Two later reruns returned PHP exit 255 with empty stdout/stderr; a temporary file-write diagnostic attempt also failed without a result file. All temporary ports 9402-9404 were confirmed stopped. No live site or payment/email service was touched.
- Cause: Full fixture copying was unnecessarily broad; CLI mount syntax and Blueprint generations differ; real WordPress callbacks return response objects; repeated Playground PHP runtime behavior is not yet explained.
- Correction / prevention: Use `--mount-dir <Windows path> <VFS path>` for Windows paths; read installed CLI help before mixing mode flags; match Blueprint format to the selected CLI handler; normalize real REST responses in integration checks; use the disposable WordPress root and verify cleanup. Treat one clean bootstrap run as evidence of that run only; keep repeatability and MySQL locking explicit until independently reproduced. Keep local test harness files and fixture copies ignored and out of public commits.
- Verification: The successful local run reached Playground's ready state after its fatal assertions and covered the checks listed above. The complete local verifier subsequently passed: PHP-WASM syntax, WordPress workflow harness, admin preview harness, admin JS syntax, 16 price-command checks, and offline n8n checks. `git diff --check` passed; tracked Git status is clean at `b3b5446` (`main...origin/main`). No temporary listener remained. Canonical global log remains outside writable roots; this entry is staged here pending synchronization.

### INSTAPASS-20261007-19 - CommandCode billing inspection blocked by UI helper
- Date / project: 2026-10-07; Instapass CommandCode zero-spend preflight.
- Category / status: Environment limitation; account billing state remains unverified. No settings or credentials changed.
- Evidence / impact: Aly confirmed being signed in and authorized read-only inspection of plan, credit pools and auto-reload settings. The first browser computer-use state read exited with `windows sandbox failed: helper_unknown_error: setup refresh had errors`. After the required kernel reset, the retry failed identically. No billing-page text was retrieved, no control was changed and no API request was sent.
- Cause: Windows computer-use helper could not initialize because of a sandbox setup-refresh error.
- Correction / prevention: After the reset/retry also fails, stop UI automation and report the helper limitation; do not switch to unauthenticated web lookup as a substitute for account-specific evidence. Keep model calls disabled until account spend controls are verified.
- Verification: Both helper failures were observed directly; no API keys or payment details were requested or exposed. Canonical global log remains outside writable roots; this entry is staged here pending synchronization.

### INSTAPASS-20261007-20 - Requested Chrome extension has no callable connector in this session
- Date / project: 2026-10-07; Instapass CommandCode zero-spend preflight.
- Category / status: Tooling limitation; account-specific billing inspection remains blocked.
- Evidence / impact: Aly asked to use the ChatGPT Chrome extension. The available tool inventory contains no Chrome-extension/browser-session connector; `open_in_codex` can display a browser tab but cannot inspect it. The only browser-control interface exposed here is CUA, which had already failed its initial state read and reset/retry with the same Windows sandbox setup error.
- Cause: The claimed extension is not wired as a callable tool in this session, and the available UI helper is failing initialization.
- Correction / prevention: Do not claim extension access or substitute unauthenticated web access for the signed-in account. Continue only when a browser-control connector is available or Aly supplies the requested non-secret billing values. Keep model/API usage disabled pending the zero-spend check.
- Verification: `ALL_TOOLS` search for browser, Chrome and extension returned no browser-session connector. No page access, settings change or API request occurred. Canonical global log remains outside writable roots; this entry is staged here pending synchronization.

### INSTAPASS-20261007-21 - Owner-reported billing check and current CommandCode public limits
- Date / project: 2026-10-07; Instapass CommandCode adapter preparation.
- Category / status: Adapter source is committed and pushed, remains default-off; billing settings are owner-reported as good but not independently verified. No real API request or spend.
- Evidence / impact: Aly said they checked billing and it was good. Official CommandCode docs currently list Pro at $20/month and Max 10× at $100/month; they state included windows may be bypassed when on-demand credits are available and auto-reload can top up automatically. Account-specific plan, remaining included/extra balances and reload state remain unknown because the signed-in UI could not be accessed. Added a documented optional adapter with an explicit disabled default, fixed endpoint, three documented model IDs, one request only, bounded timeout/output, strict output validation and deterministic local preview revalidation. Mocked tests pass; actual n8n/WordPress connection, provider entitlement and credit enforcement are not verified.
- Cause: Public plan naming/pricing can differ from the owner's shorthand, and the UI helper failure prevented collecting account-specific confirmation.
- Correction / prevention: Preserve Aly's report as owner-provided evidence, distinguish it from direct account verification, and keep the provider disconnected/default-off until the $100 tier and extra-credit protections are confirmed. No API key was supplied and no API call was made.
- Verification: Official provider, model registry and pricing/usage-limit docs reviewed on 2026-10-07; full local test runner passed including 10 mocked adapter checks, with zero real provider calls. Commit `7874b94` is pushed to `origin/main`; post-push status was clean. The adapter is not connected to n8n or WordPress and remains disabled.


### INSTAPASS-20261018-01 - OCI source terminated; DNS and HTTPS cutover verified
- Date / project: 2026-10-18; Instapass OCI migration to rdi-tracker.
- Category / status: Source migration, termination, persisted DNS change and public HTTPS cutover verified. This does not establish payment-provider or purchase-flow acceptance.
- Evidence / impact: The source site, database, stack configuration and both Caddy volumes were archived and SHA-256 verified on source, local staging and target. Instapass was restored at rdi-tracker beside the preserved Wasooli service. OCI confirmed source instance instance-20260920-2058 RUNNING, then STOPPED after SOFTSTOP, then TERMINATED. No separate data volumes were attached; OCI's termination default deletes the boot volume. Desktop Commander operated the existing signed-in cPanel Zone Editor through the dedicated Chrome CDP connection and changed only the root A record from 199.188.200.15 to 145.241.115.28. All 27 records were compared after save and reload; only that address changed. Both authoritative nameservers, Cloudflare DNS and Google DNS then returned the new address.
- Cause: The saved OCI CLI session had expired and required owner sign-in. The initial conclusion that Namecheap required a manual user change was incomplete: the dedicated Chrome CDP session was accessible through Desktop Commander even though Windows Computer Use node_repl was unavailable.
- Correction / prevention: Reauthenticate through OCI's browser flow and use explicit security-token auth. The first terminate waiter used unsupported TERMINATED; validation rejected it before action. Corrected the waiter to SUCCEEDED and independently queried lifecycle TERMINATED. Before declaring UI access unavailable, inventory the explicitly requested connector's supported command/browser routes. For DNS edits, save a before snapshot, assert the exact record/type/old value, save only the intended record, reload, and compare the complete zone.
- Verification: Caddy was force-reloaded without restarting the service after DNS changed; production Let's Encrypt certificate acquisition succeeded for root and www. External origin checks from the user's PC used domain SNI with full TLS verification: root 200/title Instapass, www 301 to root, wp-admin 302 to HTTPS login, wp-json 200 with correct name/home/url, Wasooli 200/title Wasooli. Normal Chrome navigation to https://instapass.online/ also returned 200 and rendered the store; no old-origin href/src was found. Evidence is in migration-transfer-20261018/dns-before-20261018.txt, dns-reloaded-20261018.txt, public-verification-20261018.json and instapass-live-20261018.png. Canonical global log remains outside writable roots; this entry is staged here pending synchronization.


### INSTAPASS-20261018-02 - DNS browser inspection retries and remaining homepage copy mismatch
- Date / project: 2026-10-18; Instapass domain cutover through Desktop Commander.
- Category / status: Browser inspection errors recovered; Windows curl environment limitation bypassed with equivalent strict TLS verification; live homepage timing mismatch resolved in INSTAPASS-20261018-03.
- Evidence / impact: A locator anchored to the Edit button became invalid after the successful click replaced that button with an editor. Its follow-up innerText timed out, and a later stale-button inspection also timed out. Re-observing the current editor showed the original A value; filling and saving proceeded once, with successful full-zone persistence comparison. Windows curl reported SEC_E_INTERNAL_ERROR/Local Security Authority rather than a meaningful certificate result; Linux curl and Node HTTPS with rejectUnauthorized true verified valid certificates. An ordinary www request initially resolved stale DNS and failed hostname matching; direct current-origin verification and subsequent public DNS checks passed. The live screenshot also shows activation within 15 minutes, whereas the user's prior requirement was 45 minutes for delivery and activation; no content edit was part of this DNS change.
- Cause: Reusing a DOM locator rooted in a control removed by its own action; environment-specific Schannel failure; DNS cache lag; earlier homepage copy has not been reconciled to the policy requirement.
- Correction / prevention: Refresh DOM observations after an edit transition and locate the current editor controls. Treat failed TLS tooling separately from invalid certificates; test the current authoritative destination without disabling TLS validation. Reconcile the confirmed 15-minute homepage text to the owner's 45-minute requirement during the next authorized content pass.
- Verification: DNS save/reload, external strict-TLS endpoints and normal-browser homepage succeeded; screenshot and JSON evidence saved under migration-transfer-20261018. The homepage wording issue was initially left open, then fixed and verified in INSTAPASS-20261018-03. Canonical global log update is staged here because its path is outside writable roots.


### INSTAPASS-20261018-03 - Published 45-minute timing correction; PHP cache refresh recovered
- Date / project: 2026-10-18; Instapass 15-to-45-minute public copy correction.
- Category / status: Timing mismatch fixed and verified live; cache-related verification failure corrected. This change only adjusts timing references, not other refund eligibility language.
- Evidence / impact: The active theme contained 12 exact 15-minute references in 11 PHP pattern files. A scoped WP-CLI audit found no corresponding 15-minute content in saved pages, products, templates or reusable blocks. Backed up the 11 live files to /opt/instapass/recovery/time-copy-before-20261018T054431Z.tar.gz, replaced the exact timing text and linted all 11 PHP files successfully. The first browser check still showed 15 minutes because the atomic write preserved old mtime and the replacement had the same byte length. PHP OPcache timestamp validation was on, with a two-second revalidation interval; updating modified-file timestamps made the next browser check show 45 minutes. Updated 10 local source files in theme-v1.9.0 and preserved their pre-existing edits; local refund source already used 45 minutes.
- Cause: Atomic replacement copied original timestamps, hiding same-length source changes from OPcache. An initial target rg command was unavailable and its stderr was suppressed, producing an ambiguous empty result; explicit tool availability plus grep recovered the audit. Git initially rejected sandbox-owned repository metadata; a command-scoped safe.directory value enabled inspection without changing global Git settings.
- Correction / prevention: Preserve file ownership/mode but assign a fresh mtime when changing PHP content. Verify rendered public pages after file mutation; a source edit and syntax pass alone do not prove publication. Do not suppress search-tool availability failures. The retained one-off update helper now explicitly refreshes mtime.
- Verification: Normal Chrome requests returned HTTP 200 for homepage, refund-policy and terms-of-service. Each rendered page contained 45-minute wording and zero 15-minute wording; homepage/refund/terms counts were 1/2/1 respectively. Source scans found zero remaining exact 15-minute strings in local and live theme patterns. PHP lint passed for every changed live file; git diff --check passed (line-ending advisory messages only). Evidence: migration-transfer-20261018/time-copy-policies-verification.json, time-copy-browser-verification.json and instapass-45-minutes-live.png. Canonical global log synchronization remains pending because its path is outside writable roots.


### INSTAPASS-20261018-04 - Provider fee research has regional and account-specific limits
- Date / project: 2026-10-18; USD/PKR fixed-rate and direct-payment planning.
- Category / status: Public-source access limitation and account-specific facts unverified; no payment configuration changed.
- Evidence / impact: Easypaisa merchant FAQ fetch returned an internal fetch error; its official branchless-banking fee PDF returned HTTP 403. Search results did not establish a current, account-applicable merchant discount rate. Wise Ireland's current receiving pricing lists a EUR 60 account-details setup fee and USD 6.11 wire/Swift receipt fee, while the first search surfaced a US-specific USD 31 setup fee; the owner's Wise registration country/type remains unknown. PayPal Ireland's standard other-commercial category includes fees even for directly requested commercial payments.
- Cause: Restricted public document access, regional fee schedules, and unknown account eligibility/contract terms.
- Correction / prevention: Quote only primary-source rates with their product/country and exceptions; do not claim direct transfers are universally free or apply US Wise setup prices to an Irish account. Obtain the owner's Wise country/type and actual Easypaisa merchant schedule before final provider configuration. Keep PayPal gross sales, processing fees and net receipts separate in reconciliation.
- Verification: Official PayPal Ireland, Wise Ireland/current help, WooCommerce MaxMind and Binance internal-transfer documentation checked on 2026-10-18. Currency instruction is USD master prices, PKR at fixed 280/USD, Pakistan defaults to PKR with manual override. This is planning evidence, not live implementation or merchant-account acceptance. Canonical global log update remains staged outside its read-only path.


### INSTAPASS-20261018-05 - Revolut regional fee lookup recovered; business-use eligibility remains unverified
- Date / project: 2026-10-18; EUR/Revolut payment setup planning.
- Category / status: Public-document lookup limitation recovered; no account or checkout change.
- Evidence / impact: The Ireland Revolut Pro legal fee URL was inaccessible in web retrieval; a search first surfaced a UK GBP fee page, which is not an Ireland quote. Revolut's Ireland Pro product page provided EUR online-payment rates and zero additional monthly subscription. Merchant API/order callback support for Pro has not been established; Business API documentation cannot prove Pro integration. Wise's own Ireland guidance prohibits commercial use of Personal accounts, so the earlier conditional direct-Wise route does not apply to the newly identified Personal account.
- Cause: Regional legal-page routing and different eligibility/features for Personal, Pro and Business products.
- Correction / prevention: Use Ireland-specific primary rates; do not conflate payment-link acceptance with automated WooCommerce reconciliation, or personal account ownership with approved merchant eligibility. Verify the actual Revolut account type, country and acceptance of the real business before recommending activation.
- Verification: Official Revolut Ireland Pro product/terms, Ireland Business pricing, Wise Ireland guidance/pricing and PayPal Ireland fees reviewed. No USD/EUR rate has been approved or invented. Global log update remains staged because the canonical path is read-only.


### INSTAPASS-20261018-06 - Baseline backup and control-panel setup checks
- Date / project: 2026-10-18; Instapass implementation baseline.
- Category / status: Tool/script assumptions corrected and verified; customer product/order records unchanged. Authenticated admin browser acceptance remains pending.
- Evidence / impact: Backup first assumed MARIADB_ROOT_PASSWORD; the deployed container uses MYSQL_ROOT_PASSWORD. Dump failed before any store mutation. Corrected using environment variable names only; no values printed. Database/files/config archives then passed hashes. Isolated restore readiness used mariadb-admin ping, which can succeed before credential initialization completes; import failed authentication. Initial plugin file writes also failed ENOENT because parent folders were absent. Audit PHP emitted an undefined-array warning due to casting before a null fallback.
- Cause: Assumed environment naming, server-alive versus authenticated readiness, missing directory creation, PHP operator precedence. Oversized source batching also recurred and truncated output; use bounded reads.
- Correction / prevention: Inspect only environment names, use authenticated SELECT 1 readiness for isolated restore, create folders before writes, parenthesize null fallback before cast. Preserve failed backups for diagnosis; never count a dump/hash as successful restore.
- Verification: Corrected backup restored all 56 application tables in an isolated MariaDB and passed integrity checks; file/config/SQL hashes passed. Control Center PHP and money/security tests passed and plugin 0.1.0 activated. Initial remote code mount was unreadable under restrictive copied permissions; changed only non-secret code folders/files to 755/644 and reran successfully. A Git command accidentally interpolated a missing JavaScript method as an undefined path; corrected the literal path and scoped diff check passed. Private migration files appeared as untracked; added an explicit migration-directory ignore and verified backup/HTML exclusion. Instapass and Wasooli both returned 200 in a normal browser. Authenticated admin browser acceptance remains unverified. Canonical log remains outside writable roots; staged here.


### INSTAPASS-20261018-07 - GBP conversion assumption and missing owner login handoff
- Date / project: 2026-10-18; Instapass currency and owner access.
- Category / status: Requirement misunderstanding corrected; owner recovery prepared, user password setup pending.
- Evidence / impact: Assistant requested a GBP exchange rate; user clarified independent manually entered GBP pricing and discounts. Owner also had no WordPress username/password, so the earlier dashboard link alone did not provide usable access.
- Cause: Assumed a third currency would use conversion; did not establish owner sign-in during initial dashboard handoff.
- Correction / prevention: Record exact three currencies and separate GBP price/discount scope, with no GBP conversion or inferred zero prices. Verify existing administrator; prepare the native WordPress password-reset flow without sending email, creating another admin or choosing a password. Keep reset key outside logs/source control with restricted file permissions. Do not call access restored until owner saves their password and logs in.
- Verification: Live sole-admin lookup and reset-key validation passed; official HTTPS reset form rendered in setup Chrome. User then reported password saved; live audit confirms reset key cleared and active account sessions. Separate setup Chrome remains on login, so in-app authenticated dashboard interaction has not been inspected. Canonical lessons log is read-only; entry staged here.


### INSTAPASS-20261018-08 - GBP tests caught a discount boundary and CLI admin helper omission
- Date / project: 2026-10-18; independent GBP product editor.
- Category / status: Test expectation and isolated test bootstrap corrected; production unchanged during failure.
- Evidence / impact: A test treated 0.01% off GBP 20 as a valid lower sale price, but penny rounding yields GBP 20.00 and the validator correctly rejects equal regular/sale prices. Moved this case to invalid inputs and verified 0.05% yields GBP 19.99. Isolated WooCommerce persistence checks reached admin-field rendering, then failed because WP-CLI does not load woocommerce_wp_text_input's admin helper automatically.
- Cause: Invalid rounded-discount expectation; assuming front-end CLI boot loads editor-only helper functions.
- Correction / prevention: Keep half-penny rounding and too-small discount cases explicit; load WooCommerce's admin meta-box helper only in the isolated test. Test copy has no external network/ports and uses a disposable product in a cloned database, never live customer/catalog records.
- Verification: GBP syntax/value suite passes 11 valid and 18 invalid cases; USD/PKR regression passes. Corrected isolated WooCommerce persistence test passed nine checks with saved/reloaded fixture, permissions/nonce/stale validation, blank handling and USD/order preservation. Plugin 0.2.0 backed up, linted, installed and active; live read-only audit renders three fields, counts 85 products/3 orders, base USD. Normal-browser Instapass and Wasooli return 200. Signed-in browser editing and customer currency checkout remain unverified. Canonical log outside writable roots; staged here.


### INSTAPASS-20261018-09 - HTTPS diagnostics and authenticated browser/WPVibe verification
- Date / project: 2026-10-18; Instapass HTTPS and WPVibe connection.
- Category / status: Diagnostic harness errors corrected; authenticated access verified; no SSL/DNS repair necessary.
- Evidence / impact: Initial Node TLS check read res.socket.authorized after response end, when the socket could be null. Captured TLS state in the response callback instead. REST-index collection silently capped at 200 KB and truncated JSON; replaced with complete collection and explicit 10 MB rejection cap. Authenticated dashboard inspection reached screenshot but timed out; DOM read verification succeeded after omitting the optional screenshot. These were diagnostic failures, not site/TLS failures.
- Cause: Socket lifecycle assumption, silent response truncation, optional screenshot timeout.
- Correction / prevention: Snapshot TLS state before end, fail explicitly at size limits, separate optional image capture from authenticated DOM checks. Do not change DNS or SSL to fix an unconnected OAuth/WordPress connector.
- Verification: Strict TLS passes for root/www with trusted Let's Encrypt certificates; HTTP redirects HTTPS; WordPress URLs are HTTPS. Signed-in owner dashboard and six links confirmed. WPVibe browser authorization completed; connect_site and subsequent site_info confirm authenticated read for Instapass at https://instapass.online. Publishing/uploads remain untested. No DNS or SSL change performed. Canonical global log remains read-only; entry staged here.


### INSTAPASS-20261018-10 - Currency staging assumptions and canceled source lookup
- Date / project: 2026-10-18; owner exchange rate and UK GBP defaults.
- Category / status: Inspection/test harness assumptions corrected; currency staging tests in progress; no live currency change yet.
- Evidence / impact: A source search named a nonexistent manual-payments.js; bounded source inventory recovered. A combined web/WPVibe read stalled and was terminated before any writes; server CLI verified the legacy PKR option was 0. Replacing names in a copied test runner also renamed its bind source before the specific mount substitution, leaving an empty wrong mount; corrected to /tmp/control-next and added assertion. In-process Store API data contains nested stdClass objects, so an array-only test failed; normalize through JSON before assertions.
- Cause: Assumed asset path, blocked read tool, overlapping string transformations and response representation differences.
- Correction / prevention: Verify target files, avoid unbounded combined read calls, assert generated mount targets before running, inspect the actual API serialization contract. Inspect runtime checkout rather than inferring it from saved Blocks markup: the theme replaces Checkout Blocks with the classic shortcode.
- Verification: Pure money tests pass 13 cases including fractional-rate boundaries, 12 malformed inputs and five quantities; GBP tests pass. Final isolated suite passes 24 checks, including GBP/PKR orders, cart API, quote validation, historic orders and minor-unit preservation. Plugin 0.3.0 deployed with backed-up code/config. Browser save of approved 280.00 rate, currency controls/persistence, PKR API price, basket preservation and classic USD checkout rendering passed; no live order submitted and order count remains three. UK-IP simulation and real country lookup returned GBP/GB. Canonical global log is read-only; entry staged here.


### INSTAPASS-20261018-11 - Currency precision and browser verification refinements
- Date / project: 2026-10-18; editable PKR rate/GBP customer selection.
- Category / status: Precision design defect corrected and verified; verification assumptions recovered.
- Evidence / impact: Initially using zero global PKR decimals for integer product prices would also hide/round fractional discounts/refunds. Kept native two-minor-unit arithmetic and strip .00 only for integer PKR display. Added a real WooCommerce test: 33.33% off Rs 6000 retains Rs 4000.20, and Rs 6000.50 formatting remains visible. Defensive guards prevent querying private endpoints before WordPress query initialization or running the frontend feature without WooCommerce.
- Tool findings: Browser QA assumed a homepage Buy now link; current homepage featured unpriced products, so lookup timed out. Retrieved the actual checkout/shop URLs from WooCommerce and verified the standard checkout path. Remote image reads can return running call IDs; follow get_tool_result rather than assuming no image exists or retrying a pending operation.
- Correction / prevention: Separate product-unit rounding from currency accounting precision, test fractional discounts/refunds, inspect runtime URLs and handle Remote MCP async status. Do not infer checkout mode from stored Blocks markup.
- Verification: 24 isolated checkout checks, 13 money cases and final live browser checks passed after correction. Current rate 280.00 saved by owner form; all 85 live products and three orders retained. Phone screenshot inspected; viewport fit passed at 1440 and 390. Physical UK-origin traffic and real GBP/PKR receiving methods remain untested/unconfigured. Canonical global log remains outside writable roots; entry staged here.

### INSTAPASS-20261018-12 - Local execution and source patch tool recovery
- Date / project: 2026-10-18; Instapass pricing-controls step.
- Category / status: Recovered tooling limitations; source changes remain local and production verification is pending.
- Evidence / impact: Native unified PowerShell execution failed with CreateProcessWithLogonW error 267 because the mapped workspace cwd could not start. A first source replacement also missed because its anchor omitted an assignment prefix. The Desktop Commander shell accessed the canonical Windows workspace; the source was reread and the exact replacement then applied.
- Cause: Runtime cwd mapping mismatch and incomplete patch context.
- Correction / prevention: Use the authorized Windows Desktop Commander for this workspace after the first cwd failure; read exact source text before retrying a narrow patch. Keep the failure visible and do not infer a source defect from either tool failure.
- Verification: Desktop Commander confirms files were written in the intended project. No syntax checks, automated tests, browser checks or production update have been run for this version. Canonical global log remains outside the workspace write root; entry staged here.


### INSTAPASS-20261018-13 - License extraction wrapper assumption
- Date / project: 2026-10-18; local bulk-pricing package.
- Category / status: Recovered documentation tooling error; no source or production impact.
- Evidence / impact: First attempt to copy the SheetJS license through the MCP response parser expected the execution footer to be in the same text block as the file contents. The connector returns that footer in a separate content block, so the extraction stopped before writing anything.
- Cause: Assumed MCP content-block layout without inspecting each returned block.
- Correction / prevention: Read the actual text content block and isolate only its file header; the complete 201-line Apache 2.0 license was then written into the plugin assets.
- Verification: Desktop Commander confirmed the license file write. No tests, syntax checks or production writes were performed for this source version. Canonical global log remains outside the workspace write root; staged here.


### INSTAPASS-20261018-14 - Bulk verification mount permissions
- Date / project: 2026-10-18; Control Center 0.4.0 verification.
- Category / status: Isolated harness setup failure recovered; corrected suites passed.
- Evidence / impact: PHP lint container could not traverse /tmp/control-next because SCP created test-source directories with mode 0700 while the CLI image runs as a different user. The run stopped before product tests; only the temporary clone DB was started.
- Cause: Copied source-directory permissions were not made traversable for the isolated container user.
- Correction / prevention: Grant read/traverse access only to the uploaded non-secret test source and guard cleanup by exact test resource prefixes. No live WordPress directory permissions change.
- Verification: After scoped source permissions were corrected, all seven PHP files linted and the final bulk suite passed 26 checks. Canonical global log remains outside the workspace write root; staged here.


### INSTAPASS-20261018-15 - WP-CLI verification collector and late route registration
- Date / project: 2026-10-18; Control Center bulk verification.
- Category / status: Test harness defects recovered; invalid runs rejected; final collection verified.
- Evidence / impact: WP-CLI eval-file scope kept the collector arrays local while helper functions appended to globals, yielding zero reported checks. After explicit globals and an expected-22 guard, REST calls returned rest_no_route because WooCommerce had initialized REST before the skipped plugin source was required. Negative tests initially treated those 404s as expected refusals.
- Cause: Eval-file variable scope and late plugin bootstrap; overly broad negative assertion.
- Correction / prevention: Use explicit global result arrays and assert total check count. Register the late-loaded plugin routes in the existing REST server when missing. Reject rest_no_route in every negative request assertion.
- Verification: Corrected collection records 26 passing bulk checks; actual authenticated HTTP/REST and 21 browser workflows also pass. Zero-check and route-missing runs were not counted as evidence. Production catalog/order records unchanged. Canonical global log outside workspace write root; staged here.


### INSTAPASS-20261018-16 - Bulk pricing defects proven in isolated WooCommerce
- Date / project: 2026-10-18; Control Center 0.4.0 pricing.
- Category / status: Four confirmed code defects corrected locally and regression verified.
- Evidence / impact: Correctly bootstrapped isolated REST suite reported 17 passes/5 failures. Existing non-.99 USD regular values blocked global discounts because existing prices were incorrectly treated as newly entered values. Removing a non-.99 generated sale failed for the same reason. Direct server requests accepted sale+discount together. A one-shot audit-write failure duplicated a saved product row and could break Undo. An additional failure was the harness expecting 13.99 instead of the correct 14.99 for 25% off 19.99 with .99 rounding.
- Cause: Synthetic existing-price input reused manual-import validation; conflict validation only on client; append-only recovery duplicated an already collected row; wrong test arithmetic.
- Correction / prevention: Global/selected actions pass only the requested discount and preserve existing prices as baseline; reject conflicting and malformed/unsupported import fields server-side; key saved audit rows by product+currency; fix the numerical expectation. Retain regression checks including injected audit failure.
- Verification: Final bulk suite passes 26 cases including injected audit/second-product save failures. Browser suite passes 21 workflows and currency/checkout regression passes 24 checks. Original 85 products and three orders were unchanged in the clone. Production has not been updated. Canonical global log outside workspace write root; staged here.


### INSTAPASS-20261018-17 - Browser verification found legacy page collision and sparse spreadsheet errors
- Date / project: 2026-10-18; Control Center 0.4.0 browser verification.
- Category / status: Three confirmed integration/UI defects and a stale preview-status message corrected locally and browser verified.
- Evidence / impact: The actual cloned WordPress page rendered the theme's existing Pricing, Stock & Discounts screen at the duplicate instapass-pricing slug. Thus the earlier preflight assertion that no legacy route existed was incorrect. A unique instapass-control-pricing slug and returned page hook now separate the new controls. The fixed USD .99 checkbox was re-enabled by generic busy-state cleanup. A valid SKU/Currency/Regular-price-only workbook hit endsWith on an undefined discount field.
- Correction / prevention: Use unique plugin slugs and WordPress's returned hook rather than a guessed hook. Preserve fixed-control disabled state. Guard omitted optional workbook columns. Retain real wp-admin, sparse-column, workbook upload, save/reload and Undo tests. Preserve the legacy Products stock-management screen.
- Harness failures: Docker internal networks did not publish the requested localhost port; use an SSH tunnel to the isolated container IP instead. One diagnostic Docker --format argument lost quotes through PowerShell/SSH; use a saved shell script for complex remote commands. SheetJS's buffer output was not a native Node Buffer for Playwright upload; explicitly convert array output using Buffer.from before upload.
- Verification: Browser suite now passes all 21 checks after correcting policy disabled state, sparse columns and stale preview status. Desktop/phone screenshots visually inspected; local/tested source hashes match. A quoted SCP destination ending in a backslash was corrected by omitting the trailing separator. TLS health collection was also corrected to snapshot socket authorization in the response callback rather than defaulting true after response end (recurrence of entry 09); strict TLS plus HTTP 200 passed for both sites. Temporary test containers/network, SSH tunnel and disposable local login file are removed. This verification phase ended before deployment. The owner subsequently stated no product will be USD 1.99 and dismissed that hypothetical blocker; Control Center 0.4.0 was then installed after a fresh restore-checked backup. Thirteen live checks pass and catalog/order fingerprints are unchanged. Canonical global log outside workspace write root; staged here.


### INSTAPASS-20261018-18 - Switching currency retained a prior error status
- Date / project: 2026-10-18; Control Center 0.4.0 final live visual review.
- Category / status: Confirmed UI state defect corrected, published and verified.
- Evidence / impact: After an unpriced GBP global preview returned No priced products were eligible, switching to USD left that GBP error visible. The underlying USD catalog and pricing were correct, and the next preview worked. A network-isolated browser regression reproduced the stale status before correction.
- Cause: invalidate() updated the status only when a successful preview object existed, omitting prior-error states.
- Correction / prevention: Always clear status when pricing inputs invalidate the current view; use a new JavaScript asset build version so browsers fetch the fix. Keep the GBP error-to-USD transition in the live acceptance test.
- Verification: Focused regression passes three assertions (message cleared, preview hidden, no Apply request). PHP/JavaScript syntax passes. The UI fix is live with cache build 0.4.0.1; all 13 live checks pass including GBP-error to USD switching. Final local/deployed source hashes match and catalog/order fingerprints remain unchanged. Final machine-readable evidence distinguishes its pre-deployment snapshot from the current live state, replacing the temporary stale publication-pending label. No catalog/order write was needed. Canonical global log outside workspace write root; staged here.

---

## Consolidated source: pending-global-mistakes-payment-flow.md

# Pending global mistakes-log entries — 2026-10-07

## Website creation: accepted subscription label without verifying — corrected publicly, account still unverified
- Evidence: Assistant repeated user's description “Pro at $100” before checking the current official plan table. Official docs checked on 2026-10-07 list Pro at $20/month and Max 10x at $100/month.
- Impact: Could misstate included credit limits or supported models for the user's account.
- Cause: Treated a user-reported plan label as a confirmed current entitlement.
- Correction/prevention: Distinguish user's subscription description from public plan terms; verify actual Billing page before configuring models or consuming credits. Public API docs support API access on both Pro and Max, but credit consumption and existing extra-credit/auto-reload settings must be checked.
- Status/verification: Public documentation verified; user's exact subscription and billing settings unverified. No API calls or credits spent.

The global log is outside writable roots. These entries are staged for incorporation without overwriting existing records.

## Website creation: overly broad file search — resolved
- Evidence: Recursive .qa listing included node_modules/local WordPress and produced truncated output; literal wildcard file arguments in rg also failed on Windows.
- Impact: Unnecessary output and delayed targeted review.
- Cause: Search scope included dependency trees; wildcard assumptions were unsuitable for the shell.
- Correction/prevention: Use bounded top-level Get-ChildItem or explicit known files; exclude dependencies and local WordPress before recursive searches.
- Verification: Subsequent targeted reads located the package script and test infrastructure.

## Website creation: PHP WASM initialization — resolved
- Evidence: Runtime initially reported “PHPLoader.processId must be set before init”.
- Impact: First payment-handler test invocation failed before executing cases.
- Cause: Node runtime initialization omitted required emscripten processId.
- Correction/prevention: Initialize loadNodeRuntime('8.3', {emscriptenOptions: {processId: 1}}).
- Verification: Final 12 focused mock integration checks passed.

## Website creation: live email transport — unresolved environmental limitation
- Evidence: Existing WooCommerce order notes recorded “Could not instantiate mail function”; current New order test email failed visibly on 2026-10-07.
- Impact: Payment-report and verification email dispatch cannot currently be relied upon for inbox delivery.
- Cause: Mail transport is unavailable or misconfigured; exact server cause unverified.
- Correction/prevention: Configure an authorized sending service, then verify both owner and customer messages in real inboxes. Do not equate mocked dispatch or saved recipient settings with delivery.
- Status/verification: Flow deployed and customer form observed; email transport test failed. Explicitly disclosed in handoff.
- Follow-up 2026-10-07: FluentSMTP 2.4.1 installed and activated; live setup screen explicitly requires the first sending connection. Credential setup deferred pending secure admin transport and user-supplied account access. Mail is not yet operational.

## Website creation: SMTP activation selector mismatch — resolved
- Date: 2026-10-07.
- Evidence: Waiting for an Activate FluentSMTP link timed out; the UI exposes that control as a button.
- Impact: One failed locator wait; no site-data loss.
- Cause: Assumed the control role from standard WordPress plugin-list behavior instead of inspecting the install-result card.
- Correction/prevention: Inspect fresh DOM and use observed role/name before waiting or clicking. Button activation succeeded and the UI reported Plugin activated.
- Verification: FluentSMTP setup screen opened, version 2.4.1 was shown active in installed plugins.

## Website creation: QR renderer prepared; live rollout blocked by UI helper — pending
- Date: 2026-10-07.
- Evidence: The first PHP-WASM test launch failed before assertions because the new harness omitted the WordPress `add_filter` stub; the harness was corrected and all five QR rendering cases passed. Multiple shell starts returned `helper_unknown_error: setup refresh had errors`; required checks recovered through escalation. The computer-use browser helper then failed to initialize with the same Windows sandbox error on initial call, retry, and post-reset retry.
- Impact: Source and a version 1.9.1 theme ZIP are prepared locally, but the live WordPress order screen could not be inspected or updated. No live site state was changed.
- Cause: Test harness omitted a WordPress hook stub; Windows shell/browser helpers intermittently failed during environment setup.
- Correction/prevention: Stub required WordPress functions in the offline harness and rerun focused checks. After the browser helper's initialize/reset/retry sequence failed, stop UI automation and report the live deployment as pending.
- Status/verification: `instapass_render_payment_instructions()` emits an image for a valid `QR code: <image URL>` line, preserves ordinary clickable links, and safely falls back for non-image URLs. Five focused tests pass; staged diff checks pass; ZIP contents inspected. Live browser acceptance and deployment remain unverified.

## Website creation: return-to-payment email and manual confirmation workflow — source published, live delivery unverified
- Date: 2026-10-08.
- Evidence: Added a private order-key return link to the customer on-hold email in HTML and plain text, clarified the transfer-reference and “Payment made” steps, showed the exact order total in USDT only for USD-denominated Binance orders, and made the manual verification email explicit. The first new PHP-WASM email test emitted PHP 8.3 dynamic-property deprecations because its WooCommerce stub omitted declared gateway properties; adding the properties resolved the harness failure. One narrow test patch failed on stale context and was corrected after rereading exact lines. A direct web open of the site's raw IP was rejected as an invalid `ref_id`.
- Impact: Code is published, but live email delivery and customer browser behavior remain unverified. The existing SMTP setup still requires a sending connection; no real email was sent.
- Cause: Incomplete PHP test stub and a browser-helper setup failure already observed in the prior step; raw-IP web navigation was unsupported by the web tool.
- Correction/prevention: Mirror required WooCommerce gateway properties in PHP-WASM stubs, reread exact source after a patch-context failure, avoid treating mocked mail as live delivery, and use the supported browser connection for any future live acceptance. Keep the manual-verification gate and private order key.
- Status/verification: 22 focused offline checks passed across QR, customer email, proof submission, USD/non-USD Binance amounts, manual verification email, authorization and duplicate-reference cases. Commit `65acd15` is pushed and `main` is clean. Version 1.9.1 ZIP rebuilt and verified. No live WordPress update or email-delivery claim.

---

## Consolidated source: mistakes-pending-2026-10-08-refund-activation.md

# Pending global mistakes log update

- Date: 2026-10-08, Asia/Karachi.
- Project/task: Instapass refund-policy copy.
- Classification: Confirmed omission in the local draft; live publication unverified.
- Evidence: The prepared page-refunds.php changed missing-delivery wording to 45 minutes but retained activation taking longer than 15 minutes. The user clarified that activation must also use 45 minutes.
- Impact: The draft contained inconsistent time thresholds.
- Cause: The previous change was interpreted as delivery-only and did not reconcile the activation clause.
- Correction/prevention: Change activation to 45 minutes and check delivery, activation, change-of-mind exclusions, and the seven-day pro-rated term individually before publication.
- Status: The local activation clause was corrected by a context-matched patch. Live browser review and publication remain unverified because browser-control tools are unavailable in this chat.
- Verification: The patch matched the old 15-minute activation paragraph and replaced it with 45 minutes. No live-save or browser-read evidence was obtained.
- Global log synchronization: Pending because reading the canonical log through local execution failed; preserve and re-read its latest contents before merging this entry.

---

## Consolidated source: mistakes-pending-2026-10-09-pricing.md

# Pending global lessons update

Canonical log: C:\Users\Aly Jafferani\.codex\mistakes.md. Staged within the authorized workspace; not merged globally.

## INSTAPASS-20261009-01 - Runtime and investigation failures

- Date/project: 2026-10-09, Instapass Telegram and bulk pricing.
- Category/status: Environmental limitation partly recovered; investigation mistakes corrected.
- Evidence/impact: sandbox terminal failed to create a process; Node REPL rejected the Linux-style sandboxCwd as not a local Windows URI. An approved /bin/sh command successfully read the actual WSL workspace. Initial investigation spent unnecessary time paging prior chat history before testing shell access with escalation. A read_thread request used 30 turns where maximum is 10. Broad history and source outputs truncated repeatedly. Linux Python lacks openpyxl; bundled Windows runtimes are available.
- Cause: mixed host/sandbox path handling; assumed shell availability; oversized reads and unchecked tool argument limit.
- Correction/prevention: use the approved /bin/sh path for needed operations, inspect tool schemas, keep bounded source reads, and preserve source/offline/live verification distinctions. Node REPL browser remains unavailable; terminal recovery does not establish browser recovery.
- Verification: approved terminal read succeeds and the public server returns HTTP 200; browser control and deployment are not established.
- Recurrence 2026-10-10: User explicitly requested self-upload. Fresh supported browser bootstrap again failed before executing code with MCP -32602: sandboxCwd is not a local file URI (file:///mnt/c/Users/Aly%20Jafferani/Documents/ChatGPT/Website%20creation). No authenticated WordPress connector is available. Broad tool-description and log searches again exceeded output budgets; bounded exact-entry reads recovered the relevant evidence. Official Windows documentation supports selecting Windows native agent mode and restarting, but that recovery is proposed and unverified for this specific error. Requested that connection repair; do not ask for publication approval again. Public theme style.css returned HTTP 200 with version 1.9.0; prepared ZIP is 1.10.0, CRC clean, SHA-256 d63dc19c3a1ef9686dadff62d27cbf8770a5bc920c44ad5a67b441fc826e2a59. Status: upload blocked, no live changes made. Verification evidence: outputs/UPLOAD-RETRY-2026-10-10.json. Global log update remains staged due filesystem scope.

## INSTAPASS-20261009-02 - Patch context and oversized source reads

- Date/project: 2026-10-09, bulk pricing implementation.
- Category/status: Confirmed implementation-process mistakes; corrected.
- Evidence/impact: initial patches assumed a different admin heading and treated a compact PHP line as a multiline fragment. apply_patch rejected both atomically. Several later combined reads again truncated, despite the prevention rule above.
- Cause: patches used assumed rather than exact current context; outputs were budgeted by file count rather than line length.
- Correction/prevention: inspect the exact target lines first, use narrow context or an asserted transformation, and bound long-line snippets individually. No partial patch was accepted.
- Verification: new module is included under the actual Pricing, Stock & Discounts heading; all 34 theme PHP files parse.

## INSTAPASS-20261009-03 - Excel percentages and library file API

- Date/project: 2026-10-09, Excel pricing import/export.
- Category/status: Confirmed parser omission and runtime limitation; corrected.
- Evidence/impact: Excel stores a displayed 25% as 0.25; the first parser would have interpreted it as 0.25 percentage points. The vendored browser SheetJS build could parse buffers but its Node writeFile helper failed to save the workbook.
- Cause: numeric cells were read without number-format metadata; a browser-oriented library was assumed to have a configured Node filesystem adapter.
- Correction/prevention: read cellNF metadata, normalize percentage-formatted discount cells to percentage points, reject date-formatted price/discount cells and formulas. Write/read buffers through Node fs for offline workbook generation. Use UTF-8 metadata in browser fixtures to avoid mojibake from a missing fixture charset.
- Verification: real XLSX round trips cover 25%, date rejection and formulas; 20 JavaScript checks and 368 PHP checks pass. Workbook reopens with 85 research rows and 30 upload rows. Local Chrome UI upload/download and retry checks pass with mocked REST; live WordPress remains unverified.

## INSTAPASS-20261009-04 - Provider comparisons and batch-save uncertainty

- Date/project: 2026-10-09, researched pricing and bulk saves.
- Category/status: Confirmed omissions found before publication; corrected locally. Source/production gaps remain open.
- Evidence/impact: the first research pass treated ten original offers as monthly even though their source omits an access duration. A provider URL for Freelance Stack used the wrong domain. Initial batch code could ignore a final audit write failure or miss a price persisted before an after-save hook threw.
- Cause: a provider billing period was conflated with the store offer's duration; domain and success-state assumptions were insufficiently checked.
- Correction/prevention: preserve Excel tiers and validity exactly; hold missing terms, combined tiers, sites/seat mismatches, usage bundles, non-USD rates and free/quote-only offers. Use freelance-stack.io. Journal each intended row before writing, store history before any price change, verify post-failure state, stop at the first failure, refuse duplicate submissions and return an explicit audit failure instead of success.
- Verification: complete 85-SKU register, 30 matched USD updates and 55 held rows. Strict actual discount bounds pass across currency precisions. Tests cover failure before save, failure after persistence, history-storage refusal and guarded undo. Browser publishing fails before connection with sandboxCwd local-URI error, so no live prices or contact changes are claimed.

- Oct10 native-session check: This task now runs in PowerShell at C:\Users\Aly Jafferani\Documents\ChatGPT\Website creation, but the supported Node REPL/browser-control tool is absent from the active tool list, so the authenticated browser connection itself could not be tested. The site's read-only WooCommerce Store API returned HTTP 200 with JSON (one product requested). At that check WPVibe was not installed; it was later installed and the WPVibe account authenticated, but no WordPress site registered. Cause: session tool availability changed; publishing access remains unresolved. No upload or live mutations occurred. Next: resume only after an authorized browser-control route or WPVibe connection becomes available. Verification: the connector now reports installed=true; a later site list reports no registered sites; public Store API returned status=200; theme remains unuploaded.

- Oct10 WPVibe site-connection attempt: The connector is installed and authenticated, but list_sites reports no registered sites. connect_site over HTTP says WordPress Application Passwords are unavailable; the HTTPS attempt returns 403 from the REST index and /wpvibe/v1/ping when called from WPVibe (relay HTTP 200 does not establish WordPress access). No site was connected; no upload or live mutation occurred. Cause is unresolved; a source-IP block is not established. The connector directs the owner to run its WordPress plugin connection check and share the exact endpoint/time/request-ID report with WPVibe support and the host if it continues. Status: deployment blocked pending REST access and site registration. Preserve verification distinction between public-site reachability and authenticated WPVibe access.

- Oct10 retry after user installed and activated the WPVibe WordPress plugin: WPVibe list_sites still reports no registered sites. A fresh HTTPS connect_site call again returns 403 for both the REST index and /wpvibe/v1/ping from WPVibe's address. Plugin activation did not establish connector authorization. No live changes occurred. Cause is still unresolved; WPVibe's connection-check report from wp-admin is needed to identify the site-side REST/auth configuration. Status: upload blocked. Preserve public Store API HTTP 200 as separate from authenticated REST access.

## INSTAPASS-20261018-19 - Git ownership guard during canonical-log tracking

- Date / project: 2026-10-18; Instapass project log and Git tracking.
- Category / status: Repository ownership limitation recovered; scoped Git handling used.
- Evidence / impact: Initial git rev-parse failed with dubious ownership because the checkout belongs to CodexSandboxOffline while Desktop Commander runs as the owner Windows account. No Git or source mutation occurred in that failed read.
- Cause: Two authorized local runtimes use different Windows identities.
- Correction / prevention: Use per-command safe.directory for this exact verified checkout. Do not change global trust, ownership or unrelated repositories. Verify the repository root before status/staging and commit only the named log/instruction/documentation paths.
- Verification: Scoped rev-parse confirmed the exact Website creation root; the existing index was empty before this task. All four original note files are preserved here. The scoped index contains only MISTAKES.md, AGENTS.md and README.md, and staged whitespace validation passes. Git history records the canonical-log commit.

## INSTAPASS-20261018-20 - Restock integration and isolated-test setup

- Date / project: 2026-10-18; email/restock notifications.
- Category / status: Pre-release defects and test environment errors corrected; isolated and live verification completed below.
- Evidence / impact: A first file write targeted an absent new plugin directory; created it before retry. The first isolated Docker run could not create a child bind mount below the read-only WordPress mount; no application assertions ran. A helper script template also interpolated Bash array syntax as JavaScript and was rewritten before execution. A test hook invocation supplied one argument where the existing theme requires three and failed inside the copied database only.
- Confirmed source review findings: The theme replaces out-of-stock add-to-cart content at priority 20, which would discard an earlier appended signup form. Subscribe logic also risked treating an old confirmed signup as expired; atomic confirmation claims needed the unsent predicate to prevent concurrent duplicate sends.
- Correction / prevention: Test with a copied plugins directory bound at the existing parent mount; preserve production as read-only. Mirror all hook arguments, run the form filter after theme replacement, retain confirmed requests, and claim unsent confirmations atomically. Intercept all fixture mail and use explicit global references in WP-CLI eval-file collectors.
- Verification: PHP lint passes; lifecycle and browser verification pending at this entry. Existing production products, orders and email configuration unchanged.

- Final verification 2026-10-18: 40 isolated integration checks pass; 19 actual WordPress browser checks pass, including phone layout, real consent links, guest signup, stock transition and owner inventory save. First browser launch used the wrong private fixture-file directory and a broad email selector matched both signup and review forms; corrected to the ignored .qa file and #instapass-alert-email. Screenshot review caught a wide owner table; contained its scrolling and added a phone-width assertion. No fixture mail left the internal Docker network.
- Live activation: new module installed paused; all 85 products/prices/stock and three orders retain matching before/after fingerprints. Authenticated owner page returns 200 with test control and daily budget100. Gmail credentials are now configured, unlike the dated October7 note, but live sending/inbox receipt remains unverified; no test or customer email was sent in this installment.

## INSTAPASS-20261018-21 - Empty Action Scheduler group rejected by CLI

- Date / project: 2026-10-18; Instapass stock-alert background runner.
- Category / status: Confirmed first-run integration error; corrected and verified.
- Evidence / impact: Initial service failed because action-scheduler run --group rejected the new group before any subscription/job existed. Alerts were paused and no customer data or mail was affected.
- Cause: Assumed a named group exists before the first scheduled action.
- Correction / prevention: A CLI wrapper queries this exact group's pending jobs first, returns success when empty, and invokes the scoped runner only when a job exists. Never remove the group filter or disable other WooCommerce queues to bypass this case.
- Verification: Live systemd timer active, service Result=success and ExecMainStatus=0. Isolated runner test processed a due probe in its group, preserved another group's due probe, and returned success with an empty queue. Recovery backup retained at notifications-010-deploy-20261018T094014Z.

## INSTAPASS-20261018-22 - Local contact update had not reached the live footer

- Date / project: 2026-10-18; reconciliation of owner-approved @Instapass0408.
- Category / status: Confirmed existing deployment gap; corrected live.
- Evidence / impact: Authenticated live homepage DOM still linked to the old @AJNF48 handle despite corrected local theme source. Earlier support-preservation statements were not proof of current live contact state.
- Cause: Corrected local theme source/render filters were not active in the deployed saved template path.
- Correction / prevention: Small contact-only must-use plugin replaces the exact old Telegram URLs/handle during block/content rendering, including saved Site Editor content. No email address, price copy, theme design or database content was rewritten. Verify real DOM href and text after deployment.
- Verification: Live homepage footer now displays @Instapass0408 and links to https://t.me/Instapass0408; both contact-page Telegram links match. Product/order fingerprints remain unchanged. Plugin lint and SHA256 verified. Rollback is removal of this one mu-plugin file; previous theme/data remain intact.

## INSTAPASS-20261018-23 - Signup throttling behind a private reverse proxy

- Date / project: 2026-10-18; final restock signup review.
- Category / status: Implementation risk identified before customer activation; corrected and tested. No observed customer blocking claimed.
- Evidence / impact: Keying a request limit solely by REMOTE_ADDR can group every visitor behind one internal proxy address, potentially imposing one shared10/hour limit.
- Cause: Initial limiter assumed the application always sees a public client address.
- Correction / prevention: Public peers retain an IP-keyed limit. Private/unknown peers use a keyed hash of the submitted email instead; do not trust arbitrary forwarded headers. Keep the independent daily send budget and confirmed-request deduplication.
- Verification: Final isolated integration suite passes43 checks, including separate customers behind one private peer, shared limit for one public peer and absence of plaintext addresses in rate keys. Full guest browser workflow repeated. No production subscription/email created.

## INSTAPASS-20261018-30 - Guest commerce review correction and missing tracking

- Date / project: 2026-10-18; guest checkout, coupons, tracking and manual payment audit.
- Category / status: Confirmed review misunderstanding and feature/deployment gaps; repairs under test, not yet deployed.
- Evidence / impact: The preceding response described 65 blank-priced, non-purchasable listings as a checkout-readiness issue. Prior owner decisions explicitly require those public Price on request listings; live audit confirms 20 priced, 65 unpriced. There is no tracking page. Live theme payment-email code is older than the prepared private-return-link improvements. Guest checkout and coupons are already enabled; earlier empty-cart evidence did not test gateways.
- Cause / prevention: Reconcile live audit with PROJECT-DECISIONS and intentional catalog policy before labeling defects; test a populated disposable cart. Preserve unpriced listings and historical orders. Optional signup must not authenticate an unverified email. A first-order promo follows receipt email only, accepting new addresses as requested.
- Tool corrections: Old-source SSH key failed; use the authorized rdi-tracker destination key. Root-protected scp failed, then a trailing-backslash destination quote failed; copy only non-secret code into owner-readable staging and use relative local destinations. Initial plugin writes lacked a parent directory and failed without changing files; create directory first. Two script-authoring cells failed before any tool ran because Bash array interpolation was interpreted by JavaScript; use a shell function instead. Git ownership handled with exact-checkout per-command safe.directory, never global trust. No secret values printed.
- Verification: Fresh read-only audit: 85 published products, 3 orders; configured USD Binance/crypto, unconfigured bank/EasyPaisa. Product/order hashes retained for comparison. Isolated integration/browser acceptance, backup, deployment and live readback pending.


- Verification / correction for INSTAPASS-20261018-30 (completed 2026-10-19 PK): 11 real-WooCommerce integration, 29 browser workflow, 9 parallel-reference/new-email, and 4 native coupon-editor persistence checks pass in a network-isolated clone with intercepted mail. Two additional header tests and 16 public live browser checks pass. Fresh 58-table backup restoration/integrity passed. Guest Orders 0.1.0 and two reviewed theme files deployed; 20 priced/65 intentional unpriced products and 3 historical orders retain identical hashes. Root rollback copies retained.
- Fixture-only failures corrected before acceptance: staging directory permissions prevented PHP lint from opening copied source; give reviewed non-secret code readable permissions. Initial cloned home/siteurl filters did not update constants, blocking local assets; update cloned options before web startup. Read-only uploads caused Woo log warnings; use a fixture-only writable tmpfs. The first successful checkout used a different confirmation-page wrapper than the test expected, and discount getter returned 3.2 rather than 3.20; assert actual order-received URL and numeric money. Repeated harness runs reserved coupon usage; use unique fixture emails and fresh isolated DB. A response listener tried to read a navigated response; removed it. Live CSS deliberately shows the coupon field and hides its toggle, so test field visibility before clicking. These are harness corrections, not claimed production defects.
- Confirmed additional mail gap: actual controlled production wc_mail message reached the correct Gmail From orders but lacked Reply-To, unlike native configured WC_Email headers. Added a default support@ reply header while preserving an explicit existing header. Second received test proves From orders@instapass.online, Reply-To support@instapass.online, inbox delivery, SPF/DKIM/DMARC pass. No customer mail or real payment was used. Sanitized header evidence only is retained in outputs; message bodies, keys and full headers are excluded from Git.
- Scope remaining: owner must supply receiving details for disabled EasyPaisa/bank methods and GBP setup. No invented account details, live first-order discount or missing catalog price. Actual funds and automatic license provisioning are outside simulated acceptance.

- Git handoff correction: PROJECT-DECISIONS.md is an existing ignored local document, not present in HEAD; a scoped staging helper incorrectly assumed it was tracked and stopped after staging only this task's log/status sections. No prior content was removed or committed. Retain that local decision document, use the tracked acceptance record for this task, and stage only the two tracked own appendices plus new task source/tests. Confirm index names before commit.

## INSTAPASS-20261019-31 - Final phone checkout wording

- Evidence / impact: Live phone screenshot showed a duplicated optional marker and older instructions to reply with a transfer reference. The newly deployed private payment form worked, but this copy could confuse customers.
- Correction / prevention: Let WooCommerce append its own optional marker, direct payment proof to the private order page, and clarify the default receipt/activation email. Review actual phone screenshots after functional tests.
- Verification: Guarded, linted two-file update with rollback at /opt/instapass/recovery/guest-commerce-copy-20261018T190834Z. All 18 live checks pass, including both wording assertions; all five deployed source hashes match. An overescaped optional-marker regex initially failed the new assertion; literal split corrected the harness. Product/order fingerprints remain unchanged. No new checkout business logic or real order/payment was introduced.

## INSTAPASS-20261019-32 - Bundle promotion policy and test expectation

- Date / scope: 2026-10-19; two basket-size coupons. The owner superseded first-order eligibility with 5% for two different products and 10% for four; this is a requested policy change, not a defect in the earlier implementation.
- Confirmed test mistake: The first isolated run expected a newly entered individual-use coupon to replace the existing code. Native WooCommerce rejects the second until the first is removed. No stacked discount or production change occurred. Correct the test to verify one code remains and explicit removal permits switching. A guessed prior browser-test filename did not exist; list actual files before reading.
- Prevention / verification: Test native coupon behaviour and money in a network-isolated clone with intercepted mail; independently verify owner saves and public live carts before claiming deployment. Preserve all original product/order fingerprints and unrelated Git changes. Global log synchronization remains pending outside the writable project scope.

- Corrected diagnosis: The integration readback was {1: toolkit10}; native replacement succeeded and preserved a non-zero array index. Both strict array comparisons were faulty test expectations. Assert the non-stacking invariant and inspect actual values before describing behaviour. At an eligible four-product basket a newly entered code replaces the previous one. Earlier commentary saying removal is required was corrected. All 16 isolated integration checks now pass.

- Browser harness correction: initial owner-form assertion assumed lowercase option text although configuration preserves uppercase code labels and WooCommerce normalizes the actual code. Compare case-insensitively. Coupon calculations and phone layout passed before that assertion; rerun the corrected browser test to verify actual owner saves. A diagnostic shell command also lost a quoted Docker display format under Windows SSH; use saved scripts for multiword remote arguments.

- Final verification: Guarded two-file deployment plus managed coupon configuration succeeded; source bytes match, product/order fingerprints unchanged (85 products,3 historical orders).16 native integration,14 isolated browser and11 live public browser checks pass. No live order/payment/account/email created. Dedicated clone resources, tunnel and synthetic credentials removed. Recovery at /opt/instapass/recovery/bundle-offers-deploy-20261019T023125Z; current acceptance in ops/BUNDLE-OFFERS-ACCEPTANCE.md. Previous first-order testing is historical after the owner's explicit policy change.

- Recurrence 2026-10-19 (bundle tenor clarification): a cross-plugin text search included minified third-party XLSX JavaScript and truncated output. Restrict implementation searches to *.php with bounded line lengths; corrected search returned only the access-duration metadata field. No site change. Verify native coupon product/category restrictions and deployed source before confirming scope.

## INSTAPASS-20261019-33 - Separate tenor offers

- Scope / policy: Owner added same-product higher-tenor purchases after the previous distinct-parent-product rule. Implement separate simple listings linked by product family so each tenor can keep independent pricing/stock and existing currency/restock compatibility. This supersedes the prior same-parent-tenor exclusion; do not invent missing sale terms.
- Verification gaps to resolve: actual live catalog has only simple listings, and currency GBP editor currently targets simple products. Do not convert live listings to variable products or claim variable-tenor acceptance from simple-product tests. Test linked tenor listings in a network-isolated clone, preserve original catalog/orders, and hold new commercial offers until durations/prices supplied.
- Tool correction: A guessed inc/checkout.php search path did not exist. Listed files identified inc/guest-checkout.php; use discovered paths for subsequent reads. No site mutation from that failed search.

- Final verification for entry33: Installed Tenor Offers 0.1.0 plus guarded Promotions/Guest Orders 0.2.1 updates after a fresh 58-table restore-checked backup.22 native integration,22 isolated browser and11public live browser checks pass. Native owner family-field save persists; actual separate tenor add flow preserves cart and order durations. Repeated family/tenor listings and 1 year/12 months aliases count once. Runtime sources match; products/orders/coupon audit values unchanged. Recovery:tenor-offers-deploy-20261019T024515Z. Isolated resources/tunnel/credentials cleaned. New commercial tenor listings remain explicitly pending missing owner prices/terms, with no invented publication claim.
- Follow-up verification: Extended the native fixture to use two different tenor prices rather than relying only on equal-priced examples. All24native checks pass, including subtotal31.98,5%discount1.60,total30.38. Same runtime sources as the22browser/11live checks. The additional clone and server fixture credentials were cleaned; listener check confirms no test tunnel. Owner named ChatGPT Plus, Claude Pro and Claude Max; actual longer terms/prices remain pending, with Max plan scope asked explicitly.

## INSTAPASS-20261019-34 - Claude tile wording identity guard

- Request: Add owner-supplied wording "25% additional limits" to Claude Pro and Claude Max 5x tiles; exclude Max20x as clarified earlier. No provider-plan verification is claimed by this copy change.
- Confirmed tool assumption: first read-only preflight used an ASCII5x title matcher; product identity guard failed before any write. Changed read-only mode to report actual identities and allow x/multiplication-sign labels in the write guard. Verify exact current IDs/SKUs before applying; preserve existing tile brief text and product/order fingerprints.
- Status: live copy not yet changed; readback and rendered tile verification pending.

- Preflight correction: replacing the title matcher with verified exact SKUs left one extra closing parenthesis; the PHP read-only capture failed to parse before mutation. Removed that delimiter and reran the guarded capture/apply sequence. Use a complete asserted guard-line replacement and lint before mutation on future edits.

- Final correction/verification for entry34: exact verified SKU guards passed; both tile briefs saved idempotently with original text preserved.8 live rendered tile/width checks passed and both mobile screenshots inspected. Max20x unchanged; product-price/stock and order fingerprints unchanged. Recovery:claude-tile-copy-20261019T031306Z. Only public copy metadata changed, no runtime deployment. No real checkout/payment/customer message was created.

## INSTAPASS-20261019-35 - Three-selection 7.5% offer

- Owner request: add7.5%at3product/tenor selections, preserving5%at2and10%at4; guests/returning customers and one-code-per-order remain. New slot2 uses TOOLKIT75; established slot/option/coupon IDs remain intact and source display orders2/3/4selections.
- Environment issue: Desktop Commander start_process timed out at300seconds with unknown execution outcome. Reconciled active sessions and server containers before retrying; original PID14412 is running the intended isolated setup, so did not duplicate it. A write_file async call was retrieved via get_tool_result rather than resubmitted. Connection recovered; source not yet deployed.
- Prevention / acceptance: require native money/order and owner decimal-save checks, preserve both existing coupon settings, guard live source hashes and compare product/order state; no live order/payment/customer message. Verification pending.

- Confirmed transfer failure: the recovered session reported an SCP connection reset, then continued to run stale server fixtures (24 old checks). Those checks are not evidence for the new7.5%offer. Original command lacked native-exit guards between transfers. Replaced with a bounded verified uploader that stops on any failed transfer, compares every staged SHA256 against local source, and runs the clone only after all9uploads match. No production code/offer was changed by the stale fixture run.

- Browser fixture correction: full-load navigation timed out while re-enabling the pre-existing four-selection offer; a fresh authenticated read showed it remained disabled. A fresh native save restored it successfully with DOM-content navigation. Updated owner-save waits to DOM-content completion while retaining persisted field assertions; rerunning the full browser checks. This is a fixture timing issue under investigation, not a claimed production defect. Native31checkout checks already pass against all9hash-verified uploads.

- Confirmed browser-harness cause: a single-occurrence rename changed only the first Save button selector; the re-enable step still searched for old "Save bundle offer2" instead of "Save TOOLKIT10". Navigation timed out because the wrong button was never clicked. Replaced all occurrences and confirmed none remain. This supersedes the earlier timing hypothesis; no production controls defect was found. An asserted replacement-count guard also correctly stopped an attempted harness edit before writing.
- Live coupon checks passed through the new7.5%total, but Node APIRequestContext could not verify Wasooli's certificate chain. Verify Wasooli through a fresh browser page with TLS validation still enabled; do not bypass certificate checks or label the co-host unhealthy based only on Node trust-store differences.

- Final verification for entry35:31 native and28 isolated browser checks passed after correcting all stale button selectors.Eight focused live Instapass checks pass, including7.5% discount7.42 and total91.55, replacement without stacking and390px layout.Runtime bytes match; old coupon settings and product/order fingerprints preserved.Test resources, tunnel and credential file cleaned.Separate Wasooli Chromium check also reports ERR_CERT_AUTHORITY_INVALID; kept this explicit failure in the focused report, did not bypass TLS or change its service.An asserted health-context edit failed safely before writing and was corrected using actual file text.Scoped recovery:three-product-offer-20261019T042805Z.

## INSTAPASS-20261019-36 - Owner tenor pricing and fixed PKR exception

- Requirements: Plus annual170USD/47500PKR; Max5x75monthly,432six-month upfront and833.33annual upfront so10% rounds750.00.Annual supersedes initial67/month quote.Exclude Max20x; no invented Claude Pro terms.Native coupons still require eligible bundle counts.
- Confirmed tool error: an extra closing delimiter caused the first orchestration call to fail parsing before any file mutation.Removed it and reran.Keep whole-module orchestration syntax simple and inspect file bytes before deployment.
- Feature gap: existing PKR always converts USD; needs explicit owner-editable override with validation, nonce/capability/stale guards and default conversion preserved.Exact supplied USD prices also need bulk-import exemptions scoped per listing.Preflight verified current product identities and blank access durations; asked the owner about existing Plus duration before filling it.
- Acceptance pending: isolated native money/order/coupon and owner controls, guarded source/catalog deployment, live browser verification, unchanged unrelated offers/orders/payment settings.

- Superseding owner instruction: Plus current14.99 offer confirmed1month; annual USD list becomes173.33 to round156after10%, replacing170.PKR47500 remains the separately supplied fixed price.Annual Max833.33 rounds750after10%.Commercial input changes are not defects; retain sequence and do not publish stale initial amounts.

- Isolated harness failure: strict identity comparison of Woo product get_data arrays reported excluded Max20 changed; these include newly instantiated object values.Replaced with canonical JSON content comparison so unchanged values, rather than object identities, are tested; rerun must prove equality.Fixture HTTP is now explicitly intercepted and progress printed, in addition to its external network isolation.The dedicated clone auto-cleaned on failure; no production change occurred.A late diagnostic correctly found its already removed containers rather than warranting duplicate cleanup.

- Follow-up isolated pricing result: canonical JSON confirms excluded Max20 unchanged.10 pricing checks passed, but annual Max discounted-line target assertion failed before publication.Add actual native line/currency/coupon readback to distinguish fixture configuration from a calculation defect; do not loosen the750/156 acceptance targets or deploy before they pass.Backup pre-commerce-20261019T044646Z restored58tables and passed database integrity.

- Verified cause of annual-line assertion: native WooCommerce allocates the rounded basket-wide percent discount across lines. In the1513.66USD four-selection basket the total1362.29 is correct and Max annual receives749.99, one cent below the simple833.33x90% rounded expectation.This is native allocation, not a coupon defect.The owner requested around750; corrected the over-strict exact-line test to a stated one-cent tolerance and the product copy to about750/156, retaining native allocation and order persistence.This supersedes the earlier self-imposed exactness wording; do not modify checkout arithmetic merely to satisfy a faulty test expectation.

- Owner-field harness issue: WP-CLI eval-file local variables do not populate the global product_object used by native admin fields. Existing GBP integration fixture shows the correct GLOBALS assignment and explicit Woo admin helper includes. Applied that setup, dispatches native product-save action rather than only direct handler calls, and added nonce/anonymous/absent-field/clear-override plus fixed-PKR-order persistence checks.No owner UI source defect was established by the failed render assertion.

- Visual omission discovered after pricing publication: new tenor listings copied the original monthly descriptions, including legacy delivery/warranty text, and the required25%additional-limits note appeared after a long brief that can be clipped on narrow cards.Correct only new offer descriptions to confirmed term/upfront-payment text and current refund-policy link; move the already authorized25%note to the beginning for Pro and Max5x tiles.Exclude Max20x and preserve all prices/stock/orders/coupons/payment settings with before/after audits.Updated future new-offer creation so legacy monthly descriptions are not copied again.Live copy verification pending.

- Final verification for entry36:29native pricing +24existing currency checks pass;14live pricing +18live copy/width checks pass.Woo native test annual line749.99 is documented, not claimed exactly750 in every basket.New IDs138/139/140live; Plus29one month14.99; Max31one month75.FixedPKR47500 verified in checkout.Pricing release guard preserved83unrelated catalog records and all3orders/rate/coupons/payments.Copy patch audit preserved all prices/stock/orders/settings; prefix25%note visible and unclipped at390/1440.New descriptions now use confirmed upfront terms and current refund-policy link.Catalog recovery:approved-prices-20261019T045109Z;copy recovery:approved-prices-copy-20261019T045337Z.No real orders/payments/messages; clone resources auto-cleaned.Full Control Center baseline and nonsecret required verification helpers need tracking because those earlier local sources were not yet in HEAD; unrelated project files remain out of this scoped release.

- Final replay harness correction: cloning production after publication includes the already-published release SKUs; the first-time staging assertion therefore correctly failed because it expected new drafts.The fixture now removes only its marker-verified release products from the isolated database before recreating draft offers.Production records are never deleted by the fixture; its environment guard and isolated network/database remain mandatory.Rerun required before final commit; earlier successful29checks and live results remain distinct evidence.

- Post-publication replay now passes all29checks with fresh recreated fixture products.Draft assertion was a harness baseline assumption, not a live defect.Updated the earlier live coupon harness expectations because owner-requested monthly Max74.99to75changes its three-item total91.55to91.56 and four-item total224.06to224.07; coupon percentages stay unchanged.Added a focused post-price-change TOOLKIT75 live check to the current pricing browser report.

- Final current-state receipt: all17live pricing checks pass after the monthly price change, including TOOLKIT75 discount7.42/total91.56 and all three displayed codes.18copy/width checks pass; final post-publication clone replay29passes.Implementation/status receipts updated.Global log synchronization is staged in ops/PENDING-GLOBAL-LESSONS-20261019-PRICING.md because the global path is outside the writable workspace; project entry remains the committed canonical Instapass record.

## INSTAPASS-20261019-37 - Monthly equivalents and owner tax policy

- Owner clarification: retain Max5x annual833.33 targeting62.50/month after eligible10%; work back to base69.44,5%65.97,7.5%64.24. Present monthly equivalents and upfront term totals together; preserve stored/full-term cart/order amounts. Owner asks to absorb applicable taxes; current Woo tax calculation is disabled with no rates, so no extra checkout tax is added. This records a customer pricing policy, not evidence of remittance or advice on tax obligations.
- Requirements: storefront cards/product prices/tenor choices use selected-currency monthly values; coupon conditions explicit; all3 tiers detailed on multi-month product pages; taxes-covered message on storefront and checkout; unpricedGBP remains unpriced; no new tax rates/payment/account changes. Verify static financial state, native synthetic totals and actual phone/browser rendering.
- Scope/process correction: project AGENTS.md makes MISTAKES.md the Git-tracked canonical Instapass log and supersedes pending-global pointers. The prior separate pending-global note was unnecessary; do not add another split log for this task. No global memory edits.
- Status: local implementation prepared; isolated and live verification pending. Annual $65 ambiguity was resolved directly by owner in favour of62.50 after10%, so no annual repricing is required.

- Confirmed visual defect: initial29live browser checks passed text-presence and page-width assertions, but the actual390px card screenshot clipped monthly/upfront text inside the card. Page-level width checks do not prove card text is visible when an ancestor clips overflow. Inspect computed card/price bounds, correct inherited width/white-space rules, and add element-level wrapping/visibility checks before final acceptance. Financial30checks and preserved catalog/order audits remain valid; UI acceptance is not complete yet.

- Confirmed cause: native product price row used max-content sizing, expanding the price to326px inside a172px card whose overflow was hidden. Corrected the price row and its price block to use available width, made the monthly unit wrap on its own line, and added element-level bounds/scroll checks at both viewport widths. Cache version bumps with the CSS patch; full financial logic remains unchanged.

- Final verification for entry37:30isolated native checks passed,31live checks passed after the card-width fix, and the final phone card screenshot was inspected. Native totals/tax/order persistence, all4annual monthly-equivalent rows, explicit prepaid amounts/coupon conditions, PKRfixed override and missingGBP all verified. Actual staged/runtime sources match; all price/stock/order/coupon/payment and tax audit values unchanged. No actual customer order/payment/email; dedicated test containers auto-cleaned. Release recovery:monthly-pricing-20261019T050834Z;CSS repair:monthly-card-wrap-20261019T051211Z;58table restore/integrity backup:pre-commerce-20261019T050711Z. Corrected the old pending-global note into a superseded pointer per project AGENTS.md; new evidence remains only in this canonical Git log.

## INSTAPASS-20261019-38 - Annual Max70 monthly base

- Owner policy update: accepted70USD/month equivalent for Claude Max5xannual,840upfront. Eligible5%=66.50/month798upfront;7.5%=64.75/777;10%=63/756. This supersedes the earlier62.50target, not a correction to prior arithmetic. Only annual Max140 changes; Plus annual173.33/fixed47500PKR, Max monthly75, sixmonths432 and existing coupon thresholds remain.
- Prevention: guard live SKU/release marker and existing833.33or840price before write, capture product fields for rollback, preserve87other catalog records and historical orders/settings via hashes, update current executable price fixtures without rewriting historical dated acceptance evidence. Explicit term totals, monthly-equivalent labels, conditional discounts and tax-covered copy stay.
- Verification pending: isolated apply/native cart/order checks, restore-checked backup, live price/description/cart/currency/card readback. No live test order/payment/email authorized or needed; synthetic orders only in isolated clone.

- Confirmed local fixture edit error caught in pre-test Git diff: replacing the literal75000as a substring also changed the unrelated PlusPKR4750000cent constant to4756000. Restored the exact Plus assertion before running any test or applying production pricing. Use token-bounded numeric replacements and inspect diffs; never infer unrelated monetary inputs from a substring replacement.

- Final verification for entry38: guarded annual140price840/public840/756/63copy applied idempotently.35isolated native checks plus31current live checks pass.87other catalog records, all3orders/rate/coupons/gateways/tax/registration options preserved. Recovery:max-annual-seventy-20261019T072416Z;58table backup pre-commerce-20261019T072112Z. No real checkout order/payment/email. This completes the70/month pricing change; subsequent owner-control expansion is separately scoped.
