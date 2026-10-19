$ErrorActionPreference='Stop'
Set-Location -LiteralPath 'C:\Users\Aly Jafferani\Documents\ChatGPT\Website creation'
$a=@('-i','C:\Users\Aly Jafferani\.ssh\rdi-tracker-oracle','-o','BatchMode=yes','-o','ConnectTimeout=15')
function Send-Verified($src,$dest){$h=(Get-FileHash -Algorithm SHA256 -LiteralPath $src).Hash.ToLowerInvariant(); & scp.exe -B @a $src ('ubuntu@145.241.115.28:'+$dest);if($LASTEXITCODE -ne 0){throw 'SCP failed'};$line=& ssh.exe -n -T @a 'ubuntu@145.241.115.28' ('sha256sum '+$dest);if($LASTEXITCODE -ne 0 -or ($line -split '\s+')[0] -ne $h){throw 'Hash mismatch'};Write-Output ('Verified '+$src)}
Send-Verified 'plugins/instapass-tenor-offers/monthly-pricing.php' '/home/ubuntu/owner-pricing-code/tenor/monthly-pricing.php'
Send-Verified 'plugins/instapass-tenor-offers/instapass-tenor-offers.php' '/home/ubuntu/owner-pricing-code/tenor/instapass-tenor-offers.php'
Send-Verified 'ops/patch-owner-pricing-copy.sh' '/home/ubuntu/patch-owner-pricing-copy.sh'
& ssh.exe -n -T @a 'ubuntu@145.241.115.28' 'sudo bash /home/ubuntu/patch-owner-pricing-copy.sh'
if($LASTEXITCODE -ne 0){throw 'Copy patch failed'}
