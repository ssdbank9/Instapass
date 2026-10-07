<?php
/**
 * Instapass theme functions.
 *
 * @package Instapass
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'INSTAPASS_VERSION', '1.9.0' );

require_once get_template_directory() . '/inc/pricing.php';
require_once get_template_directory() . '/inc/product-art.php';
require_once get_template_directory() . '/inc/catalogue.php';
require_once get_template_directory() . '/inc/product-features.php';
require_once get_template_directory() . '/inc/promotions.php';
require_once get_template_directory() . '/inc/guest-checkout.php';
require_once get_template_directory() . '/inc/manual-payments.php';
require_once get_template_directory() . '/inc/payment-confirmation.php';

/**
 * Theme supports.
 */
function instapass_setup() {
	// Translations (drop .mo files into /languages).
	load_theme_textdomain( 'instapass', get_template_directory() . '/languages' );

	// Core block theme niceties.
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
	add_editor_style( 'assets/studio.css' );
	add_editor_style( 'assets/product-tiles.css' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// WooCommerce.
	// 600px card thumbnails (cards are ~270px wide, so this is sharp on 2x screens);
	// the single product image keeps WooCommerce's default 600px.
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 600,
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'instapass_setup' );

/**
 * Products without a photo: a transparent key-and-bolt mark instead of
 * WooCommerce's grey box, so the card shows the scheme's accent gradient
 * (see .wc-block-components-product-image in style.css). Upload a real
 * image on the product and this is never used.
 */
function instapass_placeholder_img_src( $src ) {
	return get_template_directory_uri() . '/assets/icons/icon-512.png';
}
add_filter( 'woocommerce_placeholder_img_src', 'instapass_placeholder_img_src' );

/**
 * Favicons from assets/icons/ (the Instapass mark in the default scheme's
 * colours), used only while no Site Icon is set. Upload your own under
 * Appearance > Editor > Styles > (site) or Settings > General > Site Icon and
 * these are skipped automatically.
 */
function instapass_fallback_icons() {
	if ( has_site_icon() ) {
		return;
	}
	$base = get_template_directory_uri() . '/assets/icons/';
	echo '<link rel="icon" href="' . esc_url( $base . 'favicon.ico' ) . '" sizes="32x32">' . "\n";
	echo '<link rel="icon" href="' . esc_url( $base . 'icon-32.png' ) . '" sizes="32x32" type="image/png">' . "\n";
	echo '<link rel="icon" href="' . esc_url( $base . 'icon-16.png' ) . '" sizes="16x16" type="image/png">' . "\n";
	echo '<link rel="icon" href="' . esc_url( $base . 'icon-512.png' ) . '" sizes="512x512" type="image/png">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $base . 'apple-touch-icon.png' ) . '">' . "\n";
}
add_action( 'wp_head', 'instapass_fallback_icons', 2 );
add_action( 'login_head', 'instapass_fallback_icons', 2 );

/**
 * Front-end stylesheet.
 */
function instapass_enqueue_styles() {
	wp_enqueue_style( 'instapass-style', get_stylesheet_uri(), array(), INSTAPASS_VERSION );
	wp_enqueue_style( 'instapass-studio', get_template_directory_uri() . '/assets/studio.css', array( 'instapass-style' ), INSTAPASS_VERSION );
	wp_enqueue_style( 'instapass-product-tiles', get_template_directory_uri() . '/assets/product-tiles.css', array( 'instapass-studio' ), INSTAPASS_VERSION );
}
add_action( 'wp_enqueue_scripts', 'instapass_enqueue_styles' );

/** Resolve storefront links from WooCommerce instead of assuming its page slug. */
function instapass_shop_url() {
	$url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
	return $url && -1 !== $url ? $url : home_url( '/shop/' );
}

/** An unconfigured category falls back to the catalogue rather than a 404. */
function instapass_category_url( $slug ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$url = get_term_link( $term );
		if ( ! is_wp_error( $url ) ) { return $url; }
	}
	return instapass_shop_url();
}

function instapass_preload_hero_art() {
	if ( is_front_page() ) {
		echo '<link rel="preload" as="image" href="' . esc_url( get_template_directory_uri() . '/assets/img/aurora-key.webp' ) . '" fetchpriority="high">' . "\n";
	}
}
add_action( 'wp_head', 'instapass_preload_hero_art', 2 );

