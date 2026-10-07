<?php
/**
 * Plugin Name: Instapass Automation (Owner Pricing)
 * Description: Deterministic, owner-confirmed WooCommerce price commands with audit and guarded undo. No AI or third-party API calls.
 * Version: 0.3.0
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Text Domain: instapass-automation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owner-only, deterministic price operations. Product writes require a saved
 * server-side preview, a separate confirmation, fresh fingerprint validation,
 * and an atomic one-time operation claim. No AI or n8n runtime is used.
 */
final class Instapass_Automation_Preview {
	const REST_NAMESPACE = 'instapass-automation/v1';
	const PAGE_SLUG      = 'instapass-pricing-preview';
	const PREVIEW_TTL    = 300;
	const TABLE_SUFFIX   = 'instapass_price_operations';

	public static function activate() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table           = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE {$table} (
			operation_id char(36) NOT NULL,
			user_id bigint(20) unsigned NOT NULL,
			product_id bigint(20) unsigned NOT NULL,
			payload longtext NOT NULL,
			state varchar(24) NOT NULL DEFAULT 'pending',
			fingerprint_after char(64) NOT NULL DEFAULT '',
			expires_at datetime NOT NULL,
			created_at datetime NOT NULL,
			confirmed_at datetime NULL,
			undone_at datetime NULL,
			audit_log longtext NOT NULL,
			PRIMARY KEY  (operation_id),
			KEY user_created (user_id, created_at),
			KEY state_expiry (state, expires_at)
		) {$charset_collate};";
		dbDelta( $sql );
	}

	private static function table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_SUFFIX;
	}

	public static function register_admin_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Instapass Pricing Assistant', 'instapass-automation' ),
			__( 'Pricing Assistant', 'instapass-automation' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_admin_page' )
		);
	}

	public static function enqueue_admin_assets( $hook_suffix ) {
		if ( 'woocommerce_page_' . self::PAGE_SLUG !== $hook_suffix || ! self::can_manage() ) {
			return;
		}

		$asset_url = plugin_dir_url( __FILE__ ) . 'assets/';
		wp_enqueue_script( 'instapass-automation-preview', $asset_url . 'admin.js', array(), '0.3.0', true );
		wp_localize_script(
			'instapass-automation-preview',
			'InstapassAutomationPreview',
			array(
				'urls'  => array(
					'preview' => esc_url_raw( rest_url( self::REST_NAMESPACE . '/commands/preview' ) ),
					'confirm' => esc_url_raw( rest_url( self::REST_NAMESPACE . '/commands/confirm' ) ),
					'undo'    => esc_url_raw( rest_url( self::REST_NAMESPACE . '/commands/undo' ) ),
					'history' => esc_url_raw( rest_url( self::REST_NAMESPACE . '/commands/history' ) ),
				),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
		wp_enqueue_style( 'instapass-automation-preview', $asset_url . 'admin.css', array(), '0.3.0' );
	}

	public static function render_admin_page() {
		if ( ! self::can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'instapass-automation' ) );
		}
		?>
		<div class="wrap instapass-automation">
			<h1><?php echo esc_html__( 'Pricing and Discounts Assistant', 'instapass-automation' ); ?></h1>
			<p><?php echo esc_html__( 'Describe one price change, review the exact values, then confirm it to update the product.', 'instapass-automation' ); ?></p>
			<div class="notice notice-info inline"><p><?php echo esc_html__( 'Only your explicit confirmation applies a price change. Every applied change is recorded and can be undone if the product has not changed since.', 'instapass-automation' ); ?></p></div>
			<form id="instapass-preview-form">
				<label for="instapass-command"><strong><?php echo esc_html__( 'Pricing command', 'instapass-automation' ); ?></strong></label>
				<p><input id="instapass-command" name="command" type="text" class="regular-text" maxlength="500" required autocomplete="off" placeholder="Give chatgpt-plus 25% off"></p>
				<p class="description"><?php echo esc_html__( 'Examples: “Set chatgpt-plus sale price to 14.99” or “Give chatgpt-plus 25% off”. Use an exact product name or SKU.', 'instapass-automation' ); ?></p>
				<p><button type="submit" class="button button-primary" id="instapass-preview-submit"><?php echo esc_html__( 'Preview change', 'instapass-automation' ); ?></button></p>
			</form>
			<div id="instapass-preview-result" class="instapass-preview-result" aria-live="polite" hidden></div>
			<hr>
			<h2><?php echo esc_html__( 'Recent price operations', 'instapass-automation' ); ?></h2>
			<div id="instapass-operation-history" aria-live="polite"><?php echo esc_html__( 'Loading history…', 'instapass-automation' ); ?></div>
		</div>
		<?php
	}

	public static function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/commands/preview',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'preview_command' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'args'                => array(
					'command' => array(
						'required'          => true,
						'type'              => 'string',
						'maxLength'         => 500,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
		register_rest_route(
			self::REST_NAMESPACE,
			'/commands/confirm',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'confirm_operation' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'args'                => array(
					'operation_id' => array( 'required' => true, 'type' => 'string', 'maxLength' => 36 ),
				),
			)
		);
		register_rest_route(
			self::REST_NAMESPACE,
			'/commands/undo',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'undo_operation' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'args'                => array(
					'operation_id' => array( 'required' => true, 'type' => 'string', 'maxLength' => 36 ),
				),
			)
		);
		register_rest_route(
			self::REST_NAMESPACE,
			'/commands/history',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'operation_history' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	public static function can_manage() {
		return current_user_can( 'manage_woocommerce' );
	}

	public static function preview_command( WP_REST_Request $request ) {
		if ( ! function_exists( 'wc_get_product' ) || ! function_exists( 'instapass_price_values' ) || ! function_exists( 'instapass_price_fingerprint' ) ) {
			return new WP_Error( 'instapass_preview_unavailable', __( 'WooCommerce and the Instapass pricing helpers must be active.', 'instapass-automation' ), array( 'status' => 503 ) );
		}

		$command = trim( (string) $request->get_param( 'command' ) );
		$parsed  = self::parse_command( $command );
		if ( is_wp_error( $parsed ) ) {
			return $parsed;
		}

		$product = self::find_exact_product( $parsed['target'] );
		if ( is_wp_error( $product ) ) {
			return $product;
		}
		if ( ! $product->is_type( array( 'simple', 'external', 'variation' ) ) ) {
			return new WP_Error( 'unsupported_product_type', __( 'Choose a simple product or a specific variation SKU.', 'instapass-automation' ), array( 'status' => 400 ) );
		}
		if ( ! current_user_can( 'edit_post', $product->get_id() ) ) {
			return new WP_Error( 'product_forbidden', __( 'You do not have permission to edit this product.', 'instapass-automation' ), array( 'status' => 403 ) );
		}

		$regular = $product->get_regular_price( 'edit' );
		$sale    = $product->get_sale_price( 'edit' );
		$mode    = $parsed['operation'];
		$values  = instapass_price_values(
			'regular' === $mode ? $parsed['value'] : $regular,
			'sale' === $mode ? $parsed['value'] : $sale,
			'discount' === $mode ? $parsed['value'] : '',
			'discount' === $mode ? 'discount' : 'sale',
			'discount' === $mode && 'rounded' === $parsed['rounding']
		);

		if ( is_wp_error( $values ) ) {
			return new WP_Error( 'invalid_price_change', $values->get_error_message(), array( 'status' => 400 ) );
		}

		$new_regular = (float) $values['regular'];
		$new_sale    = '' === $values['sale'] ? null : (float) $values['sale'];
		if ( $new_regular <= 0 || ( null !== $new_sale && ( $new_sale <= 0 || $new_sale >= $new_regular ) ) ) {
			return new WP_Error( 'invalid_price_change', __( 'The proposed prices must be positive, with any sale price below the regular price.', 'instapass-automation' ), array( 'status' => 400 ) );
		}

		$currency     = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '';
		$now          = current_time( 'timestamp', true );
		$operation_id = wp_generate_uuid4();
		$expires_at   = gmdate( 'Y-m-d H:i:s', $now + self::PREVIEW_TTL );
		$preview      = array(
				'schema_version'           => 1,
				'state'                    => 'pending_confirmation',
				'operation_id'             => $operation_id,
				'expires_at'               => $expires_at,
				'requires_owner_review'    => true,
				'command'                  => $command,
				'currency'                 => $currency,
				'product_id'               => $product->get_id(),
				'sku'                      => $product->get_sku(),
				'name'                     => $product->get_name(),
				'fingerprint'              => instapass_price_fingerprint( $product ),
				'before'                   => array(
					'regular_price' => (string) $regular,
					'sale_price'    => (string) $sale,
				),
				'after'                    => array(
					'regular_price' => (string) $values['regular'],
					'sale_price'    => (string) $values['sale'],
				),
				'actual_discount_percent' => null === $new_sale ? 0 : round( ( 1 - $new_sale / $new_regular ) * 100, 2 ),
				'sale_timing'              => 'preserve_existing_dates',
			);
		$audit = self::audit_entry( 'preview_created', get_current_user_id(), array( 'fingerprint' => $preview['fingerprint'] ) );
		global $wpdb;
		$inserted = $wpdb->insert(
			self::table_name(),
			array(
				'operation_id' => $operation_id,
				'user_id'      => get_current_user_id(),
				'product_id'   => $product->get_id(),
				'payload'      => wp_json_encode( $preview ),
				'state'        => 'pending',
				'expires_at'   => $expires_at,
				'created_at'   => gmdate( 'Y-m-d H:i:s', $now ),
				'audit_log'    => wp_json_encode( array( $audit ) ),
			),
			array( '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);
		if ( false === $inserted ) {
			return new WP_Error( 'preview_persist_failed', __( 'The preview could not be saved. No product was changed.', 'instapass-automation' ), array( 'status' => 500 ) );
		}
		return rest_ensure_response( $preview );
	}

	public static function confirm_operation( WP_REST_Request $request ) {
		$operation = self::get_operation( $request->get_param( 'operation_id' ) );
		if ( is_wp_error( $operation ) ) {
			return $operation;
		}
		if ( 'applied' === $operation['state'] ) {
			return rest_ensure_response( array( 'state' => 'already_applied', 'operation_id' => $operation['operation_id'] ) );
		}
		if ( 'pending' !== $operation['state'] ) {
			return new WP_Error( 'operation_not_pending', __( 'This preview is no longer available for confirmation.', 'instapass-automation' ), array( 'status' => 409 ) );
		}
		if ( strtotime( $operation['expires_at'] . ' UTC' ) <= current_time( 'timestamp', true ) ) {
			self::transition( $operation, 'pending', 'expired', 'preview_expired' );
			return new WP_Error( 'preview_expired', __( 'This preview expired. Create a new one and review it again.', 'instapass-automation' ), array( 'status' => 410 ) );
		}
		if ( ! self::claim_operation( $operation, 'pending', 'applying' ) ) {
			return new WP_Error( 'operation_claimed', __( 'This preview is already being processed or is no longer pending.', 'instapass-automation' ), array( 'status' => 409 ) );
		}
		if ( ! self::acquire_product_lock( (int) $operation['product_id'] ) ) {
			self::transition( $operation, 'applying', 'pending', 'product_busy_retry_allowed' );
			return new WP_Error( 'product_busy', __( 'This product could not be reserved for a price operation. No price was changed; check the operation history before retrying.', 'instapass-automation' ), array( 'status' => 409 ) );
		}

		try {
			$product = wc_get_product( (int) $operation['product_id'] );
			$payload = json_decode( $operation['payload'], true );
			if ( ! is_array( $payload ) || ! self::valid_payload( $payload, $operation ) ) {
				self::transition( $operation, 'applying', 'manual_review', 'invalid_stored_preview' );
				return new WP_Error( 'invalid_stored_preview', __( 'The saved preview data is invalid. No price was changed; inspect the operation.', 'instapass-automation' ), array( 'status' => 500 ) );
			}
			if ( ! $product || ! current_user_can( 'edit_post', (int) $operation['product_id'] ) || ! $product->is_type( array( 'simple', 'external', 'variation' ) ) ) {
				self::transition( $operation, 'applying', 'manual_review', 'apply_refused', array( 'reason' => 'product_or_permission_unavailable' ) );
				return new WP_Error( 'product_unavailable', __( 'The product cannot be edited. The operation is stopped for review.', 'instapass-automation' ), array( 'status' => 403 ) );
			}
			if ( ! hash_equals( $payload['fingerprint'], instapass_price_fingerprint( $product ) ) ) {
				self::transition( $operation, 'applying', 'stale', 'stale_preview', array( 'current_fingerprint' => instapass_price_fingerprint( $product ) ) );
				return new WP_Error( 'stale_preview', __( 'The product changed after this preview. Nothing was applied; create and review a fresh preview.', 'instapass-automation' ), array( 'status' => 409 ) );
			}
			self::write_product_prices( $product, $payload['after'] );
			$saved = wc_get_product( (int) $operation['product_id'] );
			if ( ! self::prices_match( $saved, $payload['after'] ) ) {
				throw new RuntimeException( 'Saved prices could not be verified.' );
			}
			$after_fingerprint = instapass_price_fingerprint( $saved );
			$updated           = self::complete_operation( $operation, 'applying', 'applied', 'applied', array( 'fingerprint_after' => $after_fingerprint, 'confirmed_at' => current_time( 'mysql', true ) ), array( 'fingerprint_after' => $after_fingerprint ) );
			if ( ! $updated ) {
				self::transition( $operation, 'applying', 'manual_review', 'audit_finalize_failed', array( 'fingerprint_after' => $after_fingerprint ) );
				return new WP_Error( 'audit_update_failed', __( 'The price was saved, but its audit status could not be finalized. Inspect this operation before retrying.', 'instapass-automation' ), array( 'status' => 500 ) );
			}
			return rest_ensure_response( array( 'state' => 'applied', 'operation_id' => $operation['operation_id'], 'name' => $payload['name'], 'after' => $payload['after'] ) );
		} catch ( Throwable $error ) {
			$current = wc_get_product( (int) $operation['product_id'] );
			self::transition( $operation, 'applying', 'manual_review', 'apply_failed', array( 'error' => 'Product save or verification failed.', 'current_fingerprint' => $current ? instapass_price_fingerprint( $current ) : '' ) );
			return new WP_Error( 'apply_needs_review', __( 'The operation may have partially saved. It is locked for manual review and will not be retried automatically.', 'instapass-automation' ), array( 'status' => 500 ) );
		} finally {
			self::release_product_lock( (int) $operation['product_id'] );
		}
	}

	public static function undo_operation( WP_REST_Request $request ) {
		$operation = self::get_operation( $request->get_param( 'operation_id' ) );
		if ( is_wp_error( $operation ) ) {
			return $operation;
		}
		if ( 'undone' === $operation['state'] ) {
			return rest_ensure_response( array( 'state' => 'already_undone', 'operation_id' => $operation['operation_id'] ) );
		}
		if ( 'applied' !== $operation['state'] ) {
			return new WP_Error( 'undo_unavailable', __( 'Only an applied operation can be undone.', 'instapass-automation' ), array( 'status' => 409 ) );
		}
		$payload = json_decode( $operation['payload'], true );
		if ( ! is_array( $payload ) || ! self::valid_payload( $payload, $operation ) ) {
			return new WP_Error( 'invalid_stored_preview', __( 'The saved operation data is invalid. No undo was attempted.', 'instapass-automation' ), array( 'status' => 500 ) );
		}
		$product = wc_get_product( (int) $operation['product_id'] );
		if ( ! $product || ! is_array( $payload ) || ! current_user_can( 'edit_post', (int) $operation['product_id'] ) || ! $product->is_type( array( 'simple', 'external', 'variation' ) ) ) {
			return new WP_Error( 'product_unavailable', __( 'The product cannot be edited by this user.', 'instapass-automation' ), array( 'status' => 403 ) );
		}
		if ( '' === $operation['fingerprint_after'] || ! hash_equals( $operation['fingerprint_after'], instapass_price_fingerprint( $product ) ) ) {
			self::transition( $operation, 'applied', 'undo_stale', 'undo_refused_stale_product', array( 'current_fingerprint' => instapass_price_fingerprint( $product ) ) );
			return new WP_Error( 'undo_stale', __( 'The product changed after this operation. Undo was refused to protect the newer values.', 'instapass-automation' ), array( 'status' => 409 ) );
		}
		if ( ! self::claim_operation( $operation, 'applied', 'undoing' ) ) {
			return new WP_Error( 'undo_claimed', __( 'This undo is already being processed or is no longer available.', 'instapass-automation' ), array( 'status' => 409 ) );
		}
		if ( ! self::acquire_product_lock( (int) $operation['product_id'] ) ) {
			self::transition( $operation, 'undoing', 'applied', 'product_busy_undo_retry_allowed' );
			return new WP_Error( 'product_busy', __( 'This product could not be reserved for undo. No undo was applied; check the operation history before retrying.', 'instapass-automation' ), array( 'status' => 409 ) );
		}
		try {
			$product = wc_get_product( (int) $operation['product_id'] );
			if ( ! $product || ! $product->is_type( array( 'simple', 'external', 'variation' ) ) || ! hash_equals( $operation['fingerprint_after'], instapass_price_fingerprint( $product ) ) ) {
				self::transition( $operation, 'undoing', 'undo_stale', 'undo_refused_stale_product', array( 'current_fingerprint' => $product ? instapass_price_fingerprint( $product ) : '' ) );
				return new WP_Error( 'undo_stale', __( 'The product changed during undo confirmation. No undo was applied.', 'instapass-automation' ), array( 'status' => 409 ) );
			}
			self::write_product_prices( $product, $payload['before'] );
			$restored = wc_get_product( (int) $operation['product_id'] );
			if ( ! self::prices_match( $restored, $payload['before'] ) ) {
				throw new RuntimeException( 'Restored prices could not be verified.' );
			}
			$fingerprint = instapass_price_fingerprint( $restored );
			if ( ! self::complete_operation( $operation, 'undoing', 'undone', 'undone', array( 'undone_at' => current_time( 'mysql', true ) ), array( 'fingerprint_undone' => $fingerprint ) ) ) {
				self::transition( $operation, 'undoing', 'manual_review', 'undo_audit_finalize_failed', array( 'fingerprint_undone' => $fingerprint ) );
				return new WP_Error( 'audit_update_failed', __( 'Prices were restored, but the audit status could not be finalized. Inspect this operation.', 'instapass-automation' ), array( 'status' => 500 ) );
			}
			return rest_ensure_response( array( 'state' => 'undone', 'operation_id' => $operation['operation_id'], 'name' => $payload['name'], 'restored' => $payload['before'] ) );
		} catch ( Throwable $error ) {
			$current = wc_get_product( (int) $operation['product_id'] );
			self::transition( $operation, 'undoing', 'manual_review', 'undo_failed', array( 'error' => 'Undo save or verification failed.', 'current_fingerprint' => $current ? instapass_price_fingerprint( $current ) : '' ) );
			return new WP_Error( 'undo_needs_review', __( 'The undo may have partially saved. It is locked for manual review and will not be retried automatically.', 'instapass-automation' ), array( 'status' => 500 ) );
		} finally {
			self::release_product_lock( (int) $operation['product_id'] );
		}
	}

	public static function operation_history() {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE user_id = %d ORDER BY created_at DESC LIMIT 30', get_current_user_id() ),
			ARRAY_A
		);
		$history = array();
		foreach ( (array) $rows as $row ) {
			$payload = json_decode( $row['payload'], true );
			$audit   = json_decode( $row['audit_log'], true );
			if ( ! is_array( $payload ) ) {
				$history[] = array(
					'operation_id' => $row['operation_id'],
					'state'        => 'manual_review' === $row['state'] ? $row['state'] : 'corrupt_record',
					'command'      => __( 'Stored operation data is unreadable.', 'instapass-automation' ),
					'name'         => __( 'Needs manual review', 'instapass-automation' ),
					'sku'          => '',
					'before'       => array( 'regular_price' => '', 'sale_price' => '' ),
					'after'        => array( 'regular_price' => '', 'sale_price' => '' ),
					'created_at'   => $row['created_at'],
					'confirmed_at' => $row['confirmed_at'],
					'undone_at'    => $row['undone_at'],
					'audit_log'    => is_array( $audit ) ? $audit : array(),
				);
				continue;
			}
			$history[] = array(
				'operation_id' => $row['operation_id'],
				'state'        => $row['state'],
				'command'      => $payload['command'],
				'name'         => $payload['name'],
				'sku'          => $payload['sku'],
				'before'       => $payload['before'],
				'after'        => $payload['after'],
				'created_at'   => $row['created_at'],
				'confirmed_at' => $row['confirmed_at'],
				'undone_at'    => $row['undone_at'],
				'audit_log'    => is_array( $audit ) ? $audit : array(),
			);
		}
		return rest_ensure_response( $history );
	}

	private static function get_operation( $operation_id ) {
		if ( ! is_string( $operation_id ) || ! preg_match( '/^[a-f0-9-]{36}$/i', $operation_id ) ) {
			return new WP_Error( 'invalid_operation_id', __( 'A valid operation ID is required.', 'instapass-automation' ), array( 'status' => 400 ) );
		}
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE operation_id = %s AND user_id = %d', $operation_id, get_current_user_id() ),
			ARRAY_A
		);
		if ( ! is_array( $row ) ) {
			return new WP_Error( 'operation_not_found', __( 'No operation was found for this account.', 'instapass-automation' ), array( 'status' => 404 ) );
		}
		return $row;
	}

	private static function valid_payload( $payload, $operation ) {
		if ( ! isset( $payload['operation_id'], $payload['product_id'], $payload['fingerprint'], $payload['before'], $payload['after'], $payload['command'] )
			|| $payload['operation_id'] !== $operation['operation_id']
			|| (int) $payload['product_id'] !== (int) $operation['product_id']
			|| ! is_string( $payload['fingerprint'] )
			|| ! preg_match( '/^[a-f0-9]{64}$/i', $payload['fingerprint'] )
			|| ! is_string( $payload['command'] )
			|| strlen( $payload['command'] ) > 500
		) {
			return false;
		}
		foreach ( array( 'before', 'after' ) as $side ) {
			if ( ! is_array( $payload[$side] ) || ! array_key_exists( 'regular_price', $payload[$side] ) || ! array_key_exists( 'sale_price', $payload[$side] ) ) {
				return false;
			}
			foreach ( array( 'regular_price', 'sale_price' ) as $field ) {
				$value = $payload[$side][$field];
				if ( ! is_scalar( $value ) || ( '' !== (string) $value && ( ! is_numeric( $value ) || ! is_finite( (float) $value ) || (float) $value < 0 ) ) ) {
					return false;
				}
			}
		}
		$regular = (float) $payload['after']['regular_price'];
		$sale    = $payload['after']['sale_price'];
		return $regular > 0 && ( '' === (string) $sale || ( (float) $sale > 0 && (float) $sale < $regular ) );
	}

	private static function claim_operation( $operation, $expected_state, $next_state ) {
		global $wpdb;
		$expiry_clause = 'pending' === $expected_state ? ' AND expires_at > UTC_TIMESTAMP()' : '';
		$sql = $wpdb->prepare(
			'UPDATE ' . self::table_name() . ' SET state = %s WHERE operation_id = %s AND user_id = %d AND state = %s' . $expiry_clause,
			$next_state,
			$operation['operation_id'],
			get_current_user_id(),
			$expected_state
		);
		return 1 === (int) $wpdb->query( $sql );
	}

	private static function product_lock_name( $product_id ) {
		global $wpdb;
		$blog_id = function_exists( 'get_current_blog_id' ) ? (int) get_current_blog_id() : 1;
		$site    = ( defined( 'DB_NAME' ) ? DB_NAME : '' ) . $wpdb->prefix . ':' . $blog_id . ':' . (int) $product_id;
		return 'ipa-' . substr( hash( 'sha256', $site ), 0, 48 );
	}

	private static function acquire_product_lock( $product_id ) {
		global $wpdb;
		$lock = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', self::product_lock_name( $product_id ) ) );
		return '1' === (string) $lock;
	}

	private static function release_product_lock( $product_id ) {
		global $wpdb;
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', self::product_lock_name( $product_id ) ) );
	}

	private static function transition( $operation, $expected_state, $next_state, $event, $details = array() ) {
		global $wpdb;
		$audit = json_decode( $operation['audit_log'], true );
		$audit = is_array( $audit ) ? $audit : array();
		$audit[] = self::audit_entry( $event, get_current_user_id(), $details );
		return 1 === (int) $wpdb->update(
			self::table_name(),
			array( 'state' => $next_state, 'audit_log' => wp_json_encode( $audit ) ),
			array( 'operation_id' => $operation['operation_id'], 'user_id' => get_current_user_id(), 'state' => $expected_state ),
			array( '%s', '%s' ),
			array( '%s', '%d', '%s' )
		);
	}

	private static function complete_operation( $operation, $expected_state, $next_state, $event, $extra_fields, $details ) {
		global $wpdb;
		$audit = json_decode( $operation['audit_log'], true );
		$audit = is_array( $audit ) ? $audit : array();
		$audit[] = self::audit_entry( $event, get_current_user_id(), $details );
		$fields = array_merge( $extra_fields, array( 'state' => $next_state, 'audit_log' => wp_json_encode( $audit ) ) );
		$formats = array_fill( 0, count( $fields ), '%s' );
		return 1 === (int) $wpdb->update(
			self::table_name(),
			$fields,
			array( 'operation_id' => $operation['operation_id'], 'user_id' => get_current_user_id(), 'state' => $expected_state ),
			$formats,
			array( '%s', '%d', '%s' )
		);
	}

	private static function audit_entry( $event, $actor_id, $details = array() ) {
		return array( 'event' => $event, 'actor_id' => (int) $actor_id, 'at_utc' => current_time( 'mysql', true ), 'details' => $details );
	}

	private static function write_product_prices( $product, $prices ) {
		if ( ! is_array( $prices ) || ! isset( $prices['regular_price'], $prices['sale_price'] ) ) {
			throw new RuntimeException( 'Stored price proposal is invalid.' );
		}
		$regular = (string) $prices['regular_price'];
		$sale    = (string) $prices['sale_price'];
		$product->set_regular_price( $regular );
		$product->set_sale_price( $sale );
		$product->set_price( '' !== $sale && $product->is_on_sale( 'edit' ) ? $sale : $regular );
		if ( ! $product->save() ) {
			throw new RuntimeException( 'WooCommerce did not confirm the product save.' );
		}
		if ( $product->is_type( 'variation' ) && class_exists( 'WC_Product_Variable' ) ) {
			WC_Product_Variable::sync( $product->get_parent_id() );
		}
	}

	private static function prices_match( $product, $prices ) {
		if ( ! $product || ! is_array( $prices ) ) {
			return false;
		}
		$decimals = function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2;
		return wc_format_decimal( $product->get_regular_price( 'edit' ), $decimals ) === wc_format_decimal( $prices['regular_price'], $decimals )
			&& wc_format_decimal( $product->get_sale_price( 'edit' ), $decimals ) === wc_format_decimal( $prices['sale_price'], $decimals );
	}

	private static function parse_command( $command ) {
		if ( '' === $command || strlen( $command ) > 500 ) {
			return new WP_Error( 'invalid_command', __( 'Enter a command of at most 500 characters.', 'instapass-automation' ), array( 'status' => 400 ) );
		}

		if ( preg_match( '/^set (.+?) (regular|sale) price to (?:\$)?([0-9]+(?:\.[0-9]{1,2})?)$/i', $command, $match ) ) {
			return array(
				'target'    => trim( $match[1] ),
				'operation' => strtolower( $match[2] ),
				'value'     => $match[3],
				'rounding'  => 'exact',
			);
		}

		if ( preg_match( '/^give (.+?) ([0-9]+(?:\.[0-9]{1,2})?)% (?:off|discount)(?: (exact|rounded))?$/i', $command, $match ) ) {
			$percent = (float) $match[2];
			if ( $percent <= 0 || $percent >= 100 ) {
				return new WP_Error( 'invalid_discount', __( 'Discount must be greater than 0% and less than 100%.', 'instapass-automation' ), array( 'status' => 400 ) );
			}
			return array(
				'target'    => trim( $match[1] ),
				'operation' => 'discount',
				'value'     => (string) $percent,
				'rounding'  => isset( $match[3] ) && '' !== $match[3] ? strtolower( $match[3] ) : 'rounded',
			);
		}

		return new WP_Error( 'unsupported_command', __( 'Use: Set PRODUCT regular/sale price to AMOUNT; or Give PRODUCT N% off exact/rounded.', 'instapass-automation' ), array( 'status' => 400 ) );
	}

	private static function find_exact_product( $target ) {
		$target = trim( wp_strip_all_tags( $target ) );
		if ( '' === $target ) {
			return new WP_Error( 'invalid_target', __( 'Enter an exact product name or SKU.', 'instapass-automation' ), array( 'status' => 400 ) );
		}
		$id     = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( $target ) : 0;
		if ( $id ) {
			$product = wc_get_product( $id );
			return $product ? $product : new WP_Error( 'product_not_found', __( 'No matching product was found.', 'instapass-automation' ), array( 'status' => 404 ) );
		}

		$matches = wc_get_products(
			array(
				'limit'   => 100,
				'status'  => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'return'  => 'objects',
				's'       => $target,
			)
		);
		$exact = array_values(
			array_filter(
				$matches,
				static function ( $product ) use ( $target ) {
					return 0 === strcasecmp( trim( $product->get_name() ), $target );
				}
			)
		);

		if ( 0 === count( $exact ) ) {
			return new WP_Error( 'product_not_found', __( 'No exact product name or SKU match. Use the full name or unique SKU.', 'instapass-automation' ), array( 'status' => 404 ) );
		}
		if ( 1 !== count( $exact ) ) {
			return new WP_Error( 'ambiguous_product', __( 'More than one product has that name. Use a unique SKU.', 'instapass-automation' ), array( 'status' => 409 ) );
		}
		return $exact[0];
	}
}

add_action( 'rest_api_init', array( 'Instapass_Automation_Preview', 'register_routes' ) );
add_action( 'admin_menu', array( 'Instapass_Automation_Preview', 'register_admin_page' ) );
add_action( 'admin_enqueue_scripts', array( 'Instapass_Automation_Preview', 'enqueue_admin_assets' ) );
register_activation_hook( __FILE__, array( 'Instapass_Automation_Preview', 'activate' ) );
