<?php
/**
 * Plugin Name: Instapass Control Center
 * Description: Owner controls, editable USD/PKR rate, independent GBP/EUR prices and customer currency selection.
 * Version: 0.8.0
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH')) exit;
define('INSTAPASS_CONTROL_VERSION', '0.8.0');
require_once __DIR__ . '/includes/money.php';
require_once __DIR__ . '/includes/rate-settings.php';
require_once __DIR__ . '/includes/currencies.php';
require_once __DIR__ . '/includes/gbp-editor.php';
require_once __DIR__ . '/includes/eur-editor.php';
require_once __DIR__ . '/includes/pkr-editor.php';
require_once __DIR__ . '/includes/bulk-pricing.php';
require_once __DIR__ . '/includes/offer-manager.php';
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
add_action('admin_menu', function () {
    if (class_exists('WooCommerce')) add_menu_page('Instapass', 'Instapass', 'manage_woocommerce', 'instapass-control', 'instapass_control_page', 'dashicons-store', 55);
});
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_instapass-control') wp_enqueue_style('instapass-control', plugins_url('assets/admin.css', __FILE__), [], INSTAPASS_CONTROL_VERSION);
});
function instapass_control_page() {
    if (!current_user_can('manage_woocommerce')) wp_die('Permission denied.', '', ['response'=>403]);
    if (!function_exists('wc_get_orders')) { echo '<div class="wrap"><h1>Instapass</h1><p>Activate WooCommerce to use this dashboard.</p></div>'; return; }
    $quote=null; $error=''; $usd='4.99'; $quantity='1';
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        check_admin_referer('instapass_currency_preview');
        $usd=isset($_POST['usd']) && is_string($_POST['usd']) ? trim(wp_unslash($_POST['usd'])) : '';
        $quantity=isset($_POST['quantity']) && is_string($_POST['quantity']) ? trim(wp_unslash($_POST['quantity'])) : '';
        try {
            if (!preg_match('/^[1-9][0-9]{0,3}$/D', $quantity)) throw new InvalidArgumentException('Quantity must be a whole number from 1 to 2500.');
            $quote=Instapass_Control_Money::quote($usd, (int)$quantity, instapass_control_rate());
        } catch (InvalidArgumentException $e) { $error=$e->getMessage(); }
    }
    $counts=wp_count_posts('product');
    $unpriced=new WP_Query(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','meta_query'=>['relation'=>'OR',['key'=>'_price','compare'=>'NOT EXISTS'],['key'=>'_price','value'=>'','compare'=>'=']]]);
    $out=new WP_Query(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','meta_query'=>[['key'=>'_stock_status','value'=>'outofstock']]]);
    $orders=wc_get_orders(['limit'=>5,'orderby'=>'date','order'=>'DESC','return'=>'objects']);
    $hpos=class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    $orders_path=$hpos?'admin.php?page=wc-orders':'edit.php?post_type=shop_order';
    $gateways=WC()->payment_gateways()->payment_gateways();
    ?>
    <div class="wrap ipc">
      <header class="ipc-hero"><div><span class="ipc-eyebrow">INSTAPASS / STORE MANAGEMENT</span><h1>Your store, in one place.</h1><p>Manage your catalog, check orders and prepare USD, PKR and independent GBP/EUR prices.</p></div><a class="button" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">View storefront <span aria-hidden="true">↗</span></a></header>
      <?php instapass_control_rate_panel(); ?>
      <div class="ipc-metrics">
      <?php foreach ([['Published products',(int)$counts->publish],['Awaiting a price',(int)$unpriced->found_posts],['Out of stock',(int)$out->found_posts],['Orders awaiting payment',(int)wc_orders_count('pending')+(int)wc_orders_count('on-hold')]] as $metric): ?>
        <div class="ipc-metric"><span><?php echo esc_html($metric[0]); ?></span><strong><?php echo esc_html(number_format_i18n($metric[1])); ?></strong></div>
      <?php endforeach; ?>
      </div>
      <div class="ipc-grid"><section class="ipc-card"><h2>Daily controls</h2><div class="ipc-actions">
      <?php
      $links=[['Products & Offers','admin.php?page=instapass-offers'],['Add a product / duration','admin.php?page=instapass-offers'],['Orders & refunds',$orders_path],['Payment methods','admin.php?page=wc-settings&tab=checkout'],['Customer reviews','edit.php?post_type=product&page=product-reviews']];
      $links[]=['Prices & bulk discounts','admin.php?page=instapass-control-pricing'];
      foreach($links as [$label,$url]): ?><a href="<?php echo esc_url(admin_url($url)); ?>"><?php echo esc_html($label); ?> <span aria-hidden="true">→</span></a><?php endforeach; ?>
      </div><p class="ipc-muted">Refunding an order in WooCommerce and returning money through a manual payment method are separate steps.</p></section>
      <section class="ipc-card"><span class="ipc-badge">PRICE PREVIEW</span><h2>USD → PKR</h2><p>1 USD = Rs <?php echo esc_html(instapass_control_rate()); ?>. Round each product unit to the nearest Rs 100, then multiply by quantity.</p>
      <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=instapass-control')); ?>" class="ipc-preview">
      <?php wp_nonce_field('instapass_currency_preview'); ?>
      <label for="ipc-usd">USD unit price<input id="ipc-usd" name="usd" inputmode="decimal" type="text" maxlength="9" value="<?php echo esc_attr($usd); ?>" required></label>
      <label for="ipc-qty">Quantity<input id="ipc-qty" name="quantity" inputmode="numeric" type="number" min="1" max="2500" step="1" value="<?php echo esc_attr($quantity); ?>" required></label>
      <button class="button button-primary" type="submit">Preview price</button></form>
      <?php if ($error): ?><div class="ipc-error" role="alert"><?php echo esc_html($error); ?></div><?php endif; ?>
      <?php if ($quote): ?><div class="ipc-result" role="status"><strong>Rs <?php echo esc_html(number_format($quote['pkr_unit_rupees'])); ?> <small>per unit</small></strong><p><?php echo esc_html($quote['quantity']); ?> × Rs <?php echo esc_html(number_format($quote['pkr_unit_rupees'])); ?> = <b>Rs <?php echo esc_html(number_format($quote['pkr_total_rupees'])); ?></b></p><p>USD total: $<?php echo esc_html(Instapass_Control_Money::usd_text($quote['usd_total_cents'])); ?></p></div><?php else: ?><div class="ipc-example">$4.99 → Rs <?php echo esc_html(number_format(Instapass_Control_Money::quote('4.99',1,instapass_control_rate())['pkr_unit_rupees'])); ?> <span>·</span> $9.99 → Rs <?php echo esc_html(number_format(Instapass_Control_Money::quote('9.99',1,instapass_control_rate())['pkr_unit_rupees'])); ?></div><?php endif; ?>
      <p class="ipc-muted">This calculator previews rate-derived prices; a fixed PKR product override takes priority. Customers can choose USD, GBP, EUR or PKR; payments are available only in currencies configured for each payment method.</p><p><strong>GBP (£) and EUR (€):</strong> Set independent regular prices, sale prices or discounts under <a href="<?php echo esc_url(admin_url('edit.php?post_type=product')); ?>">Products → Edit → Product data → General</a>. Leave a GBP or EUR price blank until ready; no conversion from USD.</p></section></div>
      <section class="ipc-card"><h2>Recent orders</h2><div class="ipc-table"><table class="widefat striped"><thead><tr><th>Order</th><th>Created</th><th>Status</th><th>Total</th></tr></thead><tbody>
      <?php foreach($orders as $order): ?><tr><td><a href="<?php echo esc_url($order->get_edit_order_url()); ?>">#<?php echo esc_html($order->get_order_number()); ?></a></td><td><?php echo esc_html($order->get_date_created()?wc_format_datetime($order->get_date_created()):'—'); ?></td><td><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></td><td><?php echo wp_kses_post($order->get_formatted_order_total()); ?></td></tr><?php endforeach; ?>
      <?php if (!$orders): ?><tr><td colspan="4">No orders yet.</td></tr><?php endif; ?></tbody></table></div><p><a href="<?php echo esc_url(admin_url($orders_path)); ?>">View all orders →</a></p></section>
      <section class="ipc-card"><h2>Payment setup</h2><p>Enabled below means enabled in WooCommerce settings. Checkout availability also depends on configuration and compatibility.</p><div class="ipc-table"><table class="widefat striped"><thead><tr><th>Method</th><th>Setting</th><th>Configure</th></tr></thead><tbody>
      <?php foreach($gateways as $id=>$gateway): ?><tr><td><?php echo esc_html($gateway->get_method_title()); ?></td><td><?php echo esc_html($gateway->enabled==='yes'?'Enabled':'Disabled'); ?></td><td><a href="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section='.rawurlencode($id))); ?>">Settings</a></td></tr><?php endforeach; ?>
      </tbody></table></div><p class="ipc-muted">Payment reports require verification against money received. Keep friend-owned personal Wise / Revolut accounts disabled until the actual collection arrangement is accepted by the provider.</p></section>
    </div>
    <?php
}
