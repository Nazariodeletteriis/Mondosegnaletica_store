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
$cat_name     = $current_cat ? $current_cat->name : __( 'Catalogo', 'mondosegnaletica' );
$cat_desc     = $current_cat ? $current_cat->description : '';

$cat_meta_map = [
	// Macro-categorie
	'segnaletica-stradale-cantieristica-e-accessori' => 'CAT-ST',
	'segnaletica-di-sicurezza'                       => 'CAT-SI',
	'segnaletica-aziendale-privata-e-accessori'      => 'CAT-AZ',
	'adr-e-segnaletica-per-mezzi-da-lavoro'          => 'CAT-AD',
	// Sotto-categorie
	'segnaletica-verticale'                          => 'CAT-01',
	'segnaletica-orizzontale'                        => 'CAT-02',
	'coni-transenne'                                 => 'CAT-03',
	'delineatori-paletti'                            => 'CAT-04',
	'cantieristica'                                  => 'CAT-05',
	'dissuasori-accessori'                           => 'CAT-06',
];

$cat_code = '';
if ( $current_cat ) {
	$cat_code = get_term_meta( $current_cat->term_id, 'ms_cat_code', true );
	if ( ! $cat_code ) {
		$cat_code = $cat_meta_map[ $current_cat->slug ] ?? '';
	}
}

global $wp_query;
$total_products = (int) $wp_query->found_posts;
?>

<!-- ─── Hero compatto categoria ─── -->
<div class="archive-hero">
	<?php if ( $current_cat ) :
		$thumb_id = get_term_meta( $current_cat->term_id, 'thumbnail_id', true );
		if ( $thumb_id ) : ?>
		<div class="archive-hero__bg" aria-hidden="true">
			<?php echo wp_get_attachment_image( $thumb_id, 'ms-hero', false, [ 'loading' => 'eager' ] ); ?>
		</div>
	<?php endif; endif; ?>

	<div class="archive-hero__content container">
		<?php if ( $cat_code ) : ?>
		<span class="label-section archive-hero__label"><?php echo esc_html( $cat_code ); ?> / CATALOGO</span>
		<?php endif; ?>

		<nav class="archive-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOME</a>
			<span aria-hidden="true"> / </span>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">CATALOGO</a>
			<?php if ( $current_cat ) : ?>
				<span aria-hidden="true"> / </span>
				<span aria-current="page"><?php echo esc_html( strtoupper( $cat_name ) ); ?></span>
			<?php endif; ?>
		</nav>

		<h1 class="archive-hero__title"><?php echo esc_html( strtoupper( $cat_name ) ); ?></h1>

		<p class="archive-hero__count">
			<?php echo esc_html( number_format( $total_products, 0, ',', '.' ) ); ?> PRODOTTI ATTIVI
		</p>
	</div>
</div>

