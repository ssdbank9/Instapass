$ErrorActionPreference='Stop'
$root='C:\Users\Aly Jafferani\Documents\ChatGPT\Website creation'
Set-Location -LiteralPath $root
$key='C:\Users\Aly Jafferani\.ssh\rdi-tracker-oracle'
$sshArgs=@('-i',$key,'-o','BatchMode=yes','-o','ConnectTimeout=15','-o','ServerAliveInterval=10','-o','ServerAliveCountMax=2')
function Send-Verified([string]$source,[string]$destination){
 $localHash=(Get-FileHash -Algorithm SHA256 -LiteralPath $source).Hash.ToLowerInvariant()
 $sent=$false
 for($attempt=1;$attempt -le 2;$attempt++){
  & scp.exe @sshArgs $source ('ubuntu@145.241.115.28:'+$destination)
  if($LASTEXITCODE -ne 0){Write-Output ('Transfer retry '+$source);continue}
  $remoteLine=& ssh.exe -n -T @sshArgs 'ubuntu@145.241.115.28' ('sha256sum '+$destination)
  if($LASTEXITCODE -eq 0 -and (($remoteLine -split '\s+')[0]) -eq $localHash){$sent=$true;break}
 }
 if(!$sent){throw ('Upload/hash verification failed: '+$source)}
 Write-Output ('Verified '+$source)
}
try {
 Send-Verified 'theme-v1.9.0/instapass/inc/promotions.php' '/home/ubuntu/bundle-promotions.php'
 Send-Verified 'plugins/instapass-guest-orders/instapass-guest-orders.php' '/home/ubuntu/bundle-guest-orders.php'
 Send-Verified 'plugins/instapass-tenor-offers/instapass-tenor-offers.php' '/home/ubuntu/instapass-tenor-offers/instapass-tenor-offers.php'
 foreach($file in @('configure-three-product-offer.php','bundle-offers-integration.php','test-bundle-offers-isolated.sh','bundle-offers-configure.php','bundle-offers-fixture.php','cleanup-bundle-fixture.sh')){Send-Verified ('ops/'+$file) ('/home/ubuntu/'+$file)}
 Write-Output 'All 9 staging uploads match local SHA256 hashes.'
 & ssh.exe -n -T @sshArgs 'ubuntu@145.241.115.28' 'sudo bash /home/ubuntu/cleanup-bundle-fixture.sh && sudo bash /home/ubuntu/test-bundle-offers-isolated.sh'
 if($LASTEXITCODE -ne 0){throw 'Isolated test failed'}
 Write-Output 'THREE-OFFER TEST PIPELINE COMPLETE'
 exit 0
}catch{Write-Output ('FAILED: '+$_.Exception.Message);exit 1}
