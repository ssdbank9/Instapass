<?php
/**
 * Plugin Name: Instapass Automation (Preview)
 * Description: Authenticated, read-only WooCommerce price-command previews. No product writes or external calls.
 * Version: 0.1.0
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Text Domain: instapass-automation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * First automation slice: owner-only price previews. This plugin deliberately
 * registers no apply, coupon, stock, order, AI, or n8n endpoints.
 */
final class Instapass_Automation_Preview {
	const REST_NAMESPACE = 'instapass-automation/v1';
	const PAGE_SLUG      = 'instapass-pricing-preview';

	public static function register_admin_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Instapass Pricing Preview', 'instapass-automation' ),
			__( 'Pricing Preview', 'instapass-automation' ),
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
		wp_enqueue_script( 'instapass-automation-preview', $asset_url . 'admin.js', array(), '0.2.0', true );
		wp_localize_script(
			'instapass-automation-preview',
			'InstapassAutomationPreview',
			array(
				'restUrl' => esc_url_raw( rest_url( self::REST_NAMESPACE . '/commands/preview' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
		wp_enqueue_style( 'instapass-automation-preview', $asset_url . 'admin.css', array(), '0.2.0' );
	}

	public static function render_admin_page() {
		if ( ! self::can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'instapass-automation' ) );
		}
		?>
		<div class="wrap instapass-automation">
			<h1><?php echo esc_html__( 'Pricing and Discounts Preview', 'instapass-automation' ); ?></h1>
			<p><?php echo esc_html__( 'Describe one price change. Review the exact values before anything is changed.', 'instapass-automation' ); ?></p>
			<div class="notice notice-info inline"><p><?php echo esc_html__( 'Preview only: this screen does not save or change products.', 'instapass-automation' ); ?></p></div>
			<form id="instapass-preview-form">
				<label for="instapass-command"><strong><?php echo esc_html__( 'Pricing command', 'instapass-automation' ); ?></strong></label>
				<p><input id="instapass-command" name="command" type="text" class="regular-text" maxlength="500" required autocomplete="off" placeholder="Give chatgpt-plus 25% off"></p>
				<p class="description"><?php echo esc_html__( 'Examples: “Set chatgpt-plus sale price to 14.99” or “Give chatgpt-plus 25% off”. Use an exact product name or SKU.', 'instapass-automation' ); ?></p>
				<p><button type="submit" class="button button-primary" id="instapass-preview-submit"><?php echo esc_html__( 'Preview change', 'instapass-automation' ); ?></button></p>
			</form>
			<div id="instapass-preview-result" class="instapass-preview-result" aria-live="polite" hidden></div>
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

		$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '';
		return rest_ensure_response(
			array(
				'schema_version'           => 1,
				'state'                    => 'preview_only',
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
			)
		);
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