/**
 * Block pattern category. Patterns themselves live in /patterns and are
 * auto-registered by WordPress (block themes since 6.0); the category is
 * registered at priority 9 so it exists before they load.
 */
function instapass_register_pattern_category() {
	register_block_pattern_category(
		'instapass',
		array(
			'label'       => __( 'Instapass', 'instapass' ),
			'description' => __( 'Home page sections and starter pages for the Instapass shop.', 'instapass' ),
		)
	);
}
add_action( 'init', 'instapass_register_pattern_category', 9 );

/**
 * Explicitly point WordPress at the patterns directory as well, for hosts
 * that disable automatic theme pattern loading.
 */
function instapass_register_patterns_dir() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}
	$dir = get_template_directory() . '/patterns/';
	foreach ( glob( $dir . '*.php' ) as $file ) {
		$headers = get_file_data(
			$file,
			array(
				'title'      => 'Title',
				'slug'       => 'Slug',
				'categories' => 'Categories',
				'blockTypes' => 'Block Types',
				'postTypes'  => 'Post Types',
				'inserter'   => 'Inserter',
			)
		);
		if ( empty( $headers['slug'] ) || WP_Block_Patterns_Registry::get_instance()->is_registered( $headers['slug'] ) ) {
			continue;
		}
		ob_start();
		include $file;
		$content = ob_get_clean();

		$args = array(
			'title'      => $headers['title'],
			'content'    => $content,
			'categories' => array_filter( array_map( 'trim', explode( ',', $headers['categories'] ) ) ),
		);
		if ( ! empty( $headers['blockTypes'] ) ) {
			$args['blockTypes'] = array_filter( array_map( 'trim', explode( ',', $headers['blockTypes'] ) ) );
		}
		if ( ! empty( $headers['postTypes'] ) ) {
			$args['postTypes'] = array_filter( array_map( 'trim', explode( ',', $headers['postTypes'] ) ) );
		}
		if ( 'no' === strtolower( trim( $headers['inserter'] ) ) ) {
			$args['inserter'] = false;
		}
		register_block_pattern( $headers['slug'], $args );
	}
}
add_action( 'init', 'instapass_register_patterns_dir', 11 );

/**
 * "Buy now" instead of "Add to cart" for purchasable simple products.
 * Digital keys are bought, not collected.
 *
 * @param string          $text    Button text.
 * @param WC_Product|null $product Product.
 * @return string
 */
function instapass_buy_now_text( $text, $product = null ) {
	if ( $product instanceof WC_Product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
		return __( 'Buy now', 'instapass' );
	}
	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'instapass_buy_now_text', 10, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'instapass_buy_now_text', 10, 2 );

/**
 * Block styles: an "Outline" style exists in core; add a compact "Pill tag"
 * style for paragraphs used as labels.
 */
function instapass_register_block_styles() {
	register_block_style(
		'core/paragraph',
		array(
			'name'         => 'ip-tag',
			'label'        => __( 'Orange tag', 'instapass' ),
			'inline_style' => '.is-style-ip-tag{display:inline-block;padding:4px 10px;border-radius:var(--wp--custom--radius-small,6px);background:var(--wp--preset--color--accent-soft);color:var(--wp--preset--color--accent-ink);font-weight:700;}',
		)
	);
}
add_action( 'init', 'instapass_register_block_styles' );

/* -------------------------------------------------------------------------
 * Live counters and product signals. Real data only: nothing here invents
 * a number. See README sections 8 and 9.
 * ---------------------------------------------------------------------- */

/**
 * Total quantity of items on completed orders, cached for one hour, plus an
 * optional starting offset for shops migrating sales history from elsewhere.
 *
 * Offset: add_filter( 'instapass_delivered_offset', fn() => 1200 );
 * or:     wp option update instapass_delivered_offset 1200
 *
 * @return int
 */
