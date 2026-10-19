<?php
if(getenv('INSTAPASS_ISOLATED_TEST')==='yes'){add_filter('pre_wp_mail','__return_true',999);add_filter('pre_http_request',fn()=>new WP_Error('isolated','No external HTTP'),999);}
