<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')throw new RuntimeException('Isolated clone required');
if(!function_exists('ip_tenor_refresh_generated_copy'))require '/var/www/html/wp-content/plugins/instapass-tenor-offers/instapass-tenor-offers.php';
add_filter('pre_wp_mail','__return_true',999);add_filter('pre_http_request',fn()=>new WP_Error('isolated','No external HTTP'),999);
$checks=[];$c=function($name,$ok)use(&$checks){if(!$ok)throw new RuntimeException($name);$checks[]=$name;};
$GLOBALS['ip_cc_test_front']=true;$GLOBALS['ip_cc_test_currency']='USD';
foreach([138,139,140] as $id){$p=wc_get_product($id);$old=$p->get_short_description();$p->set_price('1200.00');$html=ip_tenor_refresh_generated_copy($old,$p);$c('generated copy follows edited price '.$id,strpos($html,'1,200.00')!==false&&strpos($html,'USD full-term total:')===false);$c('generated copy uses upfront terms '.$id,strpos($html,'No automatic monthly charge')!==false);$custom='<p>My own price explanation.</p>';$c('owner-written copy preserved '.$id,ip_tenor_refresh_generated_copy($custom,$p)===$custom);}
$p=wc_get_product(140);$GLOBALS['ip_cc_test_currency']='GBP';$html=ip_tenor_refresh_generated_copy($p->get_short_description(),$p);$c('missing GBP copy has no stale USD amount',strpos($html,'840')===false&&strpos($html,'Choose a priced currency')!==false);
$GLOBALS['ip_cc_test_currency']='PKR';$p->update_meta_data('_instapass_pkr_regular_price','48000.00');$html=ip_tenor_refresh_generated_copy($p->get_short_description(),$p);$c('generated copy uses owner PKR override',strpos($html,'48,000')!==false&&strpos($html,'840')===false);
$p->update_meta_data('_instapass_price_release','other');$c('unrelated product copy untouched',ip_tenor_refresh_generated_copy('<p>One year of access, paid upfront. USD full-term total: $840.</p>',$p)==='<p>One year of access, paid upfront. USD full-term total: $840.</p>');
$p=wc_get_product(140);$GLOBALS['ip_cc_test_currency']='USD';$p->set_price('1200.00');$old='<p class="wp-block-post-excerpt__excerpt">One year of access, paid upfront. USD full-term total: $840. Original note. </p>';$html=ip_tenor_refresh_generated_copy($old,$p);
$c('attributed excerpt paragraph refreshed',strpos($html,'1,200.00')!==false&&strpos($html,'USD full-term total:')===false);
$c('excerpt class preserved',strpos($html,'class="wp-block-post-excerpt__excerpt"')!==false);
echo wp_json_encode(['count'=>count($checks),'checks'=>$checks,'scope'=>'isolated presentation helpers; no saved product changes'],JSON_PRETTY_PRINT);
