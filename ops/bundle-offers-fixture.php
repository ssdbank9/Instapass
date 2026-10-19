<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes'){return;}
add_filter('pre_wp_mail','__return_true',999);
