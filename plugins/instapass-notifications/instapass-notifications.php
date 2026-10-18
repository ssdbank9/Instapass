<?php
/**
 * Plugin Name: Instapass Email & Stock Alerts
 * Description: Confirmed guest restock alerts and owner email controls.
 * Version: 0.1.0
 * Requires Plugins: woocommerce
 */
if (!defined('ABSPATH')) exit;
final class Instapass_Notifications {
    const OPTION='instapass_notifications';
    const GROUP='instapass-notifications';
    static $rendered=[];
    static function table(){global $wpdb;return $wpdb->prefix.'instapass_stock_alerts';}
    static function settings(){return wp_parse_args(get_option(self::OPTION,[]),['enabled'=>0,'verified'=>'','daily_limit'=>100]);}
    static function sender(){
        $cfg=get_option('fluentmail-settings',[]);
        $id=$cfg['misc']['default_connection']??'';
        $connection=$cfg['connections'][$id]??[];
        // Refreshing OAuth tokens must not invalidate the owner's inbox confirmation.
        $provider=$connection['provider_settings']??[];
        foreach(['access_token','refresh_token','expires_in','created_at','token_type'] as $key)unset($provider[$key]);
        return hash_hmac('sha256',wp_json_encode([$id,$connection['sender_settings']??[],$provider,get_option('admin_email')]),wp_salt('auth'));
    }
    static function ready(){ $s=self::settings();return !empty($s['enabled']) && hash_equals(self::sender(),(string)$s['verified']);}
    static function install(){
        global $wpdb;
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        $table=self::table();$charset=$wpdb->get_charset_collate();
        dbDelta("CREATE TABLE $table (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            product_id bigint unsigned NOT NULL,
            email varchar(254) NOT NULL,
            email_hash varchar(64) NOT NULL,
            seed varchar(64) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at bigint unsigned NOT NULL,
            expires_at bigint unsigned NOT NULL,
            confirmed_at bigint unsigned NOT NULL DEFAULT 0,
            submitted_at bigint unsigned NOT NULL DEFAULT 0,
            confirm_sent_at bigint unsigned NOT NULL DEFAULT 0,
            attempts int unsigned NOT NULL DEFAULT 0,
            lock_token varchar(64) NOT NULL DEFAULT '',
            locked_at bigint unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY product_email (product_id,email_hash),
            KEY status (status)
        ) ENGINE=InnoDB $charset;");
        dbDelta("CREATE TABLE {$wpdb->prefix}instapass_mail_budget (
            day varchar(10) NOT NULL,
            used int unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY  (day)
        ) ENGINE=InnoDB $charset;");
        if(!get_option(self::OPTION))add_option(self::OPTION,['enabled'=>0,'verified'=>'','daily_limit'=>100],'','no');
    }
    static function init(){
        add_action('admin_menu',[__CLASS__,'menu'],30);
        add_action('admin_post_instapass_notifications',[__CLASS__,'admin_post']);
        add_action('admin_post_instapass_stock_signup',[__CLASS__,'signup']);
        add_action('admin_post_nopriv_instapass_stock_signup',[__CLASS__,'signup']);
        add_action('template_redirect',[__CLASS__,'token_page']);
        add_action('woocommerce_product_options_inventory_product_data',[__CLASS__,'fields']);
        add_action('woocommerce_admin_process_product_object',[__CLASS__,'save_fields']);
        add_action('woocommerce_update_product',[__CLASS__,'restocked']);
        add_action('woocommerce_product_set_stock_status',function($id){self::restocked($id);});
        add_action('instapass_notification_job',[__CLASS__,'job'],10,3);
        add_action('woocommerce_single_product_summary',[__CLASS__,'legacy_form'],35);
        add_filter('render_block_woocommerce/add-to-cart-form',function($html,$parsed,$block){
            global $product;
            $p=function_exists('instapass_block_product')?instapass_block_product($block):$product;
            return $html.self::form($p);
        },30,3);
        add_filter('wp_privacy_personal_data_exporters',[__CLASS__,'exporter']);
        add_filter('wp_privacy_personal_data_erasers',[__CLASS__,'eraser']);
    }
    static function eligible($id){
        $p=wc_get_product($id);
        return $p && $p->is_type('simple') && $p->get_status()==='publish' && $p->is_in_stock() && (float)$p->get_price('edit')>0;
    }
    static function schedule($id,$kind,$delay=1){
        if(!function_exists('as_schedule_single_action'))return;
        as_schedule_single_action(time()+$delay,'instapass_notification_job',[(int)$id,$kind,wp_generate_uuid4()],self::GROUP);
    }
    static function token($r,$purpose){return hash_hmac('sha256',$purpose.':'.$r->id.':'.$r->seed,wp_salt('auth'));}
    static function url($r,$purpose){
        return add_query_arg(['instapass_alert'=>$purpose,'alert_id'=>$r->id,'token'=>self::token($r,$purpose)],home_url('/'));
    }
    static function fields(){
        global $product_object;
        if(!$product_object || !$product_object->is_type('simple') || !current_user_can('manage_woocommerce'))return;
        echo '<div class="options_group show_if_simple">';
        woocommerce_wp_text_input(['id'=>'_instapass_restock_date','label'=>'Expected restock date','type'=>'date','value'=>$product_object->get_meta('_instapass_restock_date'),'description'=>'An estimate shown while out of stock. Actual stock controls the alert.']);
        woocommerce_wp_text_input(['id'=>'_instapass_restock_note','label'=>'Restock message','value'=>$product_object->get_meta('_instapass_restock_note'),'custom_attributes'=>['maxlength'=>180]]);
        wp_nonce_field('instapass_restock_'.$product_object->get_id(),'instapass_restock_nonce',false);
        echo '<input type="hidden" name="instapass_restock_fingerprint" value="'.esc_attr(self::fingerprint($product_object)).'"></div>';
    }
    static function fingerprint($p){return hash('sha256',wp_json_encode([$p->get_meta('_instapass_restock_date'),$p->get_meta('_instapass_restock_note')]));}
    static function save_fields($p){
        if(!isset($_POST['instapass_restock_nonce']))return;
        $nonce=self::input('instapass_restock_nonce');$fresh=wc_get_product($p->get_id());
        if(!$p->is_type('simple') || !current_user_can('manage_woocommerce') || !current_user_can('edit_post',$p->get_id()) || !wp_verify_nonce($nonce,'instapass_restock_'.$p->get_id()) || !$fresh || !hash_equals(self::fingerprint($fresh),self::input('instapass_restock_fingerprint'))){
            WC_Admin_Meta_Boxes::add_error('Restock details were not saved. Reload the editor and try again.');return;
        }
        $date=self::input('_instapass_restock_date');$note=sanitize_text_field(self::input('_instapass_restock_note'));
        if($date!=='' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$date) || !checkdate((int)substr($date,5,2),(int)substr($date,8,2),(int)substr($date,0,4)))){
            WC_Admin_Meta_Boxes::add_error('Restock date is invalid; details were not saved.');return;
        }
        if(mb_strlen($note)>180){WC_Admin_Meta_Boxes::add_error('Restock message must be at most 180 characters.');return;}
        $p->update_meta_data('_instapass_restock_date',$date);$p->update_meta_data('_instapass_restock_note',$note);
    }
    static function input($key){return isset($_POST[$key])&&is_string($_POST[$key])?wp_unslash($_POST[$key]):'';}
    static function legacy_form(){global $product;echo self::form($product);}
    static function form($p){
        if(!$p || !$p->is_type('simple') || $p->get_status()!=='publish' || $p->is_in_stock() || isset(self::$rendered[$p->get_id()]))return '';
        self::$rendered[$p->get_id()]=true;
        $date=$p->get_meta('_instapass_restock_date');$note=$p->get_meta('_instapass_restock_note');
        ob_start();
        echo '<section class="instapass-restock" style="margin:20px 0;padding:20px;border:1px solid #7466ed;border-radius:16px"><h3>Be first to know</h3>';
        if($date)echo '<p>Expected back: '.esc_html($date).' (estimated)</p>';
        if($note)echo '<p>'.esc_html($note).'</p>';
        if(self::ready()){
            echo '<p>Get one email when this product is available. Confirm your email to activate your alert.</p><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="instapass_stock_signup"><input type="hidden" name="product_id" value="'.(int)$p->get_id().'">';
            wp_nonce_field('instapass_stock_signup','alert_nonce',false);
            echo '<label for="instapass-alert-email">Email address</label><input id="instapass-alert-email" name="email" type="email" autocomplete="email" required maxlength="254" style="display:block;width:100%;box-sizing:border-box;margin:8px 0;padding:12px"><div hidden aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div><button class="wp-element-button" type="submit">Notify me when available</button><p><small>Only this product alert. You can unsubscribe from either email. See our <a href="'.esc_url(get_privacy_policy_url()).'">privacy policy</a>.</small></p></form>';
        }else echo '<p>Restock email alerts are being prepared. Please check back soon.</p>';
        echo '</section>';return ob_get_clean();
    }
    static function signup(){
        $id=absint(self::input('product_id'));$p=wc_get_product($id);
        $valid=self::ready() && wp_verify_nonce(self::input('alert_nonce'),'instapass_stock_signup') && !self::input('website') && $p && $p->is_type('simple') && $p->get_status()==='publish' && !$p->is_in_stock();
        $email=strtolower(trim(self::input('email')));
        if(!$valid || !is_email($email) || strlen($email)>254)wp_die('Unable to save this request. Please return to the product and try again.','Restock alert',['response'=>400]);
        // Hash rate keys; never put raw addresses in transient names.
        $ip=self::rate_key($email,$_SERVER['REMOTE_ADDR']??'');
        $count=(int)get_transient('ipa_ip_'.$ip);
        if($count>=10)wp_die('Please wait before requesting another alert.','Restock alert',['response'=>429]);
        set_transient('ipa_ip_'.$ip,$count+1,HOUR_IN_SECONDS);
        self::subscribe($id,$email);
        self::message('Check your inbox','If a new alert can be created, a confirmation email will arrive shortly. Existing confirmed alerts remain active.');
    }
    static function rate_key($email,$peer){
        // A reverse proxy can present one private address for every visitor.
        // Do not trust client-supplied forwarding headers or impose one shared guest limit.
        $public=filter_var($peer,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE);
        return hash_hmac('sha256',$public?'ip:'.$peer:'email:'.strtolower($email),wp_salt('auth'));
    }
    static function subscribe($id,$email){
        global $wpdb;$t=self::table();$hash=hash('sha256',strtolower($email));
        $existing=$wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE product_id=%d AND email_hash=%s",$id,$hash));
        if($existing && ($existing->status==='confirmed' || ($existing->status==='pending' && $existing->expires_at>time())))return (int)$existing->id;
        $seed=wp_generate_password(48,false,false);
        if($existing){
            // Never replace an in-flight record, including an expired confirmation send.
            if($existing->lock_token!=='')return (int)$existing->id;
            $wpdb->update($t,['seed'=>$seed,'status'=>'pending','created_at'=>time(),'expires_at'=>time()+2*DAY_IN_SECONDS,'confirmed_at'=>0,'submitted_at'=>0,'confirm_sent_at'=>0,'attempts'=>0],['id'=>$existing->id,'lock_token'=>'']);
            $rid=(int)$existing->id;
        }else{
            $ok=$wpdb->insert($t,['product_id'=>$id,'email'=>$email,'email_hash'=>$hash,'seed'=>$seed,'created_at'=>time(),'expires_at'=>time()+2*DAY_IN_SECONDS]);
            if(!$ok)return 0;$rid=(int)$wpdb->insert_id;
        }
        self::schedule($rid,'confirm');return $rid;
    }
    static function message($title,$body){
        wp_die('<p>'.esc_html($body).'</p><p><a href="'.esc_url(wc_get_page_permalink('shop')).'">Return to the shop</a></p>',esc_html($title),['response'=>200]);
    }
    static function token_page(){
        if(!isset($_GET['instapass_alert']))return;
        nocache_headers();header('Referrer-Policy: no-referrer');
        global $wpdb;$purpose=is_string($_GET['instapass_alert'])?$_GET['instapass_alert']:'';
        $id=absint($_GET['alert_id']??0);$token=is_string($_GET['token']??null)?$_GET['token']:'';
        $r=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.self::table().' WHERE id=%d',$id));
        if(!$r || !in_array($purpose,['confirm','unsubscribe'],true) || !hash_equals(self::token($r,$purpose),$token))wp_die('This alert link is invalid.','Restock alert',['response'=>400]);
        if($_SERVER['REQUEST_METHOD']!=='POST'){
            $label=$purpose==='confirm'?'Confirm my restock alert':'Unsubscribe from this alert';
            wp_die('<p>'.esc_html($label).'</p><form method="post"><button type="submit">'.esc_html($label).'</button></form>','Instapass alert',['response'=>200]);
        }
        if($purpose==='unsubscribe'){
            $wpdb->update(self::table(),['status'=>'cancelled'],['id'=>$id]);
            self::message('Unsubscribed','This product alert has been cancelled.');
        }
        if($r->expires_at<time() && $r->status==='pending')self::message('Link expired','Please return to the product and request a new confirmation email.');
        $wpdb->query($wpdb->prepare("UPDATE ".self::table()." SET status='confirmed',confirmed_at=%d,attempts=0 WHERE id=%d AND status='pending'",time(),$id));
        if(self::eligible($r->product_id))self::schedule($id,'stock');
        self::message('Alert confirmed','Your product alert is confirmed. We will email you when it is available.');
    }
    static function restocked($id){
        if(!self::eligible($id))return;
        global $wpdb;
        $ids=$wpdb->get_col($wpdb->prepare("SELECT id FROM ".self::table()." WHERE product_id=%d AND status='confirmed' AND lock_token=''",$id));
        foreach($ids as $rid)self::schedule($rid,'stock');
    }
    static function budget(){
        global $wpdb;$t=$wpdb->prefix.'instapass_mail_budget';$day=gmdate('Y-m-d');$limit=max(1,min(500,(int)self::settings()['daily_limit']));
        $wpdb->query($wpdb->prepare("INSERT IGNORE INTO $t (day,used) VALUES (%s,0)",$day));
        return 1===$wpdb->query($wpdb->prepare("UPDATE $t SET used=used+1 WHERE day=%s AND used<%d",$day,$limit));
    }
    static function job($id,$kind,$job=''){
        if(!in_array($kind,['confirm','stock'],true))return;
        global $wpdb;$t=self::table();$r=$wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE id=%d",$id));
        if(!$r)return;
        if($kind==='confirm' && ($r->status!=='pending' || $r->confirm_sent_at))return;
        if($kind==='stock' && ($r->status!=='confirmed' || !self::eligible($r->product_id)))return;
        if($kind==='confirm' && $r->expires_at<time()){$wpdb->update($t,['status'=>'expired'],['id'=>$id,'status'=>'pending']);return;}
        if((int)$r->attempts>=3)return;
        if(!self::ready()){self::schedule($id,$kind,HOUR_IN_SECONDS);return;}
        $claim=wp_generate_uuid4();
        $extra=$kind==='confirm'?' AND confirm_sent_at=0':'';
        $claimed=$wpdb->query($wpdb->prepare("UPDATE $t SET lock_token=%s,locked_at=%d WHERE id=%d AND lock_token='' AND status=%s AND attempts<3".$extra,$claim,time(),$id,$r->status));
        if($claimed!==1)return;
        $r=$wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE id=%d AND lock_token=%s",$id,$claim));
        if(!$r)return;
        if(!self::budget()){
            $wpdb->update($t,['lock_token'=>'','locked_at'=>0],['id'=>$id,'lock_token'=>$claim]);self::schedule($id,$kind,HOUR_IN_SECONDS);return;
        }
        $p=wc_get_product($r->product_id);
        $name=$p?wp_strip_all_tags($p->get_name()):'your product';
        $unsubscribe=self::url($r,'unsubscribe');
        if($kind==='confirm'){
            $subject='Confirm your Instapass restock alert';
            $body="You requested an alert for ".$name.".\n\nConfirm your email within 48 hours:\n".self::url($r,'confirm')."\n\nIf you did not request this, ignore this email or cancel the request:\n".$unsubscribe;
        }else{
            $subject=$name.' is back at Instapass';
            $body=$name." is available again. Availability can change; see the current details and price before ordering:\n".get_permalink($r->product_id)."\n\nThis completes your one-time alert.\nUnsubscribe:\n".$unsubscribe;
        }
        try{$sent=wp_mail($r->email,$subject,$body,['Content-Type: text/plain; charset=UTF-8']);}catch(Throwable $e){$sent=false;}
        $values=['lock_token'=>'','locked_at'=>0,'attempts'=>(int)$r->attempts+1];
        if($sent){
            if($kind==='confirm')$values['confirm_sent_at']=time();
            else{$values['submitted_at']=time();$values['status']='submitted';}
        }
        // Cancellation/confirmation during SMTP must not be overwritten.
        if(isset($values['status']))$wpdb->update($t,['status'=>$values['status']],['id'=>$id,'lock_token'=>$claim,'status'=>$r->status]);
        unset($values['status']);$wpdb->update($t,$values,['id'=>$id,'lock_token'=>$claim]);
        if(!$sent && $values['attempts']<3)self::schedule($id,$kind,15*MINUTE_IN_SECONDS);
        if($sent && $kind==='confirm' && self::eligible($r->product_id)){
            $latest=$wpdb->get_var($wpdb->prepare("SELECT status FROM $t WHERE id=%d",$id));
            if($latest==='confirmed')self::schedule($id,'stock');
        }
    }
    static function menu(){add_submenu_page('instapass-control','Email & Stock Alerts','Email & Stock Alerts','manage_woocommerce','instapass-email-alerts',[__CLASS__,'page']);}
    static function page(){
        if(!current_user_can('manage_woocommerce'))return;
        global $wpdb;$s=self::settings();
        echo '<div class="wrap"><h1>Email & Stock Alerts</h1><p>Confirmations and one-time restock alerts use the existing WordPress mail connection. A submitted email is accepted by the mail transport; it does not prove inbox delivery.</p>';
        echo '<p><strong>Status: '.(self::ready()?'Enabled':'Paused / inbox confirmation required').'</strong></p>';
        if(isset($s['test_result']))echo '<p>'.(!empty($s['test_result'])?'Latest test was accepted for sending. Check your inbox before confirming receipt.':'Latest test failed. Check the FluentSMTP email log and reconnect the sender if required.').'</p>';
        if(current_user_can('manage_options')){
            echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="instapass_notifications">';
            wp_nonce_field('instapass_notifications');
            echo '<p><label>Daily mail attempt budget <input name="daily_limit" type="number" min="1" max="500" value="'.(int)$s['daily_limit'].'"></label></p><p>This budget covers stock-alert emails only. WooCommerce order emails are separate.</p><p><button class="button" name="operation" value="save">Save budget</button> <button class="button" name="operation" value="test">Send test to site administrator</button> <button class="button" name="operation" value="verify">I received the test email</button> <button class="button button-primary" name="operation" value="enable">Enable alerts</button> <button class="button" name="operation" value="pause">Pause alerts</button></p></form>';
        }
        $counts=$wpdb->get_results("SELECT status,COUNT(*) total FROM ".self::table()." GROUP BY status");
        echo '<h2>Subscriptions</h2><ul>';foreach($counts as $c)echo '<li>'.esc_html($c->status).': '.(int)$c->total.'</li>';echo '</ul>';
        echo '<p>Submitted = mail accepted for sending. A locked row means sending may have been interrupted; inspect your mail log before cancelling or contacting the customer. Failed attempts stop after three tries. Restock dates/messages are edited in Products → Inventory. Requests stay private and can be exported/erased with WordPress privacy tools.</p>';
        $page=max(1,absint($_GET['alert_page']??1));$offset=($page-1)*100;
        $total=(int)$wpdb->get_var("SELECT COUNT(*) FROM ".self::table());
        $rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".self::table()." ORDER BY id DESC LIMIT 100 OFFSET %d",$offset));
        echo '<div style="max-width:100%;overflow-x:auto"><table class="widefat striped" style="min-width:640px"><thead><tr><th>Product</th><th>Email</th><th>Status</th><th>Attempts</th><th>Action</th></tr></thead><tbody>';
        foreach($rows as $r){
            echo '<tr><td>'.esc_html(get_the_title($r->product_id)).'</td><td>'.esc_html($r->email).'</td><td>'.esc_html($r->status.($r->lock_token?' (locked)':'')).'</td><td>'.(int)$r->attempts.'</td><td><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="instapass_notifications"><input type="hidden" name="row_id" value="'.(int)$r->id.'">';
            wp_nonce_field('instapass_notifications');echo '<button class="button" name="operation" value="cancel">Cancel alert</button></form></td></tr>';
        }
        echo '</tbody></table></div>';
        if($total>100)echo '<p>'.wp_kses_post(paginate_links(['base'=>add_query_arg('alert_page','%#%',admin_url('admin.php?page=instapass-email-alerts')),'format'=>'','current'=>$page,'total'=>(int)ceil($total/100)])).'</p>';
        echo '</div>';
    }
    static function admin_post(){
        if(!current_user_can('manage_woocommerce'))wp_die('Not permitted.',['response'=>403]);
        check_admin_referer('instapass_notifications');
        $op=self::input('operation');$s=self::settings();
        if($op==='cancel'){
            global $wpdb;$wpdb->update(self::table(),['status'=>'cancelled'],['id'=>absint(self::input('row_id'))]);
        }else{
            if(!current_user_can('manage_options'))wp_die('Not permitted.',['response'=>403]);
            if($op==='save')$s['daily_limit']=max(1,min(500,absint(self::input('daily_limit'))));
            if($op==='pause')$s['enabled']=0;
            if($op==='test'){
                $s['enabled']=0;$s['verified']='';
                try{$ok=wp_mail(get_option('admin_email'),'Instapass email delivery check',"This is the Instapass email delivery check. If this arrived in your inbox, return to Email & Stock Alerts and select “I received the test email”.",['Content-Type: text/plain; charset=UTF-8']);}catch(Throwable $e){$ok=false;}
                $s['test_result']=(bool)$ok;$s['test']=$ok?self::sender():'';$s['test_at']=time();
            }
            if($op==='verify'){
                if(empty($s['test']) || !hash_equals(self::sender(),$s['test']) || time()-($s['test_at']??0)>DAY_IN_SECONDS)wp_die('Send a fresh test email first.');
                $s['verified']=self::sender();
            }
            if($op==='enable'){
                if(!hash_equals(self::sender(),(string)$s['verified']))wp_die('Send a test email and confirm it arrived before enabling alerts.');
                $s['enabled']=1;
            }
            update_option(self::OPTION,$s,false);
        }
        wp_safe_redirect(admin_url('admin.php?page=instapass-email-alerts'));exit;
    }
    static function exporter($exporters){
        $exporters['instapass-stock-alerts']=['exporter_friendly_name'=>'Instapass restock alerts','callback'=>function($email,$page=1){
            global $wpdb;$rows=$wpdb->get_results($wpdb->prepare('SELECT * FROM '.self::table().' WHERE email_hash=%s LIMIT 100 OFFSET %d',hash('sha256',strtolower($email)),(max(1,$page)-1)*100));$data=[];
            foreach($rows as $r)$data[]=['group_id'=>'instapass-stock-alerts','group_label'=>'Restock alerts','item_id'=>'instapass-alert-'.$r->id,'data'=>[['name'=>'Product','value'=>get_the_title($r->product_id)],['name'=>'Email','value'=>$r->email],['name'=>'Status','value'=>$r->status]]];
            return ['data'=>$data,'done'=>count($rows)<100];
        }];return $exporters;
    }
    static function eraser($erasers){
        $erasers['instapass-stock-alerts']=['eraser_friendly_name'=>'Instapass restock alerts','callback'=>function($email,$page=1){
            global $wpdb;$removed=$wpdb->delete(self::table(),['email_hash'=>hash('sha256',strtolower($email))]);
            return ['items_removed'=>(bool)$removed,'items_retained'=>false,'messages'=>[],'done'=>true];
        }];return $erasers;
    }
}
register_activation_hook(__FILE__,['Instapass_Notifications','install']);
add_action('plugins_loaded',function(){if(class_exists('WooCommerce'))Instapass_Notifications::init();});
