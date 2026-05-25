<?php
/**
 * Sezione 02 / CATALOGO — Grid 3×2 categorie WooCommerce.
 */

// Prendi categorie da WooCommerce
$wc_categories = [];
if ( class_exists( 'WooCommerce' ) ) {
	$wc_categories = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => 0,
		'number'     => 6,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
	] );

	if ( is_wp_error( $wc_categories ) ) {
		$wc_categories = [];
	}
}

// Fallback ai dati statici se WC non ha ancora categorie
$fallback_cats = ms_get_default_categories();
$use_fallback  = empty( $wc_categories );

// Mappa codici categoria → dati statici per overlay
$cat_meta = [
	'segnaletica-verticale'   => [ 'code' => 'CAT-01', 'count_fallback' => 412 ],
	'segnaletica-orizzontale' => [ 'code' => 'CAT-02', 'count_fallback' => 156 ],
	'coni-transenne'          => [ 'code' => 'CAT-03', 'count_fallback' => 184 ],
	'delineatori-paletti'     => [ 'code' => 'CAT-04', 'count_fallback' => 96  ],
	'cantieristica'           => [ 'code' => 'CAT-05', 'count_fallback' => 312 ],
	'dissuasori-accessori'    => [ 'code' => 'CAT-06', 'count_fallback' => 245 ],
];
?>

<section class="section-catalog" id="catalogo" aria-labelledby="catalog-title">
	<div class="container">

		<div class="section-header">
			<span class="label-section">02 / CATALOGO</span>
			<h2 class="section-title" id="catalog-title">
				Sei categorie.<br>
				<span class="section-title--accent">Una strada sola.</span>
			</h2>
		</div>

		<div class="categories-grid">
			<?php if ( ! $use_fallback && is_array( $wc_categories ) ) :
				foreach ( $wc_categories as $i => $cat ) :
					$cat_url   = get_term_link( $cat );
					$thumb_id  = get_term_meta( $cat->term_id, 'thumbnail_id', true );
					$thumb_src = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'ms-category-card' ) : '';
					$meta      = $cat_meta[ $cat->slug ] ?? [ 'code' => 'CAT-0' . ( $i + 1 ), 'count_fallback' => $cat->count ];
					$count     = $cat->count ?: $meta['count_fallback'];
			?>
			<a
				href="<?php echo esc_url( is_string( $cat_url ) ? $cat_url : '#' ); ?>"
				class="category-card"
				aria-label="<?php echo esc_attr( $cat->name . ' — ' . $count . ' prodotti' ); ?>"
			>
				<?php if ( $thumb_src ) : ?>
				<div class="category-card__media" aria-hidden="true">
					<img
						src="<?php echo esc_url( $thumb_src ); ?>"
						alt=""
						loading="<?php echo $i < 3 ? 'eager' : 'lazy'; ?>"
					>
				</div>
				<?php endif; ?>

				<div class="category-card__overlay" aria-hidden="true"></div>

				<div class="category-card__body">
					<p class="category-card__code"><?php echo esc_html( $meta['code'] ); ?></p>
					<h3 class="category-card__name"><?php echo esc_html( $cat->name ); ?></h3>
					<p class="category-card__count"><?php echo esc_html( number_format( $count, 0, ',', '.' ) ); ?> PRODOTTI</p>
				</div>
			</a>
			<?php endforeach;
			else :
				// Fallback statico
				foreach ( $fallback_cats as $i => $cat ) :
			?>
			<a
				href="<?php echo esc_url( home_url( '/negozio/' . $cat['slug'] ) ); ?>"
				class="category-card"
				aria-label="<?php echo esc_attr( $cat['name'] . ' — ' . $cat['count'] . ' prodotti' ); ?>"
			>
				<div class="category-card__overlay" aria-hidden="true"></div>
				<div class="category-card__body">
					<p class="category-card__code"><?php echo esc_html( $cat['code'] ); ?></p>
					<h3 class="category-card__name"><?php echo esc_html( $cat['name'] ); ?></h3>
					<p class="category-card__count"><?php echo esc_html( number_format( $cat['count'], 0, ',', '.' ) ); ?> PRODOTTI</p>
				</div>
			</a>
			<?php endforeach;
			endif; ?>
		</div>

	</div>
</section>
