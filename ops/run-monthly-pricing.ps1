param([ValidateSet('test','backup','deploy','wrap-fix')][string]$Action='test')
$ErrorActionPreference='Stop'
Set-Location -LiteralPath 'C:\Users\Aly Jafferani\Documents\ChatGPT\Website creation'
$a=@('-i','C:\Users\Aly Jafferani\.ssh\rdi-tracker-oracle','-o','BatchMode=yes','-o','ConnectTimeout=15','-o','ServerAliveInterval=10','-o','ServerAliveCountMax=2')
function Run-Remote([string]$cmd){& ssh.exe -n -T @a 'ubuntu@145.241.115.28' $cmd;if($LASTEXITCODE -ne 0){throw ('Remote failed: '+$cmd)}}
function Send-Verified([string]$src,[string]$dest){
 $h=(Get-FileHash -Algorithm SHA256 -LiteralPath $src).Hash.ToLowerInvariant()
 & scp.exe -B @a $src ('ubuntu@145.241.115.28:'+$dest);if($LASTEXITCODE -ne 0){throw ('Transfer failed '+$src)}
 $line=& ssh.exe -n -T @a 'ubuntu@145.241.115.28' ('sha256sum '+$dest)
 if($LASTEXITCODE -ne 0 -or (($line -split '\s+')[0]) -ne $h){throw ('Hash mismatch '+$src)}
 Write-Output ('Verified '+$src)
}
if($Action -eq 'test'){
 Run-Remote 'mkdir -p /home/ubuntu/monthly-tenor-code'
 foreach($f in @('instapass-tenor-offers.php','monthly-pricing.php','tenor-offers.css')){Send-Verified ('plugins/instapass-tenor-offers/'+$f) ('/home/ubuntu/monthly-tenor-code/'+$f)}
 foreach($f in @('monthly-pricing-integration.php','test-monthly-pricing-isolated.sh')){Send-Verified ('ops/'+$f) ('/home/ubuntu/'+$f)}
 Run-Remote 'sudo bash /home/ubuntu/test-monthly-pricing-isolated.sh'
}elseif($Action -eq 'backup'){
 Send-Verified 'ops/backup-restore-check.sh' '/home/ubuntu/backup-restore-check.sh'
 Run-Remote 'sudo bash /home/ubuntu/backup-restore-check.sh'
}elseif($Action -eq 'wrap-fix'){
 foreach($f in @('instapass-tenor-offers.php','tenor-offers.css')){Send-Verified ('plugins/instapass-tenor-offers/'+$f) ('/home/ubuntu/monthly-tenor-code/'+$f)}
 Send-Verified 'ops/fix-monthly-card-wrap.sh' '/home/ubuntu/fix-monthly-card-wrap.sh'
 Run-Remote 'sudo bash /home/ubuntu/fix-monthly-card-wrap.sh'
}else{
 foreach($f in @('monthly-price-preflight.php','audit-guest-commerce.php','deploy-monthly-pricing.sh')){Send-Verified ('ops/'+$f) ('/home/ubuntu/'+$f)}
 Run-Remote 'sudo bash /home/ubuntu/deploy-monthly-pricing.sh'
}
