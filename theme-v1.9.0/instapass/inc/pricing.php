<?php
/** WooCommerce prices remain the single source of truth. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function instapass_price_fingerprint( $product ) {
	return hash( 'sha256', wp_json_encode( array( $product->get_regular_price( 'edit' ), $product->get_sale_price( 'edit' ), $product->get_date_on_sale_from() ? $product->get_date_on_sale_from()->getTimestamp() : null, $product->get_date_on_sale_to() ? $product->get_date_on_sale_to()->getTimestamp() : null, $product->get_status(), $product->get_stock_status(), $product->get_stock_quantity(), $product->get_manage_stock() ) ) );
}

/** Validate and calculate without side effects, also used by QA. */
function instapass_price_values( $regular_raw, $sale_raw, $discount_raw, $mode, $round_99 = false ) {
	foreach ( array( $regular_raw, $sale_raw, $discount_raw ) as $raw ) {
		if ( ! is_scalar( $raw ) || ( '' !== (string) $raw && ( ! is_numeric( $raw ) || ! is_finite( (float) $raw ) || (float) $raw < 0 ) ) ) {
			return new WP_Error( 'invalid_price', __( 'Enter non-negative numbers only.', 'instapass' ) );
		}
	}
	$regular = '' === (string) $regular_raw ? '' : wc_format_decimal( $regular_raw, wc_get_price_decimals() );
	$sale = '' === (string) $sale_raw ? '' : wc_format_decimal( $sale_raw, wc_get_price_decimals() );
	if ( 'discount' === $mode ) {
		$discount = '' === (string) $discount_raw ? 0 : (float) $discount_raw;
		if ( $discount > 100 ) { return new WP_Error( 'invalid_discount', __( 'Discount must be between 0 and 100%.', 'instapass' ) ); }
		if ( $discount > 0 && ( '' === $regular || (float) $regular <= 0 ) ) { return new WP_Error( 'missing_regular', __( 'A positive regular price is required for a discount.', 'instapass' ) ); }
		$sale = $discount > 0 ? wc_format_decimal( (float) $regular * ( 1 - $discount / 100 ), wc_get_price_decimals() ) : '';
		if ( $round_99 && 2 === wc_get_price_decimals() && $discount > 0 && $discount < 100 ) {
			$target = (float) $regular * ( 1 - $discount / 100 );
			$sale = wc_format_decimal( max( .99, round( $target + .01 ) - .01 ), 2 );
		}
	}
	if ( '' !== $sale && ( '' === $regular || (float) $regular <= 0 || (float) $sale >= (float) $regular ) ) {
		return new WP_Error( 'invalid_sale', __( 'Sale price must be lower than a positive regular price.', 'instapass' ) );
	}
	return array( 'regular' => $regular, 'sale' => $sale );
}

function instapass_pricing_menu() {
	if ( class_exists( 'WooCommerce' ) ) {
		add_submenu_page( 'edit.php?post_type=product', __( 'Pricing & Discounts', 'instapass' ), __( 'Pricing & Discounts', 'instapass' ), 'manage_woocommerce', 'instapass-pricing', 'instapass_pricing_page' );
	}
}
add_action( 'admin_menu', 'instapass_pricing_menu', 30 );

function instapass_pricing_assets( $hook ) {
	if ( 'product_page_instapass-pricing' === $hook ) {
		wp_enqueue_script( 'instapass-pricing', get_template_directory_uri() . '/assets/pricing.js', array(), INSTAPASS_VERSION, true );
		wp_enqueue_style( 'instapass-pricing', get_template_directory_uri() . '/assets/pricing.css', array(), INSTAPASS_VERSION );
	}
}
add_action( 'admin_enqueue_scripts', 'instapass_pricing_assets' );

