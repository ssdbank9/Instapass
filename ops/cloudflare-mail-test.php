<?php
defined('ABSPATH')||exit;
$run=$args[0]??'';if(!preg_match('/^CFDNS20261020-[a-f0-9]{6}$/D',$run))throw new RuntimeException('Invalid test reference');
$results=[];foreach(['orders','support'] as $alias){$to=$alias.'@instapass.online';$subject='Instapass Cloudflare DNS test '.$run.' '.$alias;$body="TEST ONLY - owner-authorized Cloudflare DNS/email verification.\n\nThis confirms routing for ".$to.".\nReference: ".$run."\n\nNo order, payment or activation was created. No action is required.\n";$results[$alias]=wp_mail($to,$subject,$body,['From: Instapass <orders@instapass.online>','Reply-To: Instapass <support@instapass.online>']);}
echo wp_json_encode(['run'=>$run,'submitted'=>$results],JSON_PRETTY_PRINT)."\n";if(in_array(false,$results,true))throw new RuntimeException('One or more test messages not accepted');
