<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')return;
add_filter('pre_wp_mail','__return_true',999);
add_filter('pre_http_request',static fn($pre)=>$pre!==false?$pre:new WP_Error('isolated','External requests disabled'),999);