function instapass_pricing_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( esc_html__( 'Permission denied.', 'instapass' ) ); }
	$notice = get_transient( 'instapass_pricing_notice_' . get_current_user_id() );
	if ( $notice ) { delete_transient( 'instapass_pricing_notice_' . get_current_user_id() ); }
	$search = isset( $_GET['s'] ) && is_scalar( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$page = isset( $_GET['ip_page'] ) ? max( 1, absint( $_GET['ip_page'] ) ) : 1;
	$query = new WP_Query( array( 'post_type' => array( 'product', 'product_variation' ), 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'posts_per_page' => 50, 'paged' => $page, 's' => $search, 'orderby' => 'title', 'order' => 'ASC' ) );
	?>
	<div class="wrap ip-pricing"><h1><?php esc_html_e( 'Pricing, Stock & Discounts', 'instapass' ); ?></h1>
	<p><strong>Enabled</strong> controls whether a product is shown. <strong>Stock</strong> controls whether it can be purchased. Enabled products marked out of stock remain visible with their details.</p>
	<p>Change prices or toggle <strong>Enabled</strong> to show or hide a product. The matching price calculates as you type. <strong>Save changes</strong> applies selected rows; refresh an open storefront page to see changes. Disabled products are retained as drafts.</p>
	<p>Amounts are in <strong><?php echo esc_html( get_woocommerce_currency() ); ?></strong>, using your existing WooCommerce tax settings. Discount badges round to the nearest whole percent. Existing sale dates stay unchanged unless you select <strong>Start now</strong>.</p>
	<?php if ( $notice ) : ?><div class="notice notice-<?php echo empty( $notice['errors'] ) ? 'success' : 'warning'; ?>"><p><?php echo esc_html( sprintf( __( '%d products updated.', 'instapass' ), $notice['saved'] ) ); ?></p><?php foreach ( $notice['errors'] as $error ) : ?><p><?php echo esc_html( $error ); ?></p><?php endforeach; ?></div><?php endif; ?>
	<form method="get"><input type="hidden" name="post_type" value="product"><input type="hidden" name="page" value="instapass-pricing"><label for="ip-price-search">Search products </label><input id="ip-price-search" name="s" value="<?php echo esc_attr( $search ); ?>"><button class="button">Search</button></form>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-decimals="<?php echo esc_attr( wc_get_price_decimals() ); ?>" id="ip-price-form">
	<input type="hidden" name="action" value="instapass_save_prices"><input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>"><input type="hidden" name="ip_page" value="<?php echo esc_attr( $page ); ?>"><?php wp_nonce_field( 'instapass_save_prices' ); ?>
	<?php if ( 2 === wc_get_price_decimals() ) : ?><p><label><input id="ip-round-99" type="checkbox" name="round_99" value="1" checked> Round percentage-calculated sale prices to the nearest .99</label><br><small>The actual discount may differ slightly from the entered percentage. Directly entered sale prices stay as typed. Untick for an exact percentage.</small></p><?php endif; ?>
	<div class="ip-pricing-scroll"><table class="widefat striped"><thead><tr><th>Save</th><th>Product</th><th>Enabled</th><th>Stock</th><th>Regular price</th><th>Sale price</th><th>Discount %</th><th>Sale timing</th></tr></thead><tbody>
	<?php foreach ( $query->posts as $post ) :
		$product = wc_get_product( $post->ID );
		if ( ! $product || ! current_user_can( 'edit_post', $post->ID ) ) { continue; }
		$id = $product->get_id();
		$editable = $product->is_type( array( 'simple', 'external', 'variation' ) );
		$regular = $product->get_regular_price( 'edit' ); $sale = $product->get_sale_price( 'edit' );
		$discount = '' !== $sale && (float) $regular > 0 ? round( ( 1 - (float) $sale / (float) $regular ) * 100, 2 ) : ( '' === $regular ? $product->get_meta( '_instapass_default_discount' ) : '' );
		?>
		<tr data-price-row><td><?php if ( $editable ) : ?><input aria-label="Save <?php echo esc_attr( $product->get_name() ); ?>" type="checkbox" name="selected[]" value="<?php echo esc_attr( $id ); ?>" data-selected><?php endif; ?></td>
		<td><a href="<?php echo esc_url( get_edit_post_link( $product->is_type( 'variation' ) ? $product->get_parent_id() : $id ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a><small><?php echo esc_html( strtoupper( $product->get_type() ) . ' · ' . $product->get_status() . ( $product->get_sku() ? ' · ' . $product->get_sku() : '' ) ); ?></small></td>
		<?php if ( ! $editable ) : ?><td colspan="6">Prices and stock are derived from child products. Edit variations below; open the parent product to change its visibility.</td><?php else : ?>
		<td><label><input type="checkbox" aria-label="Enabled for <?php echo esc_attr( $product->get_name() ); ?>" name="prices[<?php echo esc_attr( $id ); ?>][enabled]" value="1" <?php checked( 'publish', $product->get_status() ); ?> data-enabled> Enabled</label><input type="hidden" name="prices[<?php echo esc_attr( $id ); ?>][availability_changed]" value="0" data-availability-changed><?php if ( '' === $regular ) { echo '<small>Visible with Price on request until priced. Enter a regular price to calculate the default 25% discount.</small>'; } ?></td>
		<td><select aria-label="Stock for <?php echo esc_attr($product->get_name()); ?>" name="prices[<?php echo esc_attr($id); ?>][stock]" data-stock><option value="instock" <?php selected($product->get_stock_status(),'instock'); ?>>In stock</option><option value="outofstock" <?php selected($product->get_stock_status(),'outofstock'); ?>>Out of stock</option><?php if($product->get_stock_status()==='onbackorder'): ?><option value="onbackorder" selected>On backorder</option><?php endif; ?></select><input type="hidden" name="prices[<?php echo esc_attr($id); ?>][stock_changed]" value="0" data-stock-changed><?php if($product->managing_stock()){echo '<small>Quantity managed in product Inventory.</small>';} ?></td>
		<?php foreach ( array( 'regular' => $regular, 'sale' => $sale, 'discount' => $discount ) as $field => $value ) : ?>
		<td><input aria-label="<?php echo esc_attr( ucfirst( $field ) . ' for ' . $product->get_name() ); ?>" type="number" min="0" <?php if ( 'discount' === $field ) { echo 'max="100"'; } ?> step="<?php echo 'discount' === $field ? '0.01' : esc_attr( pow( 10, -wc_get_price_decimals() ) ); ?>" name="prices[<?php echo esc_attr( $id ); ?>][<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-field="<?php echo esc_attr( $field ); ?>"></td>
		<?php endforeach; ?>
		<td><?php foreach ( array( 'from' => $product->get_date_on_sale_from(), 'to' => $product->get_date_on_sale_to() ) as $label => $date ) { if ( $date ) { echo '<small>' . esc_html( ucfirst( $label ) . ': ' . $date->date_i18n( 'Y-m-d H:i' ) ) . '</small>'; } } ?>
		<small><?php echo $product->is_on_sale() ? 'Active sale' : ( '' !== $sale ? 'Scheduled or inactive' : 'No sale' ); ?></small><label><input type="checkbox" name="prices[<?php echo esc_attr( $id ); ?>][start_now]" value="1" data-start-now> Start now</label>
		<input type="hidden" name="prices[<?php echo esc_attr( $id ); ?>][mode]" value="sale" data-mode><input type="hidden" name="prices[<?php echo esc_attr( $id ); ?>][fingerprint]" value="<?php echo esc_attr( instapass_price_fingerprint( $product ) ); ?>"></td>
		<?php endif; ?></tr>
	<?php endforeach; if ( ! $query->posts ) : ?><tr><td colspan="8">No products found. Add a product under Products → Add New first.</td></tr><?php endif; ?>
	</tbody></table></div><p><button type="submit" class="button button-primary">Save changes</button> <span id="ip-pricing-status" role="status" aria-live="polite">Only selected rows will be saved.</span></p></form>
	<?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( array( 'post_type' => 'product', 'page' => 'instapass-pricing', 's' => $search, 'ip_page' => '%#%' ), admin_url( 'edit.php' ) ), 'format' => '', 'current' => $page, 'total' => $query->max_num_pages ) ) ); ?>
	</div><?php
}

