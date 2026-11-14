# Product activation, API credits and30-day warranty — 9November2026

## Owner-confirmed scope

The owner confirmed that Instapass activates Claude/ChatGPT subscriptions on the account associated with the customer's supplied email, customers have access to export tools, and these account offers carry a full30-day warranty. Four families now show four checklist items: supplied-email activation, private access/confirmation, provider chat-export tools and30-day replacement/refund warranty from activation for access not working as described. A matching disclosure is appended to the Refund Policy. It does not extend a purchased term or credit expiry. Canva/Cursor do not gain an unrequested30-day promise.

The owner clarified that Claude credits are monthly API credits, supporting API, Playground, Agent SDK and Managed Agents, then explicitly added Batch API. The official name used is Message Batches API. Customer copy lists all five and identifies the linked Console organization/API-key context. Official documentation excludes Claude Code and extra Claude app usage for this monthly benefit. The owner's proposed `claude -p` exception was researched but not verified and is not advertised; direct Agent SDK support is documented. No upstream paid API request or CLI eligibility test was attempted.

OpenAI credits are named OpenAI API Credits, with supported API Platform usage, API-based agents, SDK integrations, API Playground and supported Batch API workloads. API credits remain distinct from ChatGPT subscription limits. The Codex exclusion is expressly qualified as applying to monthly subscription-included credits, not every purchased API balance. SDK coverage refers to eligible API calls billed to the linked API organization; it does not make every SDK/tool/service eligible.

## Live changes and owner control

- Group names/captions/checklists updated for six approved families. Credit product names and public copy updated across38existing credit offers. Existing SKUs, slugs, group keys and order snapshots retained.
- No changes to prices, sale amounts, stock, durations/expiry, discounts, payment settings or orders. Native guards hash every other raw product field, all orders and selected commercial options; only approved credit text fields and their modification timestamps are excluded. Group settings are compared against an exact approved transform.
- Grouped catalog1.4.1 adds a plain-text What you get textarea to each family in Instapass → Product tiles & durations (`admin.php?page=instapass-product-tiles`). Up to8items/500bytes each; existing capability, nonce and stale-fingerprint guards remain. Native checks validate normalization, invalid inputs, tag stripping and all8guarded fields rendering. Existing fallback copy remains for unmodified families.
- New additive instapass-product-policy MU helper renders the matching30-day disclosure on the Refund Policy. Existing change-of-mind exclusions are not reintroduced.
- Visual inspection identified a pre-existing literal question-mark checklist marker. Replaced it with ASCII CSS escape `\2713` so checkmarks render reliably.

## Verification and recovery

66native checks pass, including all approved names/checklists, full protected product/order/settings hashes and owner-field guards. Initial298Chrome browser assertions across USD/GBP/EUR/PKR pass; the final run after Batch/SDK wording and checkmark correction passes326assertions, recorded in outputs/product-copy-browser.json. Browser checks cover all four account families/all three durations, both credit names/logos/ranges/expiry distinctions, four-item checklists/checkmark rendering, desktop/mobile widths and the warranty disclosure. Screenshot product-copy-max-benefits.png visually rechecked after the glyph fix. No order, payment, email, provider activation, export or refund was executed. Provider-side account functionality is not an end-to-end acceptance claim.

Recovery:/opt/instapass/recovery/product-copy-20261109T145822Z (protected original catalog option, original credit copy, source backup, native results and style-before.css). Guarded main release:product-copy-release.py; native transforms/audits:product-copy-data.php; narrow API-copy follow-up:product-copy-refresh.py; final read-only audit/style patch:product-copy-final.py. Do not rerun one-time releases blindly. Preserve new owner edits/orders; roll back only the recorded text/settings/source fields, never an old full database.

Sources fetched9November2026:

- https://platform.claude.com/docs/en/about-claude/api-credits-for-subscribers — monthly permitted uses, Message Batches recommendation and explicit Claude Code exclusion.
- https://support.claude.com/en/articles/8977456-how-do-i-pay-for-my-claude-api-usage — distinguishes purchased prepaid credits from monthly included credits.
- https://support.claude.com/en/articles/9450526-export-your-claude-data — individual-account chat export; not all original uploads or cross-account import.
- https://developers.openai.com/api/docs/guides/chatgpt-api-credits — API Platform coverage, linked organization and monthly-credit Codex exclusion.
- https://developers.openai.com/api/docs/guides/batch — supported API Batch workloads.

Data-export copy is bounded to the provider's own tools and the owner's reported supplied-account capability. No specific ChatGPT export menu path or guarantee of whole-account migration is added. Searches of the restricted OpenAI Docs skill domains did not yield a dedicated ChatGPT account-export guide; Claude's guide was fetched. This does not represent an upstream export test.

Changes in this installment remain uncommitted; earlier unrelated working-tree changes are preserved. No GitHub push or unrelated deployment was performed.