function instapass_delivered_count() {
	$count = get_transient( 'instapass_delivered_count' );
	if ( false === $count ) {
		global $wpdb;
		$count = 0;
		if ( function_exists( 'WC' ) ) {
			$suppress = $wpdb->suppress_errors();
			$lookup   = $wpdb->prefix . 'wc_order_product_lookup';
			$stats    = $wpdb->prefix . 'wc_order_stats';
			$sum      = $wpdb->get_var( "SELECT SUM( l.product_qty ) FROM {$lookup} l INNER JOIN {$stats} s ON s.order_id = l.order_id WHERE s.status = 'wc-completed'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->suppress_errors( $suppress );
			if ( null === $sum && function_exists( 'wc_get_orders' ) ) {
				// Analytics tables unavailable: fall back to the most recent 500 completed orders.
				$sum = 0;
				foreach ( wc_get_orders( array( 'status' => 'completed', 'limit' => 500 ) ) as $order ) {
					foreach ( $order->get_items() as $item ) {
						$sum += (int) $item->get_quantity();
					}
				}
			}
			$count = (int) $sum;
		}
		set_transient( 'instapass_delivered_count', $count, HOUR_IN_SECONDS );
	}
	$offset = (int) apply_filters( 'instapass_delivered_offset', (int) get_option( 'instapass_delivered_offset', 0 ) );
	return max( 0, (int) $count + $offset );
}

/**
 * Refresh the cached count as soon as an order completes.
 */
function instapass_flush_delivered_count() {
	delete_transient( 'instapass_delivered_count' );
}
add_action( 'woocommerce_order_status_completed', 'instapass_flush_delivered_count' );

/**
 * Shortcode fallback for classic content: [instapass_delivered].
 */
function instapass_delivered_shortcode() {
	$n = instapass_delivered_count();
	return $n > 0 ? '<span class="ip-delivered-count">' . esc_html( number_format_i18n( $n ) ) . '</span>' : '';
}
add_shortcode( 'instapass_delivered', 'instapass_delivered_shortcode' );

/**
 * Block: instapass/delivered-count. Outputs nothing until the first sale, and
 * the stat strip hides that column with CSS when the block is empty.
 *
 * @return string
 */
function instapass_render_delivered_count() {
	$n = instapass_delivered_count();
	if ( $n < 1 ) {
		return '';
	}
	return sprintf( '<span %s>%s</span>', get_block_wrapper_attributes( array( 'class' => 'ip-delivered-count' ) ), esc_html( number_format_i18n( $n ) ) );
}

/**
 * Block: instapass/product-signals. Stock line ("Only 4 left" when stock is
 * managed and at or below the threshold, "In stock" otherwise, hidden when stock
 * is not managed) plus "N sold" from the product's real total_sales. Works inside
 * Product Collection loops (block context) and on the single product page.
 *
 * Threshold: add_filter( 'instapass_low_stock_threshold', fn() => 5 );
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Unused.
 * @param WP_Block $block      Block instance (for context).
 * @return string
 */
function instapass_render_product_signals( $attributes, $content, $block ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return '';
	}
	$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
	$product = $post_id ? wc_get_product( $post_id ) : null;
	if ( ! $product ) {
		return '';
	}
	$parts = array();

	if ( ! isset( $attributes['showStock'] ) || $attributes['showStock'] ) {
		if ( ! $product->is_in_stock() ) {
			$parts[] = '<span class="ip-signal ip-signal--out">' . esc_html__( 'Sold out', 'instapass' ) . '</span>';
		} elseif ( $product->managing_stock() ) {
			$qty       = $product->get_stock_quantity();
			$threshold = (int) apply_filters( 'instapass_low_stock_threshold', 10 );
			if ( null !== $qty && $qty <= $threshold ) {
				/* translators: %s: number of items left in stock. */
				$parts[] = '<span class="ip-signal ip-signal--low">' . esc_html( sprintf( _n( 'Only %s left', 'Only %s left', $qty, 'instapass' ), number_format_i18n( $qty ) ) ) . '</span>';
			} else {
				$parts[] = '<span class="ip-signal ip-signal--in">' . esc_html__( 'In stock', 'instapass' ) . '</span>';
			}
		}
	}

	if ( ! isset( $attributes['showSold'] ) || $attributes['showSold'] ) {
		$sold = (int) $product->get_total_sales();
		if ( $sold > 0 ) {
			/* translators: %s: number sold. */
			$parts[] = '<span class="ip-signal ip-signal--sold">' . esc_html( sprintf( _n( '%s sold', '%s sold', $sold, 'instapass' ), number_format_i18n( $sold ) ) ) . '</span>';
		}
	}

	if ( empty( $parts ) ) {
		return '';
	}
	return sprintf( '<p %s>%s</p>', get_block_wrapper_attributes( array( 'class' => 'ip-signals' ) ), implode( '', $parts ) );
}

