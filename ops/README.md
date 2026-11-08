# Instapass operations — first installment

Target: existing rdi-tracker at 145.241.115.28. WordPress is /opt/instapass; Wasooli is separate and must remain running. Scripts contain no passwords. Never publish migration archives, .env, wp-config.php or private order previews.

## Recovery evidence

The 2026-10-18 baseline backup is /opt/instapass/recovery/pre-commerce-20261018T062204Z. It contains database.sql, wordpress.tar.gz, config.tar.gz and SHA256SUMS. Directory and files are root-only. SQL restored successfully into a network-isolated temporary MariaDB with no published ports; 56 application tables and integrity checks passed. File and config archives passed listing/hash checks. A second copy of all three archives was downloaded to the owner PC and each local SHA-256 hash verified against the server manifest. Full application restoration and routine scheduled/offsite backup policies remain pending.

backup-restore-check.sh takes a new backup and performs the same isolated database restore using the already-installed MariaDB image, tmpfs and a 768 MB memory cap. It does not run cloned WordPress or send email. It stops only its own explicitly named restore container. It does not alter or stop live database/WordPress containers. Run through sudo on the target; shell commands exit on failure. Check exit status and RESULT.txt; hashes alone are not a restore test.

Do not follow Docker Compose's orphan-container warning with --remove-orphans: the maintenance project intentionally refers to the existing live containers.

## Control Center deployment and rollback

Source: plugins/instapass-control-center. deploy-control-center.sh is the initial-install helper, guarded against overwriting an existing directory. Upload reviewed code to /home/ubuntu/instapass-control-center, lint, and run the helper only after tests. Updates need a separately reviewed atomic replacement; do not rerun the initial-install helper as an updater.

Deactivate only this plugin to roll back installment 1:

```
sudo docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm wpcli plugin deactivate instapass-control-center
```

This release creates no custom database tables/options and changes no storefront/cart/order hooks. It adds an owner admin menu and preview only.

## Acceptance limits

Actual WordPress integration runs .qa/control-center-integration.php through a read-only bind mount. It uses current-user context in that CLI process to test owner/anonymous access; it neither creates a login session nor changes an account. Browser layout tests render the resulting HTML locally, and do not prove authenticated wp-admin interaction. Live anonymous access correctly redirects to login. Test actual signed-in owner navigation before calling that browser acceptance complete.

commerce-baseline.php reports counts and configuration booleans, not credentials, receiving account details or customer rows. Currency switching, geolocation, checkout integration, scheduled backup policies and verified email remain pending.

## Version 0.2.0 GBP preparation

update-control-center-020.sh guards the existing version, archives the plugin, installs/lints the new independent GBP includes, then atomically replaces the main PHP entry point with a fresh timestamp. It does not update customer records or product prices. Rollback can deactivate the plugin or restore the recorded archive; saved GBP product metadata must be retained.

Isolated tests: test-gbp-isolated.sh restores a fresh database clone into a temporary MariaDB, creates an internal Docker network with no external route or published ports, and runs only WP-CLI against read-only production WordPress files. It uses a disposable draft product in the clone, verifies GBP persistence/guards/USD preservation, deletes that fixture and removes its own containers/network. It never writes to the live database. Root-only clone and result artifacts remain under /opt/instapass/recovery/gbp-test-<UTC timestamp>. audit-gbp-editor.php renders fields read-only against live WordPress without saving products.

## Version 0.3.0 currency release

update-control-center-030.sh backs up plugin 0.2.0 and records the old rate option, installs/lints compatible includes/CSS, then replaces the main entry point with a fresh mtime. Current rate is saved as 280.00. The old rate was 0 (legacy estimate disabled). Plugin rollback archive: /opt/instapass/recovery/control-center-before-030-20261018T071145Z.tar.gz. Preserve all newer orders/GBP metadata; restoring old code may also require restoring the old estimate setting. Never replace the live DB merely to undo code.

Currency integration tests use --skip-plugins=instapass-control-center when booting the isolated clone, then load the reviewed source at /tmp/control-next. Require INSTAPASS_ISOLATED_TEST=yes. Guest fixture orders/coupons are written only in the clone, with outbound network blocked and no published ports. Current theme replaces its stored checkout block with classic checkout; validate runtime behavior instead of classifying solely from page markup.

Country default verification: verify-uk-currency.py sends a loopback request with a known UK IP through the local WordPress listener and checks GBP/default/USD link. This tests country handling/rendering, not physical UK traffic. If Cloudflare proxy is enabled later, configure trusted proxies/client-IP forwarding and honor private/no-store currency responses.


## Version 0.4.0 bulk-pricing release

update-control-center-040.sh is the one-time 0.3.0 → 0.4.0 updater, guarded by source fingerprints and matching unchanged currency modules. It archives the current plugin, installs additive bulk files, lints and atomically replaces the main entry point, then compares catalog/order fingerprints. The live page uses unique slug instapass-control-pricing, preserving the theme's existing pricing/stock screen.

Full backup: /opt/instapass/recovery/pre-commerce-20261018T085637Z. Plugin rollback: /opt/instapass/recovery/control-center-040-20261018T085702Z/control-center-before-040.tar.gz. Restore only plugin code to roll back; retain all newer orders, GBP metadata and rate/audit options. Never restore an old database solely to undo this code installation.

update-control-center-040-ui.sh publishes the small status-clearing follow-up using UI asset build 0.4.0.1. Its source-fingerprint guard intentionally accepts only the first verified 0.4.0 code state; neither updater should be blindly rerun. The final plugin version remains 0.4.0.

See outputs/CONTROL-CENTER-0.4.0-VERIFICATION.md and control-center-040-verified-result.json for validation and rollback hashes. No live price batch was applied during acceptance.
