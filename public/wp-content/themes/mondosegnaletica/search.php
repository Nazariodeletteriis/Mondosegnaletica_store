<?php
/**
 * Pagina risultati ricerca.
 *
 * I prodotti finiscono nella griglia .products-grid (stessa card del catalogo),
 * gli altri contenuti in una lista .search-posts.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ms_query   = get_search_query();
$ms_found   = (int) ( $GLOBALS['wp_query']->found_posts ?? 0 );
$ms_shop_url = function_exists( 'wc_get_page_permalink' )
	? ( wc_get_page_permalink( 'shop' ) ?: home_url( '/negozio' ) )
	: home_url( '/negozio' );

// Separiamo prodotti e contenuti prima di stampare: due contenitori distinti,
// nessun tag lasciato aperto fra i rami condizionali.
$ms_products = [];
$ms_posts    = [];

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

		if ( 'product' === get_post_type() && function_exists( 'wc_get_product' ) ) {
			$ms_product = wc_get_product( get_the_ID() );
			if ( $ms_product instanceof WC_Product ) {
				$ms_products[] = $ms_product;
				continue;
			}
		}

		$ms_posts[] = [
			'id'      => get_the_ID(),
			'title'   => get_the_title(),
			'link'    => get_permalink(),
			'excerpt' => wp_strip_all_tags( get_the_excerpt() ),
			'type'    => get_post_type(),
		];
	}
	rewind_posts();
}
?>

<main id="main" class="site-main" role="main">
	<div class="container page-wrap">

		<header class="page-hero">
			<span class="label-section"><?php esc_html_e( 'MS / RICERCA', 'mondosegnaletica' ); ?></span>
			<h1 class="page-hero__title">
				<?php
				if ( '' !== $ms_query ) {
					printf(
						/* translators: %s: termine cercato */
						esc_html__( 'Risultati per “%s”', 'mondosegnaletica' ),
						esc_html( $ms_query )
					);
				} else {
					esc_html_e( 'Cerca nel catalogo', 'mondosegnaletica' );
				}
				?>
			</h1>

			<form class="search-form-inline" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="sr-only" for="ms-search-field"><?php esc_html_e( 'Cerca prodotti', 'mondosegnaletica' ); ?></label>
				<input
					class="search-form-inline__input"
					type="search"
					id="ms-search-field"
					name="s"
					value="<?php echo esc_attr( $ms_query ); ?>"
					placeholder="<?php esc_attr_e( 'Codice, categoria o nome del segnale…', 'mondosegnaletica' ); ?>"
					autocomplete="off"
				>
				<button class="search-form-inline__submit" type="submit">
					<span class="material-symbols-outlined" aria-hidden="true">search</span>
					<?php esc_html_e( 'Cerca', 'mondosegnaletica' ); ?>
				</button>
			</form>
		</header>

		<?php if ( $ms_products || $ms_posts ) : ?>

			<p class="search-count">
				<?php
				printf(
					/* translators: %s: numero risultati */
					esc_html( _n( '%s risultato', '%s risultati', $ms_found, 'mondosegnaletica' ) ),
					'<strong>' . esc_html( number_format_i18n( $ms_found ) ) . '</strong>'
				);
				?>
			</p>

			<?php if ( $ms_products ) : ?>
				<div class="products-grid">
					<?php
					foreach ( $ms_products as $ms_product ) {
						ms_get_template_part( 'template-parts/product/card', [ 'product' => $ms_product ] );
					}
					?>
				</div>
			<?php endif; ?>

			<?php if ( $ms_posts ) : ?>
				<div class="search-posts">
					<?php foreach ( $ms_posts as $ms_post ) : ?>
						<article class="search-post">
							<span class="search-post__type">
								<?php
								$ms_type_obj = get_post_type_object( $ms_post['type'] );
								echo esc_html( $ms_type_obj ? $ms_type_obj->labels->singular_name : $ms_post['type'] );
								?>
							</span>
							<h2 class="search-post__title">
								<a href="<?php echo esc_url( $ms_post['link'] ); ?>"><?php echo esc_html( $ms_post['title'] ); ?></a>
							</h2>
							<?php if ( $ms_post['excerpt'] ) : ?>
								<p class="search-post__excerpt"><?php echo esc_html( wp_trim_words( $ms_post['excerpt'], 34 ) ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			the_posts_pagination( [
				'class'              => 'search-pagination',
				'mid_size'           => 2,
				'prev_text'          => esc_html__( 'Prec.', 'mondosegnaletica' ),
				'next_text'          => esc_html__( 'Succ.', 'mondosegnaletica' ),
				'screen_reader_text' => esc_html__( 'Navigazione risultati', 'mondosegnaletica' ),
			] );
			?>

		<?php else : ?>

			<div class="empty-state">
				<span class="empty-state__label"><?php esc_html_e( 'Nessun risultato', 'mondosegnaletica' ); ?></span>
				<h2 class="empty-state__title"><?php esc_html_e( 'Fuori catalogo.', 'mondosegnaletica' ); ?></h2>
				<p class="empty-state__text">
					<?php
					if ( '' !== $ms_query ) {
						printf(
							/* translators: %s: termine cercato */
							esc_html__( 'Nessun prodotto o contenuto corrisponde a “%s”. Verificate il termine oppure partite dal catalogo: se l\'articolo non è a listino possiamo comunque fornirlo su richiesta.', 'mondosegnaletica' ),
							esc_html( $ms_query )
						);
					} else {
						esc_html_e( 'Inserite un termine di ricerca: codice articolo, categoria o denominazione del segnale.', 'mondosegnaletica' );
					}
					?>
				</p>

				<div>
					<p class="empty-state__suggestions-label"><?php esc_html_e( 'Punti di partenza', 'mondosegnaletica' ); ?></p>
					<div class="empty-state__links">
						<a href="<?php echo esc_url( $ms_shop_url ); ?>"><?php esc_html_e( 'Catalogo completo', 'mondosegnaletica' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/soluzioni/' ) ); ?>"><?php esc_html_e( 'Soluzioni', 'mondosegnaletica' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/cantieri/' ) ); ?>"><?php esc_html_e( 'Cantieri', 'mondosegnaletica' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/contatti/' ) ); ?>"><?php esc_html_e( 'Contatti', 'mondosegnaletica' ); ?></a>
					</div>
				</div>

				<a href="<?php echo esc_url( home_url( '/richiedi-preventivo/' ) ); ?>" class="btn btn--primary">
					<?php esc_html_e( 'Richiedi il prodotto', 'mondosegnaletica' ); ?>
					<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
				</a>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
