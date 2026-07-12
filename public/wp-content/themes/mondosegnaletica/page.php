<?php
/**
 * Template pagine statiche WordPress.
 *
 * Due modalità:
 * - Pagina account WooCommerce  → shell area clienti (nav + pannello).
 * - Pagina di contenuto normale → hero editoriale + prosa .page-body.
 *
 * Il contenuto delle pagine è HTML curato: disattiviamo wpautop per non
 * far iniettare a WordPress <p>/<br> spuri dentro i blocchi tecnici.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ms_is_account = function_exists( 'is_account_page' ) && is_account_page();

if ( $ms_is_account ) :

	// Titolo dinamico in base all'endpoint corrente (Ordini, Indirizzi, ecc.).
	$ms_endpoint = '';
	if ( function_exists( 'WC' ) && WC()->query && method_exists( WC()->query, 'get_current_endpoint' ) ) {
		$ms_endpoint = (string) WC()->query->get_current_endpoint();
	}

	if ( ! is_user_logged_in() ) {
		$ms_account_title = __( 'Area Clienti', 'mondosegnaletica' );
		$ms_account_sub   = __( 'Accesso riservato a imprese, enti pubblici e professionisti della viabilità. Da qui gestite ordini, indirizzi di consegna e dati di fatturazione.', 'mondosegnaletica' );
	} else {
		$ms_account_title = __( 'Pannello Cliente', 'mondosegnaletica' );
		if ( $ms_endpoint && method_exists( WC()->query, 'get_endpoint_title' ) ) {
			$ms_endpoint_title = WC()->query->get_endpoint_title( $ms_endpoint );
			if ( $ms_endpoint_title ) {
				$ms_account_title = $ms_endpoint_title;
			}
		}
		$ms_account_sub = '';
	}
	?>

	<main id="main" class="site-main" role="main">
		<div class="container account-wrap<?php echo is_user_logged_in() ? '' : ' account-wrap--guest'; ?>">

			<header class="account-hero">
				<span class="label-section"><?php esc_html_e( 'MS / AREA CLIENTI', 'mondosegnaletica' ); ?></span>
				<h1 class="account-hero__title"><?php echo esc_html( $ms_account_title ); ?></h1>
				<?php if ( $ms_account_sub ) : ?>
					<p class="account-hero__sub"><?php echo esc_html( $ms_account_sub ); ?></p>
				<?php endif; ?>
				<div class="account-hero__hud" aria-hidden="true">
					<span><?php esc_html_e( 'Prezzi IVA esclusa', 'mondosegnaletica' ); ?></span>
					<span><?php esc_html_e( 'Spedizione 24/48h da Lucca', 'mondosegnaletica' ); ?></span>
					<span>43.8438° N · 10.5061° E</span>
				</div>
			</header>

			<div class="account-body">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>

		</div>
	</main>

<?php else : ?>

	<main id="main" class="site-main" role="main">
		<div class="container page-wrap">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<header class="page-hero">
						<nav class="page-hero__breadcrumb" aria-label="<?php esc_attr_e( 'Percorso', 'mondosegnaletica' ); ?>">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'mondosegnaletica' ); ?></a>
							<span aria-hidden="true">›</span>
							<span aria-current="page"><?php the_title(); ?></span>
						</nav>

						<h1 class="page-hero__title"><?php the_title(); ?></h1>

						<div class="page-hero__hud" aria-hidden="true">
							<span>Lucca · Toscana</span>
							<span>43.8438° N · 10.5061° E</span>
							<span><?php esc_html_e( 'Omologato Codice della Strada', 'mondosegnaletica' ); ?></span>
						</div>
					</header>

					<div class="page-body">
						<?php
						remove_filter( 'the_content', 'wpautop' );
						the_content();
						?>
					</div>

				</article>
			<?php endwhile; ?>
		</div>
	</main>

<?php endif; ?>

<?php get_footer(); ?>
