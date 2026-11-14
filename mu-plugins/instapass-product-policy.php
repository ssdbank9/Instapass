<?php
/**
 * Plugin Name: Instapass Product Warranty Disclosure
 * Description: Owner-confirmed 30-day subscription warranty and API credit disclosures.
 * Version: 1.0.0
 */
defined('ABSPATH')||exit;
function ip_product_subscription_warranty(){
 return '<section class="ip-product-warranty" aria-labelledby="ip-subscription-warranty"><h2 id="ip-subscription-warranty">30-day warranty on Claude and ChatGPT subscription accounts</h2><p>Claude Pro, Claude Max 5×, Claude Ultra and ChatGPT Plus include a full 30-day warranty from activation. If access does not work as described during that period, we provide a replacement or refund.</p><p>We activate the selected plan on the account linked to the email you provide. Activation instructions and confirmation are sent by email after payment verification. This warranty covers access faults; it does not extend the purchased subscription term or the expiry of a credit pack. API credit packs follow their displayed balance, permitted uses and expiry.</p></section>';
}
add_filter('the_content',static function($content){
 if(is_admin()||!is_main_query()||!in_the_loop())return $content;
 return is_page('refund-policy')?$content.ip_product_subscription_warranty():$content;
},30);