/**
 * Percentage off for a product on sale, rounded to a whole number.
 * Variable products report the largest discount among their variations.
 *
 * @param WC_Product $product Product.
 * @return int 0 when not on sale or not computable.
 */
function instapass_discount_percent( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
		return 0;
	}
	$best = 0;
	$candidates = $product->is_type( 'variable' ) ? $product->get_children() : array( $product->get_id() );
	foreach ( $candidates as $id ) {
		$p = $product->is_type( 'variable' ) ? wc_get_product( $id ) : $product;
		if ( ! $p || ! $p->is_on_sale() || ! $p->is_in_stock() || ( $p->is_type( 'variation' ) && ! $p->variation_is_visible() ) ) {
			continue;
		}
		$regular = (float) $p->get_regular_price();
		$sale    = (float) $p->get_sale_price();
		if ( $regular > 0 && $sale >= 0 && $sale < $regular ) {
			$best = max( $best, (int) round( ( $regular - $sale ) / $regular * 100 ) );
		}
	}
	return $best;
}

/**
 * Badge markup shared by the classic sale flash and the theme block.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function instapass_discount_badge_html( $product ) {
	$pct = instapass_discount_percent( $product );
	if ( $pct < 1 ) {
		return '';
	}
	/* translators: %d: percentage off. */
	$label = sprintf( __( '%d%% off', 'instapass' ), $pct );
	if ( $product->is_type( 'variable' ) ) {
		$label = sprintf( __( 'Up to %d%% off', 'instapass' ), $pct );
	}
	return sprintf( '<span class="ip-discount" aria-label="%s">%s</span>', esc_attr( $label ), esc_html( $label ) );
}

/**
 * Classic templates and the Product Image Gallery block print the sale flash
 * through this filter; show the percentage instead of "Sale!".
 *
 * @param string     $html    Original flash markup.
 * @param WP_Post    $post    Post.
 * @param WC_Product $product Product.
 * @return string
 */
function instapass_sale_flash( $html, $post, $product ) {
	$badge = instapass_discount_badge_html( $product );
	return $badge ? $badge : '';
}
add_filter( 'woocommerce_sale_flash', 'instapass_sale_flash', 10, 3 );

/**
 * Block: instapass/discount-badge. The WooCommerce Product Image block's own
 * badge ignores the sale-flash filter and always says "Sale", so product
 * templates use this block instead and the Woo badge is switched off.
 *
 * @param array    $attributes Attributes.
 * @param string   $content    Unused.
 * @param WP_Block $block      Block (for context).
 * @return string
 */
function instapass_render_discount_badge( $attributes, $content, $block ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return '';
	}
	$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
	$product = $post_id ? wc_get_product( $post_id ) : null;
	$badge   = $product ? instapass_discount_badge_html( $product ) : '';
	if ( ! $badge ) {
		return '';
	}
	return sprintf( '<div %s>%s</div>', get_block_wrapper_attributes( array( 'class' => 'ip-discount-wrap' ) ), $badge );
}

/**
 * Register the dynamic blocks and their tiny editor script (no build step).
 */