function instapass_save_prices() {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wc_get_product' ) ) { wp_die( esc_html__( 'Permission denied.', 'instapass' ), '', array( 'response' => 403 ) ); }
	check_admin_referer( 'instapass_save_prices' );
	$selected = isset( $_POST['selected'] ) && is_array( $_POST['selected'] ) ? array_unique( array_map( 'absint', $_POST['selected'] ) ) : array();
	if ( count( $selected ) > 50 ) { wp_die( 'Save at most 50 rows at once.' ); }
	$prices = isset( $_POST['prices'] ) && is_array( $_POST['prices'] ) ? wp_unslash( $_POST['prices'] ) : array();
	$notice = array( 'saved' => 0, 'errors' => array() );
	foreach ( $selected as $id ) {
		$product = wc_get_product( $id );
		if ( ! $product || ! current_user_can( 'edit_post', $id ) || ! $product->is_type( array( 'simple', 'external', 'variation' ) ) || ! isset( $prices[$id] ) || ! is_array( $prices[$id] ) ) { $notice['errors'][] = 'A selected product cannot be edited.'; continue; }
		$row = $prices[$id];
		if ( ! isset( $row['fingerprint'] ) || ! is_string( $row['fingerprint'] ) || ! hash_equals( instapass_price_fingerprint( $product ), $row['fingerprint'] ) ) { $notice['errors'][] = $product->get_name() . ': price changed in another session. Reload and try again.'; continue; }
		$values = instapass_price_values( $row['regular'] ?? '', $row['sale'] ?? '', $row['discount'] ?? '', isset( $row['mode'] ) && 'discount' === $row['mode'] ? 'discount' : 'sale', ! empty( $_POST['round_99'] ) );
		if ( is_wp_error( $values ) ) { $notice['errors'][] = $product->get_name() . ': ' . $values->get_error_message(); continue; }
		$change_status = ! empty( $row['availability_changed'] ); $enable = ! empty( $row['enabled'] );
		$change_stock = ! empty($row['stock_changed']); $stock = $row['stock'] ?? $product->get_stock_status();
		if($change_stock && (!is_string($stock) || !in_array($stock,array('instock','outofstock','onbackorder'),true))){$notice['errors'][]=$product->get_name().': choose a valid stock status.';continue;}
		if($change_stock && $stock==='instock' && $product->managing_stock() && $product->get_stock_quantity()<=0){$notice['errors'][]=$product->get_name().': increase its stock quantity in Inventory before marking it in stock.';continue;}
		if ( $change_status && $enable && ! current_user_can( 'publish_products' ) ) { $notice['errors'][] = $product->get_name() . ': publishing permission is required before enabling.'; continue; }
		try {
			$product->set_regular_price( $values['regular'] ); $product->set_sale_price( $values['sale'] );
			if ( $change_status ) { $product->set_status( $enable ? 'publish' : 'draft' ); }
			if ( $change_stock ) { $product->set_stock_status($stock); }
			if ( isset( $row['mode'] ) && 'discount' === $row['mode'] ) { $product->update_meta_data( '_instapass_default_discount', (float) ( $row['discount'] ?? 0 ) ); }
			if ( ! empty( $row['start_now'] ) || '' === $values['sale'] ) { $product->set_date_on_sale_from( null ); $product->set_date_on_sale_to( null ); }
			$product->set_price( $product->is_on_sale( 'edit' ) ? $values['sale'] : $values['regular'] );
			$product->save();
			if ( $product->is_type( 'variation' ) ) { WC_Product_Variable::sync( $product->get_parent_id() ); }
			$notice['saved']++;
		} catch ( Exception $error ) { $notice['errors'][] = $product->get_name() . ': ' . $error->getMessage(); }
	}
	set_transient( 'instapass_pricing_notice_' . get_current_user_id(), $notice, MINUTE_IN_SECONDS );
	$search = isset( $_POST['s'] ) && is_scalar( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'product', 'page' => 'instapass-pricing', 's' => $search, 'ip_page' => isset( $_POST['ip_page'] ) ? max( 1, absint( $_POST['ip_page'] ) ) : 1 ), admin_url( 'edit.php' ) ) );
	exit;
}
add_action( 'admin_post_instapass_save_prices', 'instapass_save_prices' );
