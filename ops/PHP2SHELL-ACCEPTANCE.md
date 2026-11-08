# PHP2Shell remediation — 2026-10-26

Scope: CVE-2026-109618 and CVE-2026-109619 on the Instapass OCI 4/24 host, `145.241.115.28`. Owner requested patching the Oracle alert. This record concerns that host and these vulnerabilities, not all OCI account resources or a forensic investigation.

## Finding and remediation

Live WordPress used PHP 8.3.33 inside its pinned Docker image. The host is Ubuntu 24.04; inventory did not identify a host PHP interpreter. PHP upstream identifies 8.3.36 as the fixed 8.3 release. Host package updates alone would not replace container PHP.

- Production WordPress container replaced with official image `wordpress@sha256:d42f63cb89ad0eab272c4b393164e63830d0df270009907919f7dcce52b1a20b`, downloaded as `wordpress:7.1.3-php8.3-apache`; PHP now 8.3.36. Existing WordPress remains 7.1.3.
- `/opt/instapass/compose.yaml` persists that immutable image and the security INI mount. Database image/volume, WordPress files mount, ports and all other resolved Compose settings were compared and preserved.
- Maintenance CLI pinned to official `wordpress@sha256:661db880e953da051abcac6c0a769767b995a82cd52101c003ca7438dd427d7d` in `/opt/instapass/maintenance.yaml`; its PHP binary independently verified as 8.3.36.
- `/opt/instapass/php-security.ini` contains `session.upload_progress.enabled=0`, read-only mounted into both runtimes. This follows PHP's October 23 additional upload-progress advice. File uploads remain available; this disables PHP's session-based upload-progress bookkeeping.
- Production WordPress container was recreated, replacing its workers. Old isolated fixture `instapass-guest-web` was stopped, preserving its container/database for review while ending the old PHP workers. No host reboot or Wasooli restart.

## Verification

| Check | Result |
| --- | --- |
| Candidate Apache HTTP probe | PHP 8.3.36, SAPI apache2handler, upload progress 0 |
| Network-isolated candidate site | Homepage, shop, tracking, login, ChatGPT checkout render successfully |
| Patched CLI + native WooCommerce country checks | Required field, missing/invalid rejection, valid code, order metadata, email metadata and non-AI exclusion pass; 10 qualifying fixture offers found |
| Production runtime re-inventory | Running image digest matches new pin; WordPress 7.1.3, PHP 8.3.36, upload progress 0; security mount read-only |
| Production commerce audit before/after | JSON-equivalent catalog, orders, coupon, currency, gateways and tracking-page inventory |
| Public HTTPS health | Instapass homepage, shop, tracking and Wasooli /healthz pass |
| Public mobile browser | All 16 existing access-safety/checkout checks pass; no order submitted |
| Temporary candidate web/probe | Removed; probe was accessible only on an internal test network, never on the public site |

No live order, payment, email, customer account, catalog price or stock change was submitted by the patch procedure. Database dump and configuration backups are protected on the server; no credentials or customer export was transferred into the repository.

## Recovery and future maintenance

Recovery folder: `/opt/instapass/recovery/php2shell-20261026T055703Z`. Contains exact original Compose files, protected pre-patch database export, commerce audits and acceptance JSON. The prior image stays cached for emergency compatibility recovery. Restoring that image would reintroduce these vulnerabilities; do not treat it as a secure operating state. A runtime rollback does not require restoring the database.

Historical test/deployment scripts may contain older pinned PHP image digests. Review and update their runtime pins before reuse; current production Compose and maintenance configuration are patched. The remediation helper intentionally refuses an already-changed live image and is not a recurring updater.

First isolated checkout probe omitted redirects/cookies and failed before production writes; corrected probe passed. Local py_compile failed to create __pycache__ under workspace restrictions; write-free AST validation passed.

Primary sources: [Oracle alert](https://www.oracle.com/security-alerts/alert-cve-2026-109618.html), [PHP October 21 security release and October 23 upload-progress advice](https://www.php.net/archive/2026.php#2026-10-21-5). This is version/configuration and application health verification, not evidence that the host was or was not previously compromised.
