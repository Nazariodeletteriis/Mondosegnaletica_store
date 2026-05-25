<?php
/**
 * WooCommerce — listing categoria / shop archive.
 *
 * Override di woocommerce/templates/archive-product.php
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$is_category  = is_product_category();
$current_cat  = $is_category ? get_queried_object() : null;
$cat_name     = $current_cat ? $current_cat->name : __( 'Negozio', 'mondosegnaletica' );
$cat_desc     = $current_cat ? $current_cat->description : '';
$product_count = wc_get_loop_prop( 'total' ) ?: ( $is_category ? $current_cat->count : null );

// Cat code da meta
$cat_code = $current_cat ? get_term_meta( $current_cat->term_id, 'ms_cat_code', true ) : '';
$cat_meta_map = [
	'segnaletica-verticale'   => 'CAT-01',
	'segnaletica-orizzontale' => 'CAT-02',
	'coni-transenne'          => 'CAT-03',
	'delineatori-paletti'     => 'CAT-04',
	'cantieristica'           => 'CAT-05',
	'dissuasori-accessori'    => 'CAT-06',
];
if ( ! $cat_code && $current_cat ) {
	$cat_code = $cat_meta_map[ $current_cat->slug ] ?? '';
}
?>

<!-- Hero compatto categoria -->
<div class="archive-hero">
	<?php
	if ( $current_cat ) {
		$thumb_id = get_term_meta( $current_cat->term_id, 'thumbnail_id', true );
		if ( $thumb_id ) :
	?>
	<div class="archive-hero__bg" aria-hidden="true">
		<?php echo wp_get_attachment_image( $thumb_id, 'ms-hero', false, [ 'loading' => 'eager' ] ); ?>
	</div>
	<?php endif; } ?>

	<div class="archive-hero__content container">
		<?php if ( $cat_code ) : ?>
			<span class="label-section"><?php echo esc_html( $cat_code ); ?> / CATEGORIA</span>
		<?php endif; ?>

		<h1 class="archive-hero__title"><?php echo esc_html( $cat_name ); ?></h1>

		<?php if ( $product_count !== null ) : ?>
		<p class="archive-hero__count">
			<?php echo esc_html( number_format( (int) $product_count, 0, ',', '.' ) ); ?> PRODOTTI ATTIVI
		</p>
		<?php endif; ?>
	</div>
</div>

<div class="container">
	<div class="archive-layout">

		<!-- Sidebar Filtri -->
		<aside class="filters-sidebar" aria-label="<?php esc_attr_e( 'Filtri prodotto', 'mondosegnaletica' ); ?>">
			<form class="filters-form" method="get" action="<?php echo esc_url( get_pagenum_link( 1 ) ); ?>">

				<!-- Filtri WooCommerce nativi (widget) -->
				<?php
				// Se attivo il plugin WC Filter Products, usare i widget.
				// Qui rendiamo filtri manuali come fallback.

				$filter_groups = [
					'Tipologia' => [
						[ 'label' => 'Pericolo',     'value' => 'pericolo'    ],
						[ 'label' => 'Prescrizione', 'value' => 'prescrizione'],
						[ 'label' => 'Indicazione',  'value' => 'indicazione' ],
						[ 'label' => 'Temporanei',   'value' => 'temporanei'  ],
					],
					'Materiale' => [
						[ 'label' => 'Alluminio',    'value' => 'alluminio'   ],
						[ 'label' => 'Zincato',      'value' => 'zincato'     ],
						[ 'label' => 'Plastica',     'value' => 'plastica'    ],
					],
					'Classe Rifrangenza' => [
						[ 'label' => 'Classe 1',     'value' => 'classe-1'    ],
						[ 'label' => 'Classe 2',     'value' => 'classe-2'    ],
						[ 'label' => 'Classe 3',     'value' => 'classe-3'    ],
					],
				];

				foreach ( $filter_groups as $group_label => $items ) : ?>
				<div class="filter-group">
					<button type="button" class="filter-group__title">
						<?php echo esc_html( $group_label ); ?>
						<span class="material-symbols-outlined filter-group__toggle" aria-hidden="true">expand_more</span>
					</button>
					<div class="filter-group__body">
						<ul class="filter-list">
							<?php foreach ( $items as $item ) :
								$checked = in_array( $item['value'], (array) ( $_GET[ 'filter_' . sanitize_key( $group_label ) ] ?? [] ), true );
							?>
							<li class="filter-item">
								<label class="filter-item__label">
									<input
										type="checkbox"
										class="filter-item__checkbox"
										name="filter_<?php echo esc_attr( sanitize_key( $group_label ) ); ?>[]"
										value="<?php echo esc_attr( $item['value'] ); ?>"
										<?php checked( $checked ); ?>
									>
									<?php echo esc_html( $item['label'] ); ?>
								</label>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
				<?php endforeach; ?>

				<!-- Prezzo -->
				<div class="filter-group">
					<button type="button" class="filter-group__title">
						Prezzo
						<span class="material-symbols-outlined filter-group__toggle" aria-hidden="true">expand_more</span>
					</button>
					<div class="filter-group__body">
						<div class="filter-price">
							<div class="filter-price__range">
								<span>€ <?php echo esc_html( isset( $_GET['min_price'] ) ? (int) $_GET['min_price'] : 0 ); ?></span>
								<span>€ <?php echo esc_html( isset( $_GET['max_price'] ) ? (int) $_GET['max_price'] : 500 ); ?></span>
							</div>
							<input
								type="range"
								class="filter-price__slider"
								name="max_price"
								min="0"
								max="500"
								step="10"
								value="<?php echo esc_attr( isset( $_GET['max_price'] ) ? (int) $_GET['max_price'] : 500 ); ?>"
							>
						</div>
					</div>
				</div>

			</form>
		</aside>

		<!-- Griglia prodotti -->
		<div class="archive-main">

			<!-- Toolbar -->
			<div class="archive-toolbar">
				<p class="archive-toolbar__count">
					<?php
					global $wp_query;
					$total = $wp_query->found_posts;
					echo esc_html( number_format( $total, 0, ',', '.' ) ) . ' PRODOTTI';
					?>
				</p>

				<div class="toolbar-actions">
					<select class="sort-select" name="orderby" onchange="this.form ? this.form.submit() : window.location='?orderby='+this.value" aria-label="Ordina per">
						<?php $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'menu_order'; ?>
						<option value="menu_order"  <?php selected( $orderby, 'menu_order' ); ?>>Ordine predefinito</option>
						<option value="popularity"  <?php selected( $orderby, 'popularity' ); ?>>Più venduti</option>
						<option value="price"       <?php selected( $orderby, 'price' ); ?>>Prezzo crescente</option>
						<option value="price-desc"  <?php selected( $orderby, 'price-desc' ); ?>>Prezzo decrescente</option>
						<option value="date"        <?php selected( $orderby, 'date' ); ?>>Più recenti</option>
					</select>

					<div class="view-toggle" role="group" aria-label="Vista">
						<button type="button" class="view-toggle__btn view-toggle__btn--active" data-view="grid" aria-label="Vista griglia" aria-pressed="true">
							<span class="material-symbols-outlined" aria-hidden="true">grid_view</span>
						</button>
						<button type="button" class="view-toggle__btn" data-view="list" aria-label="Vista lista" aria-pressed="false">
							<span class="material-symbols-outlined" aria-hidden="true">view_list</span>
						</button>
					</div>
				</div>
			</div>

			<?php if ( woocommerce_product_loop() ) : ?>

			<ul class="products-grid" aria-label="<?php esc_attr_e( 'Prodotti', 'mondosegnaletica' ); ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					$product = wc_get_product( get_the_ID() );
					if ( ! $product instanceof WC_Product ) continue;
				?>
				<li>
					<?php ms_get_template_part( 'template-parts/product/card', [ 'product' => $product ] ); ?>
				</li>
				<?php endwhile; ?>
			</ul>

			<!-- Paginazione -->
			<?php
			$total_pages = $wp_query->max_num_pages;
			if ( $total_pages > 1 ) :
				$current_page = max( 1, get_query_var( 'paged' ) );
			?>
			<nav class="archive-pagination" aria-label="<?php esc_attr_e( 'Paginazione prodotti', 'mondosegnaletica' ); ?>">
				<?php
				echo paginate_links( [
					'total'     => $total_pages,
					'current'   => $current_page,
					'prev_text' => '<span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>',
					'next_text' => '<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>',
					'type'      => 'list',
					'before_page_number' => '<span class="pagination__item">',
					'after_page_number'  => '</span>',
				] );
				?>
			</nav>
			<?php endif; ?>

			<?php else : ?>
				<p style="font-family:var(--font-mono);font-size:var(--text-sm);color:var(--color-text-muted);padding:var(--space-12) 0;">
					Nessun prodotto trovato per i filtri selezionati.
				</p>
			<?php endif; ?>

		</div><!-- /.archive-main -->

	</div><!-- /.archive-layout -->
</div><!-- /.container -->

<?php get_footer( 'shop' ); ?>
