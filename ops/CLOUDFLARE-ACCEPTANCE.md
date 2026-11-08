# Cloudflare rollout acceptance

Completed 20 October 2026, Asia/Karachi. Site: https://instapass.online. This is acceptance of the Cloudflare DNS, HTTPS and proxy rollout, not full-store launch acceptance or a migration to Astro.

## Live result

Cloudflare Free is active. Namecheap now delegates to megan.ns.cloudflare.com and remy.ns.cloudflare.com, verified after registrar page reload and through public DNS. The root and www website records are proxied to the existing OCI service at 145.241.115.28. Universal SSL is active for instapass.online and its first-level subdomains. Full (strict) validates the origin certificate, and Always Use HTTPS is on. A forced-edge HTTP request returned 301 to the HTTPS root; HTTPS returned 200 with Server:cloudflare and CF-Ray evidence.

The complete authenticated cPanel zone contained 30 business records. It matched 30 authoritative query values. Cloudflare initially contained 27 records and an obsolete root address. Corrected that address and added the missing resend._domainkey TXT plus rsend/send CNAME records. Retained existing MX, SPF, DMARC, cPanel DKIM and service-subdomain values. Mail records remain DNS-only. Root/www were kept DNS-only until the zone and edge certificate became active, then enabled deliberately. Final readback verified 30 records and preserved every staged content value.

No paid plan, persistent API key, new OCI instance or R2 billing/storage change was introduced. Existing Namecheap mail hosting remains in use; its renewal dependency is not removed by changing DNS.

## Origin readiness and recovery

Caddy remains 2.6.2. Its Instapass site block now recognises the 22 officially published Cloudflare IPv4/IPv6 ranges and supplies the verified CF-Connecting-IP as the upstream visitor address only for matching peers. Other connections keep the normal proxy behaviour. This preserves the existing country resolver and prevents direct-origin spoofing of that header. Wasooli's configuration block remained byte-identical; both sites passed HTTPS/title checks after a guarded reload.

Before applying, candidate configuration validated successfully and two isolated localhost tests passed: a trusted stand-in peer uses the Cloudflare visitor address while ignoring supplied XFF, and an untrusted peer cannot spoof it. Temporary proxy/echo processes and their temporary directory were cleaned. The change did not restrict the shared server's global firewall or interrupt Wasooli.

Protected recovery: /opt/instapass/recovery/cloudflare-proxy-20261020T060010Z. Original SHA256: ad959a40d1f93da49969b2c49f394f9311a02f2c0764ac77800a304e756eb6b3. Installed SHA256: 0d077017311194a60f2f16c99a19c9e3bc652d75360cd17c1607e49345c2083b. RESULT.json records validation, isolated checks, source hashes, application and health. The deployment helper restores the prior configuration and reloads if health verification fails.

Official range sources checked during preparation: https://www.cloudflare.com/ips-v4 and https://www.cloudflare.com/ips-v6. The origin could not fetch them directly (403), so the verified public snapshot was relayed and validated. Plan periodic range review and a separately tested Caddy upgrade; no package upgrade was attempted here.

## Test results

| Evidence | Result | Scope |
| --- | --- | --- |
| Complete zone comparison |30 records matched | Authenticated cPanel plus authoritative queries; original Cloudflare snapshot saved |
| Origin proxy tests |2 passed | Trusted visitor address and untrusted spoof rejection; isolated localhost listeners |
| Live origin checkout regression |38 passed | Existing guest/account/coupon flow after server change, before website proxy enablement |
| Forced-edge checkout regression |39 passed | Existing38checks plus proof that every navigation reached Cloudflare; all five account splits, required emails, preserved values, coupon270 USD total, mobile/desktop widths and no-JavaScript fallback |
| Forced-edge public regression |20 passed | Homepage/shop, mobile fit, USD default/manualPKR/GBP/USD, CSS/logo edge HIT, HTML privacy, www canonical redirect, correct/wrong receipt-email tracking, discount270total and activation-address privacy |
| HTTP redirection | Passed |301 to HTTPS from Cloudflare; AlwaysUseHTTPS readback on |
| Site health | Passed | Instapass and Wasooli HTTPS/title checks |
| Business-record preservation | Passed | Final native read-only check: original three order hashes unchanged, four total orders, dummy #143 cancelled/unpaid, no reported/verified payment, coupon usage 0 and stock in stock |
| Actual domain-email tests |2 received | Both orders@ and support@ forwarded to the connected owner Gmail inbox |

