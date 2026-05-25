<?php
/**
 * Sezione 03 / BESTSELLER — Carosello orizzontale con prev/next.
 */

$featured_products = [];
if ( class_exists( 'WooCommerce' ) ) {
	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'tax_query'      => [ [ // phpcs:ignore WordPress.DB.SlowDBQuery
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => 'featured',
		] ],
	];
	$query = new WP_Query( $args );
	$featured_products = $query->posts;

	if ( empty( $featured_products ) ) {
		$query = new WP_Query( [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );
		$featured_products = $query->posts;
	}
}

if ( empty( $featured_products ) ) return;
?>

<section class="section-bestseller carousel" id="bestseller" aria-labelledby="bestseller-title">
	<div class="container">

		<div class="section-header section-header--row">
			<div>
				<span class="label-section">03 / BESTSELLER</span>
				<h2 class="section-title" id="bestseller-title">I più richiesti.</h2>
			</div>
			<div class="carousel-nav" aria-label="<?php esc_attr_e( 'Navigazione carosello', 'mondosegnaletica' ); ?>">
				<button class="carousel-btn carousel-btn--prev" aria-label="<?php esc_attr_e( 'Precedente', 'mondosegnaletica' ); ?>">
					<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				</button>
				<button class="carousel-btn carousel-btn--next" aria-label="<?php esc_attr_e( 'Successivo', 'mondosegnaletica' ); ?>">
					<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
				</button>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ?: home_url( '/negozio' ) ); ?>" class="btn btn--ghost">
					Vedi tutto
					<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
				</a>
			</div>
		</div>

		<div class="carousel-track">
			<?php foreach ( $featured_products as $post ) :
				$product = wc_get_product( $post );
				if ( ! $product instanceof WC_Product ) continue;
				ms_get_template_part( 'template-parts/product/card', [ 'product' => $product ] );
			endforeach; ?>
		</div>

	</div>
</section>
