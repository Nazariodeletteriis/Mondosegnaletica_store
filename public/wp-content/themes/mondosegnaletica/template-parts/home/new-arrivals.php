<?php
/**
 * Sezione 04 / NUOVI ARRIVI — Carosello ultimi prodotti per data.
 */

$new_products = [];
if ( class_exists( 'WooCommerce' ) ) {
	$query = new WP_Query( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => [ [ // phpcs:ignore WordPress.DB.SlowDBQuery
			'key'     => '_thumbnail_id',
			'compare' => 'EXISTS',
		] ],
	] );
	$new_products = $query->posts;
}

if ( empty( $new_products ) ) return;
?>

<section class="section-new-arrivals carousel" id="nuovi-arrivi" aria-labelledby="new-arrivals-title">
	<div class="container">

		<div class="section-header section-header--row">
			<div>
				<span class="label-section">04 / NUOVI ARRIVI</span>
				<h2 class="section-title" id="new-arrivals-title">Appena arrivati.</h2>
			</div>
			<div class="carousel-nav" aria-label="<?php esc_attr_e( 'Navigazione carosello nuovi arrivi', 'mondosegnaletica' ); ?>">
				<button class="carousel-btn carousel-btn--prev" aria-label="<?php esc_attr_e( 'Precedente', 'mondosegnaletica' ); ?>">
					<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				</button>
				<button class="carousel-btn carousel-btn--next" aria-label="<?php esc_attr_e( 'Successivo', 'mondosegnaletica' ); ?>">
					<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
				</button>
			</div>
		</div>

		<div class="carousel-track">
			<?php foreach ( $new_products as $post ) :
				$product = wc_get_product( $post );
				if ( ! $product instanceof WC_Product ) continue;
				ms_get_template_part( 'template-parts/product/card', [ 'product' => $product ] );
			endforeach; ?>
		</div>

	</div>
</section>