function instapass_register_blocks() {
	wp_register_script(
		'instapass-editor',
		get_template_directory_uri() . '/assets/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
		INSTAPASS_VERSION,
		true
	);
	register_block_type(
		'instapass/delivered-count',
		array(
			'api_version'           => 3,
			'title'                 => __( 'Licences delivered (live)', 'instapass' ),
			'description'           => __( 'Real total of items on completed orders, refreshed hourly. Hidden until the first sale.', 'instapass' ),
			'category'              => 'instapass',
			'icon'                  => 'chart-bar',
			'supports'              => array( 'html' => false ),
			'render_callback'       => 'instapass_render_delivered_count',
			'editor_script_handles' => array( 'instapass-editor' ),
		)
	);
	register_block_type(
		'instapass/discount-badge',
		array(
			'api_version'           => 3,
			'title'                 => __( 'Discount badge', 'instapass' ),
			'description'           => __( 'Percentage off, computed from regular and sale price. Empty when the product is not on sale.', 'instapass' ),
			'category'              => 'instapass',
			'icon'                  => 'tag',
			'uses_context'          => array( 'postId' ),
			'supports'              => array( 'html' => false ),
			'render_callback'       => 'instapass_render_discount_badge',
			'editor_script_handles' => array( 'instapass-editor' ),
		)
	);
	register_block_type(
		'instapass/product-signals',
		array(
			'api_version'           => 3,
			'title'                 => __( 'Stock and sold count', 'instapass' ),
			'description'           => __( '"Only X left" / "In stock" and "N sold", from the product\'s real stock and sales.', 'instapass' ),
			'category'              => 'instapass',
			'icon'                  => 'tag',
			'uses_context'          => array( 'postId' ),
			'attributes'            => array(
				'showStock' => array( 'type' => 'boolean', 'default' => true ),
				'showSold'  => array( 'type' => 'boolean', 'default' => true ),
			),
			'supports'              => array( 'html' => false ),
			'render_callback'       => 'instapass_render_product_signals',
			'editor_script_handles' => array( 'instapass-editor' ),
		)
	);
}
add_action( 'init', 'instapass_register_blocks' );

/**
 * Block category for the theme's dynamic blocks.
 *
 * @param array $categories Existing categories.
 * @return array
 */
function instapass_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'instapass',
			'title' => __( 'Instapass', 'instapass' ),
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'instapass_block_category' );

/* -------------------------------------------------------------------------
 * Dual-currency display: USD is charged, PKR is an estimate from a rate the
 * shop owner sets under WooCommerce > Settings > General. See README 11.
 * ---------------------------------------------------------------------- */

/**
 * Settings fields, inserted after WooCommerce's currency options.
 *
 * @param array $settings General settings.
 * @return array
 */
function instapass_pkr_settings( $settings ) {
	$fields = array(
		array(
			'title' => __( 'Rupee price estimates', 'instapass' ),
			'type'  => 'title',
			'desc'  => __( 'Rupee prices shown on the site are calculated from this rate; buyers are charged in USD.', 'instapass' ),
			'id'    => 'instapass_pkr_options',
		),
		array(
			'title'             => __( 'USD to PKR exchange rate', 'instapass' ),
			'desc'              => __( 'Rupees per 1 US dollar, for example 280. Leave 0 to switch the estimate off.', 'instapass' ),
			'id'                => 'instapass_pkr_rate',
			'type'              => 'number',
			'default'           => '0',
			'css'               => 'width:120px',
			'custom_attributes' => array( 'min' => '0', 'step' => '0.01' ),
		),
		array(
			'title'   => __( 'Show PKR estimate', 'instapass' ),
			'desc'    => __( 'Display an approximate rupee amount next to every USD price', 'instapass' ),
			'id'      => 'instapass_pkr_show',
			'type'    => 'checkbox',
			'default' => 'yes',
		),
		array(
			'type' => 'sectionend',
			'id'   => 'instapass_pkr_options',
		),
	);
	$out      = array();
	$inserted = false;
	foreach ( $settings as $row ) {
		$out[] = $row;
		if ( ! $inserted && isset( $row['type'], $row['id'] ) && 'sectionend' === $row['type'] && 'pricing_options' === $row['id'] ) {
			$out      = array_merge( $out, $fields );
			$inserted = true;
		}
	}
	return $inserted ? $out : array_merge( $settings, $fields );
}
add_filter( 'woocommerce_general_settings', 'instapass_pkr_settings' );

/**
 * Active rate, or 0 when the feature is off.
 *
 * @return float
 */
function instapass_pkr_rate() {
	if ( 'yes' !== get_option( 'instapass_pkr_show', 'yes' ) ) {
		return 0.0;
	}
	return max( 0.0, (float) apply_filters( 'instapass_pkr_rate', get_option( 'instapass_pkr_rate', 0 ) ) );
}

/**
 * "Rs 3,640" for a USD amount, or '' when the feature is off.
 *
 * @param float $usd Amount in USD.
 * @return string
 */
function instapass_pkr_text( $usd ) {
	$rate = instapass_pkr_rate();
	if ( $rate <= 0 || ! is_numeric( $usd ) ) {
		return '';
	}
	return 'Rs&nbsp;' . number_format_i18n( round( (float) $usd * $rate ), 0 );
}

