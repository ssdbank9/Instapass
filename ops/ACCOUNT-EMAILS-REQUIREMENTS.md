# Account count and activation emails — 2026-10-19

User requirement: choosing more destination accounts must provide an activation email input for every selected account.

Current source already has one email per pre-rendered account, but custom mode exposes every possible row and has no explicit account-count control. Improve discoverability with a Number of accounts selector, show exactly that many active rows, and require one distinct valid email per account when more than one is selected. Preserve entered addresses when increasing/decreasing the count; hidden rows are disabled and never submitted. Automatically propose a balanced distribution of purchased units, allow manual3+1etc, and require the unit sum and active-account count to match on the server. A single-account blank email continues to use the checkout activation/receipt fallback. No passwords, mandatory site account, price, coupon or historical-order changes.

Verify1/2/3/4accounts, increasing and decreasing, email retention, disabled hidden rows, missing/invalid/duplicate emails, mismatched/malformed count, every partition of four units, native order persistence/private tracking, mobile, no-JavaScript form, checkout updates, live anonymous cart without real order. Guard source/commercial hashes and clean isolated resources.
