<?php
/**
 * Customizzazioni WooCommerce.
 *
 * Rimuove wrapper default WC, aggiunge campi B2B checkout,
 * gestisce label prezzi IVA esclusa, sconti per quantità.
 */

declare(strict_types=1);

// Dichiarazione compatibilità HPOS
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
} );

// ─────────────────────────────────────────────
// 1. Theme support
// ─────────────────────────────────────────────

add_action( 'after_setup_theme', function () {
	add_theme_support( 'woocommerce', [
		'thumbnail_image_width' => 600,
		'single_image_width'    => 900,
		'product_grid'          => [
			'default_rows'    => 4,
			'min_rows'        => 1,
			'default_columns' => 4,
			'min_columns'     => 1,
			'max_columns'     => 6,
		],
	] );

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
} );

// ─────────────────────────────────────────────
// 2. Rimuovi wrapper WooCommerce default
// ─────────────────────────────────────────────

// WooCommerce aggiunge automaticamente un <div class="woocommerce"> intorno
// ai contenuti — lo gestiamo noi nei template.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper' );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end' );
remove_action( 'woocommerce_sidebar',             'woocommerce_get_sidebar' );

add_action( 'woocommerce_before_main_content', function () {
	// Aperto nel template, niente wrapper generico qui
} );

// ─────────────────────────────────────────────
// 3. Label prezzi — sempre IVA esclusa (B2B)
// ─────────────────────────────────────────────

add_filter( 'woocommerce_get_price_html', 'ms_append_vat_label', 20, 2 );
function ms_append_vat_label( string $price_html, \WC_Product $product ): string {
	if ( empty( $price_html ) ) {
		return $price_html;
	}
	return $price_html . '<span class="price__vat"> + IVA</span>';
}

// ─────────────────────────────────────────────
// 4. SKU — visibile in listing e PDP
// ─────────────────────────────────────────────

// SKU già visibile nella PDP WC standard — forziamo anche in loop
add_action( 'woocommerce_after_shop_loop_item_title', 'ms_show_sku_in_loop', 5 );
function ms_show_sku_in_loop(): void {
	global $product;
	if ( ! $product instanceof \WC_Product ) return;
	$sku = $product->get_sku();
	if ( ! $sku ) return;
	echo '<span class="label-mono product-card__sku">' . esc_html( $sku ) . '</span>';
}

// ─────────────────────────────────────────────
// 5. Campi B2B nel checkout
// ─────────────────────────────────────────────

add_filter( 'woocommerce_checkout_fields', 'ms_add_b2b_checkout_fields' );
function ms_add_b2b_checkout_fields( array $fields ): array {
	$fields['billing']['billing_company_name'] = [
		'type'        => 'text',
		'label'       => __( 'Ragione Sociale', 'mondosegnaletica' ),
		'placeholder' => __( 'Nome azienda / Ente', 'mondosegnaletica' ),
		'class'       => [ 'form-row-wide' ],
		'required'    => true,
		'priority'    => 25,
	];

	$fields['billing']['billing_vat'] = [
		'type'        => 'text',
		'label'       => __( 'Partita IVA / Codice Fiscale', 'mondosegnaletica' ),
		'placeholder' => 'IT12345678901',
		'class'       => [ 'form-row-first' ],
		'required'    => true,
		'priority'    => 30,
		'validate'    => [ 'postcode' ], // sostituto — validazione custom sotto
	];

	$fields['billing']['billing_sdi'] = [
		'type'        => 'text',
		'label'       => __( 'Codice SDI / PEC', 'mondosegnaletica' ),
		'placeholder' => 'XXXXXXX oppure pec@esempio.it',
		'class'       => [ 'form-row-last' ],
		'required'    => false,
		'priority'    => 35,
	];

	return $fields;
}

