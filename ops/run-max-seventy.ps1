param([ValidateSet('test','backup','apply')][string]$Action='test')
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
 foreach($f in @('set-max-annual-seventy.php','max-seventy-integration.php','test-max-seventy-isolated.sh','monthly-pricing-integration.php')){Send-Verified ('ops/'+$f) ('/home/ubuntu/'+$f)}
 Run-Remote 'sudo bash /home/ubuntu/test-max-seventy-isolated.sh'
}elseif($Action -eq 'backup'){
 Send-Verified 'ops/backup-restore-check.sh' '/home/ubuntu/backup-restore-check.sh'
 Run-Remote 'sudo bash /home/ubuntu/backup-restore-check.sh'
}else{
 foreach($f in @('set-max-annual-seventy.php','apply-max-annual-seventy.sh')){Send-Verified ('ops/'+$f) ('/home/ubuntu/'+$f)}
 Run-Remote 'sudo bash /home/ubuntu/apply-max-annual-seventy.sh'
}