/**
 * Wrapped "≈ Rs 3,640" span.
 *
 * @param float  $usd      Amount in USD.
 * @param string $modifier Extra class, e.g. 'inline'.
 * @return string
 */
function instapass_pkr_span( $usd, $modifier = '' ) {
	$text = instapass_pkr_text( $usd );
	if ( '' === $text ) {
		return '';
	}
	$class = 'ip-pkr' . ( $modifier ? ' ip-pkr--' . sanitize_html_class( $modifier ) : '' );
	return '<span class="' . esc_attr( $class ) . '">&asymp; ' . $text . '</span>';
}

/**
 * Should the estimate be appended in the current context? Front-end pages
 * only: no admin screens, no emails, no REST / Store API payloads, unless a
 * plugin opts in with the filter.
 *
 * @return bool
 */
function instapass_pkr_context_ok() {
	$ok = ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) && empty( $GLOBALS['instapass_in_email'] );
	return (bool) apply_filters( 'instapass_pkr_allow_context', $ok );
}
add_action( 'woocommerce_email_header', function () { $GLOBALS['instapass_in_email'] = true; }, 1 );
add_action( 'woocommerce_email_footer', function () { $GLOBALS['instapass_in_email'] = false; }, 999 );

/**
 * Product prices (cards, single product, related). Sale products get the
 * estimate for the sale price only; variable products show a range.
 *
 * @param string     $html    Price HTML.
 * @param WC_Product $product Product.
 * @return string
 */
function instapass_pkr_price_html( $html, $product ) {
	if ( '' === $html || instapass_pkr_rate() <= 0 || ! instapass_pkr_context_ok() || false !== strpos( $html, 'ip-pkr' ) ) {
		return $html;
	}
	if ( $product->is_type( 'variable' ) ) {
		$min = wc_get_price_to_display( $product, array( 'price' => $product->get_variation_price( 'min', true ) ) );
		$max = wc_get_price_to_display( $product, array( 'price' => $product->get_variation_price( 'max', true ) ) );
		if ( $min === $max ) {
			return $html . instapass_pkr_span( $min );
		}
		$a = instapass_pkr_text( $min );
		$b = instapass_pkr_text( $max );
		return $a ? $html . '<span class="ip-pkr">&asymp; ' . $a . ' &ndash; ' . $b . '</span>' : $html;
	}
	if ( $product->is_type( 'grouped' ) || '' === $product->get_price( 'edit' ) ) {
		return $html;
	}
	return $html . instapass_pkr_span( wc_get_price_to_display( $product ) );
}
add_filter( 'woocommerce_get_price_html', 'instapass_pkr_price_html', 20, 2 );

/**
 * Classic cart and checkout tables (inline estimates).
 */
function instapass_pkr_cart_item_price( $html, $cart_item ) {
	if ( instapass_pkr_rate() <= 0 || ! instapass_pkr_context_ok() || false !== strpos( $html, 'ip-pkr' ) || empty( $cart_item['data'] ) ) {
		return $html;
	}
	return $html . instapass_pkr_span( wc_get_price_to_display( $cart_item['data'] ), 'inline' );
}
add_filter( 'woocommerce_cart_item_price', 'instapass_pkr_cart_item_price', 20, 2 );

function instapass_pkr_cart_item_subtotal( $html, $cart_item ) {
	if ( instapass_pkr_rate() <= 0 || ! instapass_pkr_context_ok() || false !== strpos( $html, 'ip-pkr' ) ) {
		return $html;
	}
	$amount = (float) $cart_item['line_subtotal'] + ( WC()->cart && WC()->cart->display_prices_including_tax() ? (float) $cart_item['line_subtotal_tax'] : 0 );
	return $html . instapass_pkr_span( $amount, 'inline' );
}
add_filter( 'woocommerce_cart_item_subtotal', 'instapass_pkr_cart_item_subtotal', 20, 2 );

function instapass_pkr_cart_subtotal( $html, $compound, $cart ) {
	if ( instapass_pkr_rate() <= 0 || ! instapass_pkr_context_ok() || false !== strpos( $html, 'ip-pkr' ) ) {
		return $html;
	}
	$amount = $cart->display_prices_including_tax() ? $cart->get_subtotal() + $cart->get_subtotal_tax() : $cart->get_subtotal();
	return $html . instapass_pkr_span( $amount, 'inline' );
}
add_filter( 'woocommerce_cart_subtotal', 'instapass_pkr_cart_subtotal', 20, 3 );