// Salva campi B2B sull'ordine
add_action( 'woocommerce_checkout_update_order_meta', 'ms_save_b2b_checkout_fields' );
function ms_save_b2b_checkout_fields( int $order_id ): void {
	$fields = [ 'billing_company_name', 'billing_vat', 'billing_sdi' ];
	foreach ( $fields as $field ) {
		if ( ! empty( $_POST[ $field ] ) ) {
			update_post_meta( $order_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}

// Mostra campi B2B nella email e nell'admin ordine
add_action( 'woocommerce_admin_order_data_after_billing_address', 'ms_display_b2b_fields_in_admin' );
function ms_display_b2b_fields_in_admin( \WC_Order $order ): void {
	$vat     = get_post_meta( $order->get_id(), 'billing_vat', true );
	$company = get_post_meta( $order->get_id(), 'billing_company_name', true );
	$sdi     = get_post_meta( $order->get_id(), 'billing_sdi', true );

	if ( $company ) {
		echo '<p><strong>' . esc_html__( 'Ragione Sociale', 'mondosegnaletica' ) . ':</strong> ' . esc_html( $company ) . '</p>';
	}
	if ( $vat ) {
		echo '<p><strong>' . esc_html__( 'P.IVA', 'mondosegnaletica' ) . ':</strong> ' . esc_html( $vat ) . '</p>';
	}
	if ( $sdi ) {
		echo '<p><strong>' . esc_html__( 'SDI/PEC', 'mondosegnaletica' ) . ':</strong> ' . esc_html( $sdi ) . '</p>';
	}
}

// ─────────────────────────────────────────────
// 6. Sconti per quantità — meta _ms_qty_discounts
//    Formato: [ ['min'=>1,'max'=>9,'pct'=>0], ['min'=>10,'max'=>49,'pct'=>5], ... ]
// ─────────────────────────────────────────────

function ms_get_qty_discounts( int $product_id ): array {
	$stored = get_post_meta( $product_id, '_ms_qty_discounts', true );
	if ( is_array( $stored ) && ! empty( $stored ) ) {
		return $stored;
	}

	// Default fasce B2B standard (3 tier)
	return [
		[ 'min' => 1,  'max' => 9,    'pct' => 0  ],
		[ 'min' => 10, 'max' => 49,   'pct' => 10 ],
		[ 'min' => 50, 'max' => null, 'pct' => 20 ],
	];
}

// Applica sconto al prezzo nel carrello in base alla quantità
add_filter( 'woocommerce_cart_item_price', 'ms_apply_qty_discount_cart', 10, 3 );
function ms_apply_qty_discount_cart( string $price_html, array $cart_item, string $cart_item_key ): string {
	$product_id = $cart_item['product_id'];
	$qty        = $cart_item['quantity'];
	$product    = $cart_item['data'];

	if ( ! $product instanceof \WC_Product ) return $price_html;

	$tiers     = ms_get_qty_discounts( $product_id );
	$base_price = (float) $product->get_price();

	foreach ( $tiers as $tier ) {
		$in_range = $qty >= $tier['min'] && ( $tier['max'] === null || $qty <= $tier['max'] );
		if ( $in_range && $tier['pct'] > 0 ) {
			$discounted = $base_price * ( 1 - $tier['pct'] / 100 );
			return wc_price( $discounted ) . '<span class="price__vat"> + IVA</span>';
		}
	}

	return $price_html;
}

// ─────────────────────────────────────────────
// 7. Breadcrumb WooCommerce — minimal, mono
// ─────────────────────────────────────────────

add_filter( 'woocommerce_breadcrumb_defaults', function ( array $defaults ): array {
	$defaults['delimiter']   = '<span class="pdp-breadcrumb__sep">›</span>';
	$defaults['wrap_before'] = '<span class="pdp-breadcrumb__chain">';
	$defaults['wrap_after']  = '</span>';
	return $defaults;
} );

// Rimuovi "Home" dal breadcrumb WC — il template PDP usa già "01 / CATALOGO" come radice.
add_filter( 'woocommerce_breadcrumb_trail', function ( array $trail ): array {
	if ( ! empty( $trail ) ) {
		array_shift( $trail ); // rimuove il primo elemento [Home, url]
	}
	return $trail;
} );

// ─────────────────────────────────────────────
// 8. Placeholder immagine on-brand
// ─────────────────────────────────────────────

add_filter( 'woocommerce_placeholder_img_src', function (): string {
	return get_template_directory_uri() . '/assets/img/product-placeholder.svg';
} );

add_filter( 'woocommerce_placeholder_img', function ( string $html, string $size ): string {
	$src = get_template_directory_uri() . '/assets/img/product-placeholder.svg';
	return '<img src="' . esc_url( $src ) . '" alt="Immagine non disponibile" class="woocommerce-placeholder wp-post-image" />';
}, 10, 2 );

// ─────────────────────────────────────────────
// 9. Rimuovi elementi WC non necessari
// ─────────────────────────────────────────────

// Rimuove il risultato di ricerca "Showing all X results" sopra la griglia
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

// Rimuove il menu di ordinamento WC (lo gestiamo noi nella toolbar)
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

// Rimuove "Showing 1–X of Y results" — riaggiunto nel nostro template
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count' );

// ─────────────────────────────────────────────
// 10. Filtri attributi prodotto via URL params
//     URL: ?filter_pa_{name}=slug1,slug2
// ─────────────────────────────────────────────

add_filter( 'woocommerce_product_query_tax_query', 'ms_apply_attribute_filters', 10, 2 );
function ms_apply_attribute_filters( array $tax_query, \WC_Query $wc_query ): array {
	$attribute_taxonomies = wc_get_attribute_taxonomies();
	if ( empty( $attribute_taxonomies ) ) {
		return $tax_query;
	}
	foreach ( $attribute_taxonomies as $attr ) {
		$param = 'filter_pa_' . $attr->attribute_name;
		if ( empty( $_GET[ $param ] ) ) {
			continue;
		}
		$terms = array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET[ $param ] ) ) ) );
		if ( empty( $terms ) ) {
			continue;
		}
		$tax_query[] = [
			'taxonomy' => 'pa_' . $attr->attribute_name,
			'field'    => 'slug',
			'terms'    => $terms,
			'operator' => 'IN',
		];
	}
	return $tax_query;
}