<!-- ─── Layout: sidebar + griglia ─── -->
<div class="container">
	<div class="archive-layout">

		<!-- Sidebar Filtri -->
		<aside class="filters-sidebar" aria-label="Filtri prodotto">

			<?php
			// Attributi da mostrare — esclude taglia/dimensione (dati non puliti)
			$filter_attr_slugs    = [ 'pa_tipologia', 'pa_formato', 'pa_classe-rifrangenza' ];
			$attribute_taxonomies = wc_get_attribute_taxonomies();
			$attribute_taxonomies = array_values( array_filter(
				$attribute_taxonomies,
				fn( $a ) => in_array( 'pa_' . $a->attribute_name, $filter_attr_slugs, true )
			) );

			// Stato filtri attivi per ogni attributo
			$active_filters = [];
			foreach ( $attribute_taxonomies as $attr ) {
				$param = 'filter_pa_' . $attr->attribute_name;
				if ( ! empty( $_GET[ $param ] ) ) {
					$raw = wp_unslash( $_GET[ $param ] );
					$slugs = is_array( $raw )
						? $raw
						: explode( ',', $raw );
					$active_filters[ $attr->attribute_name ] = array_values(
						array_filter( array_map( 'sanitize_title', $slugs ) )
					);
				}
			}
			$has_active_filters = ! empty( $active_filters );

			// URL reset — rimuove tutti i params filtro
			$reset_url = remove_query_arg(
				array_map( fn( $a ) => 'filter_pa_' . $a->attribute_name, $attribute_taxonomies )
			);
			?>

			<!-- Sidebar header -->
			<div class="filters-header">
				<span class="label-mono">Filtri</span>
				<?php if ( $has_active_filters ) : ?>
				<a href="<?php echo esc_url( $reset_url ); ?>" class="filters-header__reset">
					<span class="material-symbols-outlined" aria-hidden="true">close</span>
					Reset
				</a>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $attribute_taxonomies ) ) : ?>

			<?php
				$_form_action = $current_cat ? get_term_link( $current_cat ) : null;
				if ( ! $_form_action || is_wp_error( $_form_action ) ) {
					$_shop_url    = wc_get_page_permalink( 'shop' );
					$_form_action = ( $_shop_url && ! is_wp_error( $_shop_url ) ) ? $_shop_url : home_url( '/negozio/' );
				}
				?>
			<form class="filters-form" method="get" action="<?php echo esc_url( $_form_action ); ?>">

				<?php foreach ( $attribute_taxonomies as $attr ) :
					$taxonomy = 'pa_' . $attr->attribute_name;
					$param    = 'filter_' . $taxonomy;
					$selected = $active_filters[ $attr->attribute_name ] ?? [];

					$terms = get_terms( [
						'taxonomy'   => $taxonomy,
						'hide_empty' => true,
						'orderby'    => 'count',
						'order'      => 'DESC',
					] );

					if ( is_wp_error( $terms ) || empty( $terms ) ) continue;
				?>
				<div class="filter-group">
					<button type="button" class="filter-group__title js-filter-toggle" aria-expanded="true">
						<?php echo esc_html( strtoupper( $attr->attribute_label ) ); ?>
						<span class="material-symbols-outlined filter-group__toggle" aria-hidden="true">expand_more</span>
					</button>
					<div class="filter-group__body">
						<ul class="filter-list" role="list">
							<?php foreach ( $terms as $term ) :
								$is_selected = in_array( $term->slug, $selected, true );
							?>
							<li class="filter-item">
								<label class="filter-item__label <?php echo $is_selected ? 'filter-item__label--active' : ''; ?>">
									<span class="filter-item__checkbox-wrap">
										<input type="checkbox"
											   name="filter_pa_<?php echo esc_attr( $attr->attribute_name ); ?>[]"
											   value="<?php echo esc_attr( $term->slug ); ?>"
											   class="filter-item__check-input"
											   <?php checked( $is_selected ); ?>>
										<span class="filter-item__checkbox-visual" aria-hidden="true"></span>
									</span>
									<span class="filter-item__name"><?php echo esc_html( $term->name ); ?></span>
									<span class="filter-item__count">(<?php echo esc_html( $term->count ); ?>)</span>
								</label>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
				<?php endforeach; ?>

			</form>

			<?php else : ?>
				<!-- Nessun attributo configurato — navigazione categorie fallback -->
				<div class="filter-group">
					<span class="filter-group__title">CATEGORIE</span>
					<div class="filter-group__body">
						<ul class="filter-list" role="list">
							<li class="filter-item">
								<a class="filter-item__label <?php echo ( ! $is_category ) ? 'filter-item__label--active' : ''; ?>"
								   href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
									Tutto il catalogo
								</a>
							</li>
							<?php
							$sidebar_cats    = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0 ] );
							$ordered_sidebar = [];
							foreach ( $cat_meta_map as $slug => $code ) {
								foreach ( $sidebar_cats as $c ) {
									if ( $c->slug === $slug ) { $ordered_sidebar[] = $c; break; }
								}
							}
							foreach ( $ordered_sidebar as $c ) :
								$active = $is_category && $current_cat && $current_cat->slug === $c->slug;
							?>
							<li class="filter-item">
								<a class="filter-item__label <?php echo $active ? 'filter-item__label--active' : ''; ?>"
								   href="<?php echo esc_url( get_term_link( $c ) ); ?>">
									<?php echo esc_html( $c->name ); ?>
								</a>
								<span class="filter-item__count">(<?php echo esc_html( $c->count ); ?>)</span>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>

		</aside>

		<!-- Griglia prodotti -->
		<div class="archive-main">

			<!-- Toolbar -->
			<div class="archive-toolbar">
				<p class="archive-toolbar__count">
					<?php
					$per_page     = (int) $wp_query->get( 'posts_per_page' ) ?: 12;
					$paged        = max( 1, (int) $wp_query->get( 'paged' ) );
					$from         = ( $paged - 1 ) * $per_page + 1;
					$to           = min( $paged * $per_page, $total_products );
					if ( $total_products > 0 ) {
						echo esc_html( "MOSTRANDO {$from}–{$to} DI {$total_products} RISULTATI" );
					} else {
						echo 'NESSUN RISULTATO';
					}
					?>
				</p>

				<div class="toolbar-actions">
					<select class="sort-select"
					        name="orderby"
					        onchange="window.location='<?php echo esc_url( remove_query_arg( 'orderby' ) ); ?>&amp;orderby='+this.value"
					        aria-label="Ordina per">
						<?php $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'menu_order'; ?>
						<option value="menu_order" <?php selected( $orderby, 'menu_order' ); ?>>Ordine predefinito</option>
						<option value="popularity" <?php selected( $orderby, 'popularity' ); ?>>Più venduti</option>
						<option value="price"      <?php selected( $orderby, 'price' ); ?>>Prezzo crescente</option>
						<option value="price-desc" <?php selected( $orderby, 'price-desc' ); ?>>Prezzo decrescente</option>
						<option value="title"      <?php selected( $orderby, 'title' ); ?>>Alfabetico</option>
					</select>

					<div class="view-toggle" role="group" aria-label="Vista">
						<button type="button" class="view-toggle__btn view-toggle__btn--active js-view-toggle"
						        data-view="grid" aria-label="Vista griglia" aria-pressed="true">
							<span class="material-symbols-outlined" aria-hidden="true">grid_view</span>
						</button>
						<button type="button" class="view-toggle__btn js-view-toggle"
						        data-view="list" aria-label="Vista lista" aria-pressed="false">
							<span class="material-symbols-outlined" aria-hidden="true">view_list</span>
						</button>
					</div>
				</div>
			</div>

			<?php if ( woocommerce_product_loop() ) : ?>

			<ul class="products-grid" aria-label="Prodotti" id="products-grid">
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
			$total_pages  = $wp_query->max_num_pages;
			$current_page = max( 1, get_query_var( 'paged' ) );
			if ( $total_pages > 1 ) : ?>
			<nav class="archive-pagination" aria-label="Paginazione prodotti">
				<?php
				echo paginate_links( [
					'total'              => $total_pages,
					'current'            => $current_page,
					'prev_text'          => '<span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>',
					'next_text'          => '<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>',
					'type'               => 'list',
					'before_page_number' => '<span class="pagination__item">',
					'after_page_number'  => '</span>',
				] );
				?>
			</nav>
			<?php endif; ?>

			<?php else : ?>
				<p class="archive-empty">Nessun prodotto trovato per i filtri selezionati.</p>
			<?php endif; ?>

		</div><!-- /.archive-main -->

	</div><!-- /.archive-layout -->
</div><!-- /.container -->

<?php get_footer( 'shop' ); ?>
