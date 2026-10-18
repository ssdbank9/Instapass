<?php
if(!defined('WP_CLI')||!WP_CLI)throw new RuntimeException('CLI only');
$s=get_option('fluentmail-settings',[]);$out=[];
$walk=function($value,$path='')use(&$walk,&$out){if(!is_array($value))return;foreach($value as $k=>$v){$key=is_string($k)&&strpos($k,'@')!==false?'[mailbox]':(string)$k;$p=$path.'.'.$key;if(is_array($v)){$out[]=['path'=>$p,'type'=>'array','count'=>count($v)];$walk($v,$p);}elseif(in_array((string)$k,['provider','mailer','host','smtp_host','server','default_connection','connection_type'],true)){$text=is_scalar($v)?(string)$v:'unset';if(strpos($text,'@')!==false)$text='[configured mailbox]';$out[]=['path'=>$p,'value'=>$text];}elseif(preg_match('/password|api_key|secret|token|username/i',(string)$k))$out[]=['path'=>$p,'credential_present'=>!empty($v)];}};
$walk(is_array($s)?$s:[]);echo wp_json_encode(['scope'=>'structure/provider identifiers and credential-presence booleans only','configuration'=>$out],JSON_PRETTY_PRINT);
