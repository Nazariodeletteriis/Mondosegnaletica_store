<?php
/**
 * Sezione 03 / BESTSELLER — 4 prodotti featured.
 */

$featured_products = [];
if ( class_exists( 'WooCommerce' ) ) {
	// Prova featured (taxonomy visibility=featured)
	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'tax_query'      => [ [ // phpcs:ignore WordPress.DB.SlowDBQuery
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => 'featured',
		] ],
	];
	$query = new WP_Query( $args );
	$featured_products = $query->posts;

	// Fallback: prodotti più recenti
	if ( empty( $featured_products ) ) {
		$query = new WP_Query( [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 4,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );
		$featured_products = $query->posts;
	}
}

if ( empty( $featured_products ) ) return;
?>

<section class="section-bestseller" id="bestseller" aria-labelledby="bestseller-title">
	<div class="container">

		<div class="section-header section-header--row">
			<div>
				<span class="label-section">03 / BESTSELLER</span>
				<h2 class="section-title" id="bestseller-title">I più richiesti.</h2>
			</div>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ?: home_url( '/negozio' ) ); ?>" class="btn btn--ghost">
				Vedi tutto il catalogo
				<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
			</a>
		</div>

		<div class="bestseller-row products-grid">
			<?php foreach ( $featured_products as $post ) :
				$product = wc_get_product( $post );
				if ( ! $product instanceof WC_Product ) continue;
				ms_get_template_part( 'template-parts/product/card', [ 'product' => $product ] );
			endforeach; ?>
		</div>

	</div>
</section>
