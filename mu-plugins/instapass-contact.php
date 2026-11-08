<?php
/**
 * Plugin Name: Instapass Current Public Contacts
 * Description: Keep owner-approved public email and Telegram contacts consistent in rendered saved templates.
 * Version: 1.0.1
 */
if(!defined('ABSPATH'))exit;
$instapass_contact_render=static function($html){
    return str_replace(
        ['https://t.me/AJNF48','http://t.me/AJNF48','@AJNF48','ajnf0408@gmail.com','instapass0408@gmail.com'],
        ['https://t.me/Instapass0408','https://t.me/Instapass0408','@Instapass0408','support@instapass.online','support@instapass.online'],
        $html
    );
};
add_filter('render_block',$instapass_contact_render,110);
add_filter('the_content',$instapass_contact_render,110);
