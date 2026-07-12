<?php
/**
 * 404 — pagina non trovata.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ms_shop_url = function_exists( 'wc_get_page_permalink' )
	? ( wc_get_page_permalink( 'shop' ) ?: home_url( '/negozio' ) )
	: home_url( '/negozio' );
?>

<main id="main" class="site-main" role="main">
	<div class="container">
		<section class="error-404">

			<span class="error-404__code"><?php esc_html_e( 'Errore 404', 'mondosegnaletica' ); ?></span>

			<h1 class="error-404__title">
				<?php esc_html_e( 'Strada', 'mondosegnaletica' ); ?><br><?php esc_html_e( 'non trovata.', 'mondosegnaletica' ); ?>
			</h1>

			<p class="error-404__text">
				<?php esc_html_e( 'La pagina che cercate non esiste o è stata spostata. Riprendete dal catalogo oppure cercate il prodotto per codice o denominazione.', 'mondosegnaletica' ); ?>
			</p>

			<div class="error-404__actions">
				<a href="<?php echo esc_url( $ms_shop_url ); ?>" class="btn btn--primary btn--large">
					<?php esc_html_e( 'Vai al catalogo', 'mondosegnaletica' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--outline btn--large">
					<?php esc_html_e( 'Torna alla home', 'mondosegnaletica' ); ?>
				</a>
			</div>

			<form class="search-form-inline" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="margin-top:var(--space-12)">
				<label class="sr-only" for="ms-404-search"><?php esc_html_e( 'Cerca nel catalogo', 'mondosegnaletica' ); ?></label>
				<input
					class="search-form-inline__input"
					type="search"
					id="ms-404-search"
					name="s"
					placeholder="<?php esc_attr_e( 'Codice, categoria o nome del segnale…', 'mondosegnaletica' ); ?>"
					autocomplete="off"
				>
				<button class="search-form-inline__submit" type="submit">
					<span class="material-symbols-outlined" aria-hidden="true">search</span>
					<?php esc_html_e( 'Cerca', 'mondosegnaletica' ); ?>
				</button>
			</form>

			<div class="error-404__hud" aria-hidden="true">
				<span>Lucca · Toscana</span>
				<span>43.8438° N · 10.5061° E</span>
				<span><?php esc_html_e( 'Spedizione 24/48h', 'mondosegnaletica' ); ?></span>
			</div>

		</section>
	</div>
</main>

<?php get_footer(); ?>
