<?php
$tests=array();$check=function($n,$v)use(&$tests){if(!$v){throw new Exception($n);}$tests[]=$n;};
$a=apply_filters('wp_mail',array('to'=>'fixture@example.invalid','subject'=>'fixture','message'=>'fixture','headers'=>''));
$check('generic website mail has support reply-to',in_array('Reply-To: support@instapass.online',$a['headers'],true));
$b=apply_filters('wp_mail',array('to'=>'fixture@example.invalid','subject'=>'fixture','message'=>'fixture','headers'=>array('Content-Type: text/html','Reply-To: support@instapass.online')));
$check('explicit reply-to is preserved without duplication',count(array_filter($b['headers'],fn($h)=>stripos($h,'Reply-To:')===0))===1);
echo wp_json_encode(array('pass_count'=>count($tests),'checks'=>$tests),JSON_PRETTY_PRINT);
