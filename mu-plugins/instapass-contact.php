<?php
/**
 * Plugin Name: Instapass Current Telegram Contact
 * Description: Keep the owner-approved Telegram handle consistent in rendered saved templates.
 * Version: 1.0.0
 */
if(!defined('ABSPATH'))exit;
$instapass_contact_render=static function($html){
    return str_replace(
        ['https://t.me/AJNF48','http://t.me/AJNF48','@AJNF48'],
        ['https://t.me/Instapass0408','https://t.me/Instapass0408','@Instapass0408'],
        $html
    );
};
add_filter('render_block',$instapass_contact_render,110);
add_filter('the_content',$instapass_contact_render,110);
