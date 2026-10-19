$ErrorActionPreference='Stop'
Set-Location -LiteralPath 'C:\Users\Aly Jafferani\Documents\ChatGPT\Website creation'
$a=@('-i','C:\Users\Aly Jafferani\.ssh\rdi-tracker-oracle','-o','BatchMode=yes','-o','ConnectTimeout=15')
& scp.exe -B @a 'ops/owner-pricing-live-smoke.php' 'ubuntu@145.241.115.28:/home/ubuntu/owner-pricing-live-smoke.php';if($LASTEXITCODE -ne 0){throw 'Smoke transfer failed'}
$line=& ssh.exe -n -T @a 'ubuntu@145.241.115.28' 'sha256sum /home/ubuntu/owner-pricing-live-smoke.php'
if($LASTEXITCODE -ne 0 -or ($line -split '\s+')[0] -ne (Get-FileHash -Algorithm SHA256 'ops/owner-pricing-live-smoke.php').Hash.ToLowerInvariant()){throw 'Smoke hash mismatch'}
& ssh.exe -n -T @a 'ubuntu@145.241.115.28' 'sudo docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/owner-pricing-live-smoke.php:/tmp/smoke.php:ro wpcli eval-file /tmp/smoke.php'
if($LASTEXITCODE -ne 0){throw 'Live owner readback failed'}
