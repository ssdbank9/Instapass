<?php
/** Owner-only bulk pricing. WooCommerce remains the price source of truth. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function instapass_bulk_pricing_assets() {
	$uri = get_template_directory_uri();
	wp_enqueue_script( 'instapass-xlsx', $uri . '/assets/vendor/xlsx.full.min.js', array(), '0.20.3', true );
	wp_enqueue_script( 'instapass-bulk-pricing', $uri . '/assets/bulk-pricing.js', array( 'instapass-xlsx' ), INSTAPASS_VERSION, true );
	wp_localize_script( 'instapass-bulk-pricing', 'InstapassBulk', array( 'url' => rest_url( 'instapass-pricing/v1/' ), 'nonce' => wp_create_nonce( 'wp_rest' ), 'researchUrl' => $uri . '/data/researched-prices-2026-10-09.json' ) );
}

function instapass_bulk_pricing_panel() {
	?>
	<section id="ip-bulk" class="ip-bulk" aria-labelledby="ip-bulk-title">
	<h2 id="ip-bulk-title">Update many prices at once</h2>
	<p>Choose all eligible tools or select a group, enter a discount, and preview the exact prices. You can also upload an Excel file. Prices change only when you click <strong>Apply preview</strong>.</p>
	<p><label>Discount % <input id="ip-bulk-percent" type="number" min="0" max="100" step="0.01" value="25"></label>
	<label><input id="ip-bulk-round" type="checkbox" checked> Nearest .99</label>
	<button type="button" class="button" id="ip-bulk-all">Preview global discount</button>
	<button type="button" class="button" id="ip-bulk-selected">Preview selected tools</button>
	<button type="button" class="button" id="ip-bulk-random">Preview random 25–30% on selected tools</button></p>
	<p><button type="button" class="button" id="ip-bulk-researched">Preview researched prices · 9 October 2026</button> <span class="description">Only matched USD rates; unresolved offers retain their current prices. Review the source workbook before applying.</span></p>
	<p><button type="button" class="button" id="ip-bulk-template">Download Excel template</button>
	<label> Upload prices <input id="ip-bulk-file" type="file" accept=".xlsx,.csv"></label></p>
	<p class="description">Use the Prices sheet: SKU, Regular price, Discount %, Sale price, Currency. Enter a discount or a direct sale price, not both. Blank cells preserve current values; zero discount removes a sale. Currency must match the store. Formula cells are rejected; use values only. Up to 500 rows and 2 MB. Uploading does not change visibility or stock.</p>
	<p><label>Find a tool <input type="search" id="ip-bulk-search"></label>
	<button type="button" class="button" id="ip-bulk-check">Select visible tools</button>
	<button type="button" class="button" id="ip-bulk-clear">Clear selection</button></p>
	<p id="ip-bulk-status" role="status" aria-live="polite">Loading catalog…</p>
	<div class="ip-pricing-scroll" style="max-height:350px;overflow:auto"><table class="widefat striped"><thead><tr><th>Select</th><th>Tool / SKU</th><th>Regular price</th><th>Sale price</th><th>Status</th></tr></thead><tbody id="ip-bulk-catalog"></tbody></table></div>
	<div id="ip-bulk-preview" hidden><h3>Price preview</h3><p id="ip-bulk-summary"></p><div class="ip-pricing-scroll"><table class="widefat striped"><thead><tr><th>Tool</th><th>Current regular / sale</th><th>New regular / sale</th><th>Actual discount</th></tr></thead><tbody id="ip-bulk-changes"></tbody></table></div><p><button type="button" class="button button-primary" id="ip-bulk-apply">Apply preview</button> <button type="button" class="button" id="ip-bulk-cancel">Cancel preview</button></p></div>
	<div id="ip-bulk-history"><h3>Recent bulk changes</h3><p>Loading history…</p></div>
	</section><hr>
	<?php
}

function instapass_bulk_permission( $request ) {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wc_get_product' ) ) { return new WP_Error( 'forbidden', 'WooCommerce management permission is required.', array( 'status' => 403 ) ); }
	if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) { return new WP_Error( 'nonce', 'Your session expired. Reload and sign in again.', array( 'status' => 403 ) ); }
	return true;
}
add_action( 'rest_api_init', function () {
	foreach ( array( 'catalog' => array( 'GET', 'instapass_bulk_catalog' ), 'preview' => array( 'POST', 'instapass_bulk_preview' ), 'apply' => array( 'POST', 'instapass_bulk_apply' ), 'history' => array( 'GET', 'instapass_bulk_history' ), 'undo' => array( 'POST', 'instapass_bulk_undo' ) ) as $route => $definition ) { register_rest_route( 'instapass-pricing/v1', '/' . $route, array( 'methods' => $definition[0], 'callback' => $definition[1], 'permission_callback' => 'instapass_bulk_permission' ) ); }
} );
function instapass_bulk_products() {
	$products = wc_get_products( array( 'limit' => 501, 'status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'type' => array( 'simple', 'external', 'variation' ), 'orderby' => 'title', 'order' => 'ASC' ) );
	if ( count( $products ) > 500 ) { return new WP_Error( 'catalog_limit', 'The catalog exceeds 500 rows. Use individual pricing or smaller SKU imports.', array( 'status' => 400 ) ); }
	return array_values( array_filter( $products, function ( $p ) { return current_user_can( 'edit_post', $p->get_id() ); } ) );
}
function instapass_bulk_snapshot( $p ) {
	return array( 'regular' => $p->get_regular_price( 'edit' ), 'sale' => $p->get_sale_price( 'edit' ), 'from' => $p->get_date_on_sale_from() ? $p->get_date_on_sale_from()->getTimestamp() : null, 'to' => $p->get_date_on_sale_to() ? $p->get_date_on_sale_to()->getTimestamp() : null, 'default_discount' => $p->get_meta( '_instapass_default_discount', true, 'edit' ) );
}
function instapass_bulk_catalog() {
	$products = instapass_bulk_products(); if ( is_wp_error( $products ) ) { return $products; } $rows = array();
	foreach ( $products as $p ) { $rows[] = array( 'id' => $p->get_id(), 'sku' => $p->get_sku(), 'name' => $p->get_name(), 'regular' => $p->get_regular_price( 'edit' ), 'sale' => $p->get_sale_price( 'edit' ), 'status' => $p->get_status(), 'stock' => $p->get_stock_status(), 'fingerprint' => instapass_price_fingerprint( $p ) ); }
	return array( 'currency' => get_woocommerce_currency(), 'rows' => $rows );
}
function instapass_bulk_number( $value, $label, $max = 10000000 ) {
	if ( ! is_scalar( $value ) || is_bool( $value ) || ! preg_match( '/^\d+(?:\.\d+)?$/D', (string) $value ) || ! is_finite( (float) $value ) || (float) $value > $max ) { return new WP_Error( 'invalid_number', $label . ': enter a plain non-negative number.', array( 'status' => 400 ) ); } return (string) $value;
}
function instapass_bulk_error( $message ) { return new WP_Error( 'invalid_batch', $message, array( 'status' => 400 ) ); }
/** .99 inside the strict initial 25-30% band, or cents if no .99 fits. */
function instapass_bulk_random_values( $regular, $discount, $round99 ) {
	if ( ! $round99 || 2 !== wc_get_price_decimals() ) {
        $scale = pow( 10, wc_get_price_decimals() ); $base = (float) wc_format_decimal( $regular, wc_get_price_decimals() );
        $low = ceil( $base * .70 * $scale - .0000001 ); $high = floor( $base * .75 * $scale + .0000001 );
        if ( $low > $high || $base <= 0 ) { return instapass_bulk_error( 'This price cannot represent a 25–30% discount at the store currency precision.' ); }
        $sale = max( $low, min( $high, round( $base * ( 1 - $discount / 100 ) * $scale ) ) ) / $scale;
        return instapass_price_values( $regular, (string) $sale, '', 'sale', false );
    }
	$base = (float) wc_format_decimal( $regular, 2 ); $target = $base * ( 1 - $discount / 100 ); $low = ceil( $base * .70 - .99 - .0000001 ); $high = floor( $base * .75 - .99 + .0000001 );
	if ( $low <= $high ) { $sale = max( $low, min( $high, round( $target - .99 ) ) ) + .99; }
	else { $sale = floor( $base * .75 * 100 + .0000001 ) / 100; if ( $sale < $base * .70 ) { return instapass_bulk_error( 'This price cannot represent a 25–30% discount at the store currency precision.' ); } }
	return instapass_price_values( $regular, (string) $sale, '', 'sale', false );
}
function instapass_bulk_preview( $request ) {
	$body = $request->get_json_params(); if ( ! is_array( $body ) ) { return instapass_bulk_error( 'Send a valid price request.' ); }
	$mode = $body['mode'] ?? ''; $round99 = ! empty( $body['round99'] );
	if ( ! in_array( $mode, array( 'global', 'selected', 'random', 'import' ), true ) ) { return instapass_bulk_error( 'Choose a supported bulk operation.' ); } $rows = array(); $skipped = array();
	if ( 'import' === $mode ) { $rows = $body['rows'] ?? null; if ( ! is_array( $rows ) || ! count( $rows ) || count( $rows ) > 500 ) { return instapass_bulk_error( 'Import between 1 and 500 rows.' ); } }
	else {
		$discount = 'random' === $mode ? '25' : instapass_bulk_number( $body['discount'] ?? '', 'Discount', 100 ); if ( is_wp_error( $discount ) ) { return $discount; }
		$products = instapass_bulk_products(); if ( is_wp_error( $products ) ) { return $products; } $ids = $body['ids'] ?? array();
		if ( 'global' !== $mode && ( ! is_array( $ids ) || ! count( $ids ) || count( $ids ) > 500 ) ) { return instapass_bulk_error( 'Select at least one tool.' ); }
		foreach ( $ids as $id ) { if ( ! is_numeric( $id ) || (int) $id <= 0 || (string) (int) $id !== (string) $id ) { return instapass_bulk_error( 'Invalid product selection.' ); } }
		$found = array();
		foreach ( $products as $p ) {
			if ( 'global' !== $mode && ! in_array( $p->get_id(), array_map( 'intval', $ids ), true ) ) { continue; } $found[] = $p->get_id();
			if ( '' === $p->get_regular_price( 'edit' ) || (float) $p->get_regular_price( 'edit' ) <= 0 ) { $skipped[] = $p->get_name() . ': no positive regular price.'; continue; }
			$rows[] = array( 'id' => $p->get_id(), 'discount' => 'random' === $mode ? (string) random_int( 25, 30 ) : $discount );
		}
		if ( 'global' !== $mode && count( array_unique( array_map( 'intval', $ids ) ) ) !== count( $found ) ) { return instapass_bulk_error( 'A selected tool is missing or not editable. Reload the catalog.' ); }
	}
	$changes = array(); $seen = array();
	foreach ( $rows as $index => $row ) {
		$label = 'Row ' . ( $index + 2 ); if ( ! is_array( $row ) ) { return instapass_bulk_error( $label . ': invalid row.' ); }
		if ( isset( $row['currency'] ) && '' !== $row['currency'] && ( ! is_string( $row['currency'] ) || strtoupper( trim( $row['currency'] ) ) !== get_woocommerce_currency() ) ) { return instapass_bulk_error( $label . ': currency must match ' . get_woocommerce_currency() . '.' ); }
		if ( 'import' === $mode ) { if ( ! isset( $row['sku'] ) || ! is_string( $row['sku'] ) || '' === trim( $row['sku'] ) ) { return instapass_bulk_error( $label . ': SKU is required.' ); } $id = wc_get_product_id_by_sku( trim( $row['sku'] ) ); } else { $id = $row['id']; }
		$p = $id ? wc_get_product( $id ) : null;
		if ( ! $p || ! current_user_can( 'edit_post', $id ) || ! $p->is_type( array( 'simple', 'external', 'variation' ) ) ) { return instapass_bulk_error( $label . ': unknown SKU or unsupported product.' ); }
		if ( isset( $seen[$id] ) ) { return instapass_bulk_error( $label . ': duplicate SKU. Remove the duplicate before previewing.' ); } $seen[$id] = true;
		$before = instapass_bulk_snapshot( $p ); $regular = $before['regular']; $sale = $before['sale'];
		foreach ( array( 'regular', 'discount', 'sale' ) as $key ) { if ( isset( $row[$key] ) && '' !== $row[$key] ) { $v = instapass_bulk_number( $row[$key], $label . ' ' . $key, 'discount' === $key ? 100 : 10000000 ); if ( is_wp_error( $v ) ) { return $v; } } }
		if ( isset( $row['regular'] ) && '' !== $row['regular'] ) { $regular = $row['regular']; }
		$has_discount = isset( $row['discount'] ) && '' !== $row['discount']; $has_sale = isset( $row['sale'] ) && '' !== $row['sale'];
		if ( $has_discount && $has_sale ) { return instapass_bulk_error( $label . ': enter Discount % or Sale price, not both.' ); }
		if ( ! $has_discount && ! $has_sale && ( ! isset( $row['regular'] ) || '' === $row['regular'] ) ) { return instapass_bulk_error( $label . ': no price or discount supplied.' ); }
		if ( $has_sale ) { $sale = $row['sale']; }
		$values = 'random' === $mode ? instapass_bulk_random_values( $regular, (float) $row['discount'], $round99 ) : instapass_price_values( $regular, $sale, $row['discount'] ?? '', $has_discount ? 'discount' : 'sale', $round99 );
		if ( is_wp_error( $values ) ) { return instapass_bulk_error( $label . ': ' . $values->get_error_message() ); }
		$after = array( 'regular' => $values['regular'], 'sale' => $values['sale'], 'from' => null, 'to' => null, 'default_discount' => $has_discount ? $row['discount'] : $before['default_discount'] );
		$changes[] = array( 'id' => $id, 'sku' => $p->get_sku(), 'name' => $p->get_name(), 'fingerprint' => instapass_price_fingerprint( $p ), 'before' => $before, 'after' => $after, 'actual_discount' => '' !== $after['sale'] && (float) $after['regular'] > 0 ? round( ( 1 - (float) $after['sale'] / (float) $after['regular'] ) * 100, 4 ) : 0 );
	}
	if ( ! $changes ) { return instapass_bulk_error( 'No eligible priced tools. Enter regular prices first.' ); } $token = wp_generate_uuid4();
	$operation = array( 'token' => $token, 'user' => get_current_user_id(), 'created' => time(), 'state' => 'preview', 'changes' => $changes, 'skipped' => $skipped );
	if ( ! set_transient( 'ip_bulk_' . $token, $operation, 15 * MINUTE_IN_SECONDS ) ) { return new WP_Error( 'preview_storage', 'Could not save the preview. No prices changed.', array( 'status' => 500 ) ); } return $operation;
}
function instapass_bulk_write( $p, $values ) {
	$p->set_regular_price( $values['regular'] ); $p->set_sale_price( $values['sale'] ); $p->set_date_on_sale_from( $values['from'] ); $p->set_date_on_sale_to( $values['to'] );
	if ( '' === $values['default_discount'] ) { $p->delete_meta_data( '_instapass_default_discount' ); } else { $p->update_meta_data( '_instapass_default_discount', $values['default_discount'] ); }
	$p->set_price( $p->is_on_sale( 'edit' ) ? $values['sale'] : $values['regular'] ); if ( ! $p->save() ) { throw new RuntimeException( 'WooCommerce did not save the price.' ); }
	wc_delete_product_transients( $p->get_id() ); if ( $p->is_type( 'variation' ) ) { WC_Product_Variable::sync( $p->get_parent_id() ); }
}
function instapass_bulk_lock( $token ) { return add_option( 'instapass_bulk_price_lock', array( 'token' => $token, 'time' => time() ), '', false ); }
function instapass_bulk_unlock( $token ) { $lock = get_option( 'instapass_bulk_price_lock' ); if ( is_array( $lock ) && ( $lock['token'] ?? '' ) === $token ) { delete_option( 'instapass_bulk_price_lock' ); } }
function instapass_bulk_apply( $request ) {
	$token = $request->get_param( 'token' ); if ( ! is_string( $token ) || ! preg_match( '/^[a-f0-9-]{36}$/D', $token ) ) { return instapass_bulk_error( 'Invalid preview token.' ); }
	if ( ! instapass_bulk_lock( $token ) ) { return new WP_Error( 'busy', 'Another bulk change is running or needs review. Do not retry until it finishes.', array( 'status' => 409 ) ); }
	try {
		$op = get_transient( 'ip_bulk_' . $token ); if ( ! is_array( $op ) || $op['user'] !== get_current_user_id() || 'preview' !== $op['state'] || time() - $op['created'] > 900 ) { return new WP_Error( 'expired', 'Preview expired or already applied. Create a new preview.', array( 'status' => 409 ) ); }
		foreach ( $op['changes'] as $c ) { $p = wc_get_product( $c['id'] ); if ( ! $p || ! current_user_can( 'edit_post', $c['id'] ) || ! hash_equals( $c['fingerprint'], instapass_price_fingerprint( $p ) ) ) { return new WP_Error( 'stale', $c['name'] . ': changed since preview. No prices saved. Reload and preview again.', array( 'status' => 409 ) ); } }
		$op['state'] = 'applying'; $op['saved'] = array(); $op['errors'] = array(); $op['inflight'] = null;
		if ( ! add_option( 'ip_bulk_audit_' . $token, $op, '', false ) ) { return new WP_Error( 'duplicate', 'This operation was already submitted. Check history.', array( 'status' => 409 ) ); }
        $history = get_option( 'ip_bulk_history', array() ); array_unshift( $history, $token );
        if ( ! update_option( 'ip_bulk_history', array_slice( array_unique( $history ), 0, 20 ), false ) ) { delete_option( 'ip_bulk_audit_' . $token ); return new WP_Error( 'audit_storage', 'History could not be saved. No prices changed.', array( 'status' => 500 ) ); }
        delete_transient( 'ip_bulk_' . $token );
		foreach ( $op['changes'] as $c ) {
			try {
				$p = wc_get_product( $c['id'] ); if ( ! $p || ! current_user_can( 'edit_post', $c['id'] ) || ! hash_equals( $c['fingerprint'], instapass_price_fingerprint( $p ) ) ) { throw new RuntimeException( 'Changed during save. Remaining rows were not saved.' ); }
				$op['inflight'] = $c;
                if ( ! update_option( 'ip_bulk_audit_' . $token, $op, false ) ) { throw new RuntimeException( 'Could not journal the next row. No write attempted for it.' ); }
                instapass_bulk_write( $p, $c['after'] ); $op['inflight'] = null; $op['saved'][] = array( 'id' => $c['id'], 'name' => $c['name'], 'before' => $c['before'], 'after' => instapass_bulk_snapshot( $p ), 'after_fingerprint' => instapass_price_fingerprint( $p ) );
				if ( ! update_option( 'ip_bulk_audit_' . $token, $op, false ) ) { throw new RuntimeException( 'Audit save failed. Stop and review this operation.' ); }
			} catch ( Throwable $error ) {
                $op['errors'][] = $c['name'] . ': ' . $error->getMessage();
                if ( $op['inflight'] ) {
                    $fresh = wc_get_product( $c['id'] );
                    if ( $fresh && instapass_bulk_snapshot( $fresh ) === $c['after'] ) {
                        $op['saved'][] = array( 'id' => $c['id'], 'name' => $c['name'], 'before' => $c['before'], 'after' => instapass_bulk_snapshot( $fresh ), 'after_fingerprint' => instapass_price_fingerprint( $fresh ) ); $op['inflight'] = null;
                    } elseif ( $fresh && instapass_bulk_snapshot( $fresh ) === $c['before'] ) { $op['inflight'] = null; }
                    else { $op['errors'][] = 'A row has an uncertain save result. Inspect its price before any further update.'; }
                }
                break;
            }
		}
		$op['state'] = $op['inflight'] ? 'needs_review' : ( $op['errors'] ? 'partial' : 'applied' ); $op['finished'] = time();
        if ( ! update_option( 'ip_bulk_audit_' . $token, $op, false ) ) { return new WP_Error( 'audit_storage', 'Prices may have changed, but the final audit could not be saved. Inspect this operation in history; do not resubmit.', array( 'status' => 500, 'token' => $token ) ); }
		return array( 'token' => $token, 'state' => $op['state'], 'saved' => count( $op['saved'] ), 'requested' => count( $op['changes'] ), 'errors' => $op['errors'] );
	} finally { instapass_bulk_unlock( $token ); }
}
function instapass_bulk_history() {
	$result = array(); foreach ( get_option( 'ip_bulk_history', array() ) as $token ) { $op = get_option( 'ip_bulk_audit_' . $token ); if ( is_array( $op ) ) { $result[] = array( 'token' => $token, 'state' => $op['state'], 'created' => $op['created'], 'saved' => count( $op['saved'] ?? array() ), 'errors' => $op['errors'] ?? array() ); } } return $result;
}
function instapass_bulk_undo( $request ) {
	$token = $request->get_param( 'token' ); if ( ! is_string( $token ) || ! preg_match( '/^[a-f0-9-]{36}$/D', $token ) ) { return instapass_bulk_error( 'Invalid operation.' ); }
	if ( ! instapass_bulk_lock( $token ) ) { return new WP_Error( 'busy', 'Another bulk change is running.', array( 'status' => 409 ) ); }
	try {
		$op = get_option( 'ip_bulk_audit_' . $token ); if ( ! is_array( $op ) || ! in_array( $op['state'], array( 'applied', 'partial' ), true ) ) { return new WP_Error( 'undo_state', 'This operation cannot be undone.', array( 'status' => 409 ) ); }
		foreach ( $op['saved'] as $c ) { $p = wc_get_product( $c['id'] ); if ( ! $p || ! current_user_can( 'edit_post', $c['id'] ) || ! hash_equals( $c['after_fingerprint'], instapass_price_fingerprint( $p ) ) || instapass_bulk_snapshot( $p ) !== $c['after'] ) { return new WP_Error( 'undo_stale', $c['name'] . ': changed after this operation. Undo refused to protect newer changes.', array( 'status' => 409 ) ); } }
		$op['state'] = 'undoing'; if ( ! update_option( 'ip_bulk_audit_' . $token, $op, false ) ) { return new WP_Error( 'audit_storage', 'Undo journal could not be saved. Nothing restored.', array( 'status' => 500 ) ); } $restored = 0;
		try { foreach ( $op['saved'] as $c ) { $p = wc_get_product( $c['id'] ); if ( ! $p || ! current_user_can( 'edit_post', $c['id'] ) || ! hash_equals( $c['after_fingerprint'], instapass_price_fingerprint( $p ) ) || instapass_bulk_snapshot( $p ) !== $c['after'] ) { throw new RuntimeException( $c['name'] . ': changed during undo. Remaining rows were not restored.' ); } instapass_bulk_write( $p, $c['before'] ); $restored++; } $op['state'] = 'undone'; }
		catch ( Throwable $error ) { $op['state'] = 'undo_partial'; $op['errors'][] = $error->getMessage(); }
		$op['restored'] = $restored; if ( ! update_option( 'ip_bulk_audit_' . $token, $op, false ) ) { return new WP_Error( 'audit_storage', 'Undo may have changed prices, but the final audit failed. Inspect the saved prices before continuing.', array( 'status' => 500 ) ); } return array( 'state' => $op['state'], 'restored' => $restored, 'errors' => $op['errors'] );
	} finally { instapass_bulk_unlock( $token ); }
}
