<?php
/**
 * Sezione 02 / CATALOGO — Tab bar 6 categorie con 8 prodotti per tab.
 */

$cat_meta = [
	'segnaletica-verticale'   => [ 'code' => 'CAT-01', 'name' => 'Segnaletica Verticale',   'count' => 412 ],
	'segnaletica-orizzontale' => [ 'code' => 'CAT-02', 'name' => 'Segnaletica Orizzontale', 'count' => 156 ],
	'coni-transenne'          => [ 'code' => 'CAT-03', 'name' => 'Coni & Transenne',         'count' => 184 ],
	'delineatori-paletti'     => [ 'code' => 'CAT-04', 'name' => 'Delineatori & Paletti',   'count' => 96  ],
	'cantieristica'           => [ 'code' => 'CAT-05', 'name' => 'Cantieristica',            'count' => 312 ],
	'dissuasori-accessori'    => [ 'code' => 'CAT-06', 'name' => 'Dissuasori & Accessori',  'count' => 245 ],
];

$wc_categories = [];
if ( class_exists( 'WooCommerce' ) ) {
	$terms = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'slug'       => array_keys( $cat_meta ),
		'number'     => 10,
		'orderby'    => 'slug',
		'order'      => 'ASC',
	] );
	if ( ! is_wp_error( $terms ) ) {
		$wc_categories = $terms;
	}
}

// Usa fallback statico se WC non ha categorie
if ( empty( $wc_categories ) ) {
	foreach ( $cat_meta as $slug => $m ) {
		$wc_categories[] = (object) [
			'term_id' => 0,
			'slug'    => $slug,
			'name'    => $m['name'],
			'count'   => $m['count'],
		];
	}
}

// Costruisce $categories nell'ordine definito da $cat_meta (CAT-01 → CAT-06)
// WC restituisce i term in ordine alfabetico; il lookup per slug garantisce l'ordine corretto.
$terms_by_slug = [];
foreach ( $wc_categories as $term ) {
	$terms_by_slug[ $term->slug ] = $term;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/negozio/' );

// Le categorie che non esistono ancora in WooCommerce vengono saltate: meglio
// nasconderle che mostrare i conteggi del brief, che sono stime, non dati reali.
$categories = [];
foreach ( $cat_meta as $slug => $meta ) {
	$cat = $terms_by_slug[ $slug ] ?? null;
	if ( ! $cat ) {
		continue;
	}

	$cat_url = get_term_link( $cat );

	$categories[] = [
		'term'  => $cat,
		'code'  => $meta['code'],
		'url'   => is_wp_error( $cat_url ) ? $shop_url : $cat_url,
		'count' => (int) $cat->count,
	];
}
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

		<!-- Tab navigation -->
		<div class="cat-tablist" role="tablist" aria-label="<?php esc_attr_e( 'Categorie prodotti', 'mondosegnaletica' ); ?>">
			<?php foreach ( $categories as $i => $c ) : ?>
			<button
				class="cat-tab"
				role="tab"
				aria-selected="false"
				aria-controls="cat-panel-<?php echo esc_attr( $c['term']->slug ); ?>"
				id="cat-tab-<?php echo esc_attr( $c['term']->slug ); ?>"
				tabindex="-1"
			>
				<span class="cat-tab__code"><?php echo esc_html( $c['code'] ); ?></span>
				<span class="cat-tab__name"><?php echo esc_html( $c['term']->name ); ?></span>
				<span class="cat-tab__count"><?php echo esc_html( number_format( $c['count'], 0, ',', '.' ) ); ?></span>
			</button>
			<?php endforeach; ?>
		</div>

		<!-- Tab panels -->
		<?php foreach ( $categories as $i => $c ) :
			// Query prodotti per categoria
			$products = [];
			if ( class_exists( 'WooCommerce' ) && $c['term']->term_id ) {
				$pq = new WP_Query( [
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => 8,
					'tax_query'      => [ [ // phpcs:ignore WordPress.DB.SlowDBQuery
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $c['term']->slug,
					] ],
				] );
				$products = $pq->posts;
			}
		?>
		<div
			class="cat-panel"
			role="tabpanel"
			id="cat-panel-<?php echo esc_attr( $c['term']->slug ); ?>"
			aria-labelledby="cat-tab-<?php echo esc_attr( $c['term']->slug ); ?>"
			hidden
		>
			<?php if ( ! empty( $products ) ) : ?>
				<div class="cat-products-grid">
					<?php foreach ( $products as $post_item ) :
						$product = wc_get_product( $post_item );
						if ( ! $product instanceof WC_Product ) continue;
						ms_get_template_part( 'template-parts/product/card', [ 'product' => $product ] );
					endforeach; ?>
				</div>
			<?php else : ?>
				<div class="cat-panel__empty">
					<span class="label-mono"><?php echo esc_html( $c['code'] ); ?></span>
					<p><?php esc_html_e( 'Catalogo in allestimento — disponibile a breve.', 'mondosegnaletica' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="cat-panel__footer">
				<a href="<?php echo esc_url( $c['url'] ); ?>" class="btn btn--ghost">
					Vedi tutti i <?php echo esc_html( number_format( $c['count'], 0, ',', '.' ) ); ?> prodotti
					<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
				</a>
			</div>
		</div>
		<?php endforeach; ?>

	</div>
</section>