function instapass_pkr_order_total( $html ) {
	if ( instapass_pkr_rate() <= 0 || ! instapass_pkr_context_ok() || false !== strpos( $html, 'ip-pkr' ) || ! WC()->cart ) {
		return $html;
	}
	return $html . instapass_pkr_span( (float) WC()->cart->get_total( 'edit' ), 'inline' );
}
add_filter( 'woocommerce_cart_totals_order_total_html', 'instapass_pkr_order_total', 20 );

/**
 * Block-based Cart and Checkout render their tables client-side from the
 * Store API, so the filters above never touch them. Fallback: a one-line,
 * JS-free estimate above the page content on the cart and checkout pages.
 *
 * @param string $content Page content.
 * @return string
 */
function instapass_pkr_cart_notice( $content ) {
	static $done = false;
	if ( $done || ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() || instapass_pkr_rate() <= 0 || ! instapass_pkr_context_ok() ) {
		return $content;
	}
	if ( ! in_the_loop() || ! is_main_query() || ! ( is_cart() || ( is_checkout() && ! is_order_received_page() ) ) ) {
		return $content;
	}
	// Classic shortcode pages already show inline estimates in the tables.
	if ( has_shortcode( $content, 'woocommerce_cart' ) || has_shortcode( $content, 'woocommerce_checkout' ) ) {
		return $content;
	}
	$done  = true;
	$total = (float) WC()->cart->get_total( 'edit' );
	$line  = sprintf(
		/* translators: 1: rupee estimate, 2: USD total. */
		__( 'Estimated total %1$s. You will be charged %2$s (USD).', 'instapass' ),
		'<strong>&asymp; ' . instapass_pkr_text( $total ) . '</strong>',
		wp_strip_all_tags( wc_price( $total ) )
	);
	return '<p class="ip-pkr-notice">' . $line . '</p>' . $content;
}
add_filter( 'the_content', 'instapass_pkr_cart_notice', 5 );

/**
 * Shortcodes: [instapass_pkr amount="13"] and [instapass_pkr_note].
 */
function instapass_pkr_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'amount' => '0' ), $atts, 'instapass_pkr' );
	return instapass_pkr_span( (float) $atts['amount'] );
}
add_shortcode( 'instapass_pkr', 'instapass_pkr_shortcode' );

function instapass_pkr_note_text() {
	$rate = instapass_pkr_rate();
	if ( $rate <= 0 ) {
		return '';
	}
	/* translators: %s: rupees per dollar. */
	return sprintf( __( 'Prices in USD. Rupee estimates at Rs %s per $1, updated by the shop.', 'instapass' ), number_format_i18n( $rate, ( floor( $rate ) === $rate ? 0 : 2 ) ) );
}
function instapass_pkr_note_shortcode() {
	$text = instapass_pkr_note_text();
	return $text ? '<span class="ip-pkr-note">' . esc_html( $text ) . '</span>' : '';
}
add_shortcode( 'instapass_pkr_note', 'instapass_pkr_note_shortcode' );

/**
 * Block: instapass/pkr-note (footer line). Empty when the rate is 0.
 */
function instapass_render_pkr_note() {
	$text = instapass_pkr_note_text();
	if ( ! $text ) {
		return '';
	}
	return sprintf( '<p %s>%s</p>', get_block_wrapper_attributes( array( 'class' => 'ip-pkr-note has-small-font-size' ) ), esc_html( $text ) );
}
function instapass_register_pkr_block() {
	register_block_type(
		'instapass/pkr-note',
		array(
			'api_version'           => 3,
			'title'                 => __( 'Exchange rate line', 'instapass' ),
			'description'           => __( '"Prices in USD. Rupee estimates at Rs X per $1." Hidden while the rate is 0.', 'instapass' ),
			'category'              => 'instapass',
			'icon'                  => 'money-alt',
			'supports'              => array( 'html' => false ),
			'render_callback'       => 'instapass_render_pkr_note',
			'editor_script_handles' => array( 'instapass-editor' ),
		)
	);
}
add_action( 'init', 'instapass_register_pkr_block' );