Test counts overlap across suites; they are not a unique combined total. Checkout tests blocked order submission. The prior dummy #143 was used only for tracking; no new order, payment, provider account or activation was created. Two labelled internal diagnostic emails were sent once; no customer correspondence was sent.

Single 390px, unthrottled lab sample through the edge: TTFB 465 ms, load 1,439 ms, FCP/LCP 1,564 ms, CLS 0.000156. This is not a real-user 75th-percentile measurement, a Lighthouse score or proof of a universal speed improvement. Mobile homepage screenshot inspected. CSS and logo requests returned Cloudflare cache HIT; personalised HTML retained private/no-store and was not an edge HIT. No blanket HTML cache rule was added.

## Email acceptance

Run reference: CFDNS20261020-dfcd53. Two distinct messages were sent through WordPress to orders@instapass.online and support@instapass.online. Both appeared in instapass0408@gmail.com Inbox. Received From was Instapass <orders@instapass.online>; Reply-To was Instapass <support@instapass.online>. Delivered-To matched the owner Gmail, and SPF, DKIM and DMARC passed for both messages. Sending acceptance was followed by actual inbox/header verification.

This verifies post-cutover incoming alias forwarding and outgoing domain transport. A manual Gmail reply using the support@ sending identity was not performed by this rollout. Real customer restock-alert delivery remains a separate test. Do not rerun the sender simply to inspect results; use the existing unique reference and saved evidence.

## Evidence and continuation

Local ignored evidence under outputs: cloudflare-before-20261020.json; cloudflare-preflight-20261020.json; cpanel-zone-complete-20261020.json; cloudflare-staged-20261020.json; cloudflare-registrar-20261020.json; cloudflare-ssl-20261020.json; cloudflare-https-20261020.json; cloudflare-enabled-20261020.json; cloudflare-dns-after-20261020.json; cloudflare-final-20261020.json; cloudflare-caddy-applied-20261020.json; cloudflare-origin-account-emails-20261020.json; cloudflare-edge-account-emails-live.json; cloudflare-public-20261020.json; cloudflare-email-20261020.json; cloudflare-home-390.png. Transfer necessary sanitized evidence separately from Git. No raw customer email bodies, private order keys or credentials are required.

Source helpers: ops/cloudflare-rollout.cjs, cloudflare-enable-proxy.cjs, cloudflare-caddy-proxy.py, cloudflare-edge-account-emails.cjs, cloudflare-public-check.cjs and cloudflare-mail-test.php. Staging refuses an active zone; do not use it to undo a live rollout. Browser tests pin the verified edge address 104.21.3.9 to avoid stale local DNS; resolve/review that pin before future reruns. Inert server helpers are outside the webroot; no new recurring job was installed. The owner's dedicated Chrome session remains available.

If a proxy issue requires rollback, switch only root/www to DNS-only within Cloudflare and verify direct origin HTTPS. Preserve the corrected 30 records and mail routing. Restore Caddy only from its exact protected original after checking no intervening edits. Registrar rollback to old nameservers has propagation delay; both authoritative zones were kept content-aligned. Never restore an old WordPress database for this infrastructure rollback.

Next performance work: representative throttled mobile benchmarks, asset/font/script budgets, shorter product-card presentation while preserving price/term/coupon disclosures, and measured origin improvements. Public HTML caching needs a designed currency/session strategy and invalidation on owner changes. DNSSEC remains disabled after migration; enabling it with correct registrar DS is a separate post-stability hardening step. Incoming email can be moved away from Namecheap later only after replacement routing and both aliases/manual replies pass tests. Broader fulfilment, refunds, genuine restock delivery and payment acceptance remain open in the main handoff. Astro is an optional later experiment if measured results justify retaining a second frontend; no replatform was made.
