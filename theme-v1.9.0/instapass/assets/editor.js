/* Instapass editor registration for the theme's two dynamic blocks.
   Plain ES5, no build step. Front-end output comes from functions.php. */
( function ( wp ) {
	if ( ! wp || ! wp.blocks ) { return; }
	var el = wp.element.createElement;
	var SSR = wp.serverSideRender;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var __ = wp.i18n.__;

	wp.blocks.registerBlockType( 'instapass/delivered-count', {
		title: __( 'Licences delivered (live)', 'instapass' ),
		description: __( 'Real total of items on completed orders, refreshed hourly. Hidden until the first sale.', 'instapass' ),
		category: 'instapass',
		icon: 'chart-bar',
		supports: { html: false },
		edit: function () {
			return el( 'span', useBlockProps(), el( SSR, {
				block: 'instapass/delivered-count',
				EmptyResponsePlaceholder: function () {
					return el( 'span', { className: 'ip-delivered-count' }, __( '0 (hidden on the live site until the first completed order)', 'instapass' ) );
				}
			} ) );
		},
		save: function () { return null; }
	} );

	wp.blocks.registerBlockType( 'instapass/discount-badge', {
		title: __( 'Discount badge', 'instapass' ),
		description: __( 'Percentage off, computed from regular and sale price. Empty when the product is not on sale.', 'instapass' ),
		category: 'instapass',
		icon: 'tag',
		usesContext: [ 'postId' ],
		supports: { html: false },
		edit: function ( props ) {
			return el( 'div', useBlockProps(), el( SSR, {
				block: 'instapass/discount-badge',
				urlQueryArgs: { context: { postId: props.context && props.context.postId } },
				EmptyResponsePlaceholder: function () {
					return el( 'span', { className: 'ip-discount' }, __( '-35% (shown only when on sale)', 'instapass' ) );
				}
			} ) );
		},
		save: function () { return null; }
	} );

	wp.blocks.registerBlockType( 'instapass/pkr-note', {
		title: __( 'Exchange rate line', 'instapass' ),
		description: __( '"Prices in USD. Rupee estimates at Rs X per $1." Hidden while the rate is 0.', 'instapass' ),
		category: 'instapass',
		icon: 'money-alt',
		supports: { html: false },
		edit: function () {
			return el( 'p', useBlockProps(), el( SSR, {
				block: 'instapass/pkr-note',
				EmptyResponsePlaceholder: function () {
					return el( 'span', { className: 'ip-pkr-note' }, __( 'Exchange-rate line (hidden until a rate is set in WooCommerce > Settings > General)', 'instapass' ) );
				}
			} ) );
		},
		save: function () { return null; }
	} );

	wp.blocks.registerBlockType( 'instapass/product-signals', {
		title: __( 'Stock and sold count', 'instapass' ),
		description: __( '"Only X left" / "In stock" and "N sold", from the product\'s real stock and sales.', 'instapass' ),
		category: 'instapass',
		icon: 'tag',
		usesContext: [ 'postId' ],
		attributes: {
			showStock: { type: 'boolean', default: true },
			showSold: { type: 'boolean', default: true }
		},
		supports: { html: false },
		edit: function ( props ) {
			var controls = el( wp.blockEditor.InspectorControls, {},
				el( wp.components.PanelBody, { title: __( 'Show', 'instapass' ) },
					el( wp.components.ToggleControl, {
						label: __( 'Stock line', 'instapass' ),
						checked: props.attributes.showStock,
						onChange: function ( v ) { props.setAttributes( { showStock: v } ); }
					} ),
					el( wp.components.ToggleControl, {
						label: __( 'Sold count', 'instapass' ),
						checked: props.attributes.showSold,
						onChange: function ( v ) { props.setAttributes( { showSold: v } ); }
					} )
				)
			);
			return el( 'div', useBlockProps(),
				controls,
				el( SSR, {
					block: 'instapass/product-signals',
					attributes: props.attributes,
					urlQueryArgs: { context: { postId: props.context && props.context.postId } },
					EmptyResponsePlaceholder: function () {
						return el( 'p', { className: 'ip-signals' }, el( 'span', { className: 'ip-signal ip-signal--in' }, __( 'In stock', 'instapass' ) ), el( 'span', { className: 'ip-signal ip-signal--sold' }, __( 'sold count appears after the first sale', 'instapass' ) ) );
					}
				} )
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