/* -------------------------------------------------------------------------
 * Starter pages. The header and footer link to /contact/, /faq/,
 * /refund-policy/, /terms-of-service/ and /privacy-policy/. If any of those
 * pages is missing, create it (published) from the matching theme pattern so
 * no link returns a 404. Runs once per theme version on admin_init, and
 * again when the theme is activated. Existing pages are never changed; the
 * one exception is WordPress's own never-edited "Privacy Policy" draft,
 * which is filled and published so the footer link works.
 * ---------------------------------------------------------------------- */

/**
 * Slug => title and pattern for every page the theme links to.
 *
 * @return array
 */
function instapass_starter_pages() {
	return array(
		'contact'          => array(
			'title'   => __( 'Contact', 'instapass' ),
			'pattern' => 'instapass/page-contact',
		),
		'faq'              => array(
			'title'   => __( 'FAQ', 'instapass' ),
			'pattern' => 'instapass/page-faq',
		),
		'refund-policy'    => array(
			'title'   => __( 'Refund Policy', 'instapass' ),
			'pattern' => 'instapass/page-refunds',
		),
		'terms-of-service' => array(
			'title'   => __( 'Terms of Service', 'instapass' ),
			'pattern' => 'instapass/page-terms',
		),
		'privacy-policy'   => array(
			'title'   => __( 'Privacy Policy', 'instapass' ),
			'pattern' => 'instapass/page-privacy',
		),
	);
}

/**
 * Create any starter page that does not exist yet.
 *
 * @return string[] Slugs created or published in this run.
 */
function instapass_seed_pages() {
	if ( ! current_user_can( 'edit_pages' ) || ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return array();
	}
	$registry = WP_Block_Patterns_Registry::get_instance();
	$done     = array();

	foreach ( instapass_starter_pages() as $slug => $page ) {
		$slug    = sanitize_title( $slug );
		$pattern = sanitize_text_field( $page['pattern'] );
		if ( '' === $slug || ! $registry->is_registered( $pattern ) ) {
			continue;
		}
		// Same mechanism the front-page template uses: a pattern reference.
		// The editor expands it into ordinary blocks the first time the page is opened.
		$content  = '<!-- wp:pattern ' . wp_json_encode( array( 'slug' => $pattern ) ) . ' /-->';
		$existing = get_page_by_path( $slug, OBJECT, 'page' );

		if ( $existing instanceof WP_Post ) {
			$untouched_wp_privacy_draft = 'draft' === $existing->post_status
				&& (int) get_option( 'wp_page_for_privacy_policy' ) === (int) $existing->ID
				&& $existing->post_modified === $existing->post_date;
			if ( $untouched_wp_privacy_draft ) {
				$updated = wp_update_post(
					array(
						'ID'           => (int) $existing->ID,
						'post_status'  => 'publish',
						'post_content' => $content,
					),
					true
				);
				if ( ! is_wp_error( $updated ) ) {
					$done[] = $slug;
				}
			}
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'post_title'     => sanitize_text_field( $page['title'] ),
				'post_name'      => $slug,
				'post_content'   => $content,
				'post_author'    => get_current_user_id(),
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			true
		);
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}
		$done[] = $slug;
		if ( 'privacy-policy' === $slug && ! (int) get_option( 'wp_page_for_privacy_policy' ) ) {
			update_option( 'wp_page_for_privacy_policy', (int) $id );
		}
	}
	return $done;
}

/**
 * admin_init: seed once per theme version, the first time a user who can
 * edit pages opens the dashboard.
 */
function instapass_maybe_seed_pages() {
	if ( INSTAPASS_VERSION === get_option( 'instapass_pages_seeded' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_pages' ) ) {
		return; // Try again on the next admin visit by someone who can.
	}
	instapass_seed_pages();
	update_option( 'instapass_pages_seeded', INSTAPASS_VERSION, false );
}
add_action( 'admin_init', 'instapass_maybe_seed_pages' );

/**
 * Theme activation: seed straight away when an editor activates the theme.
 * Activation from WP-CLI has no user; admin_init above covers that case.
 */
function instapass_seed_pages_on_switch() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	instapass_seed_pages();
	update_option( 'instapass_pages_seeded', INSTAPASS_VERSION, false );
}
add_action( 'after_switch_theme', 'instapass_seed_pages_on_switch' );
