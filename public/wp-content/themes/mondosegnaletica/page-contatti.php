<?php
/**
 * Template pagina Contatti (slug: contatti).
 *
 * Riceve anche il POST del form contatti della homepage e il deep-link
 * dalla PDP: /contatti?prodotto=<slug>.
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/template-parts/page/form-handler.php';

$ms_form = ms_page_form_process( 'contact' );

// Prodotto di riferimento: da querystring (link dalla PDP) o dal POST in errore.
$ms_prodotto = ms_page_form_value( $ms_form, 'prodotto' );

if ( '' === $ms_prodotto && ! empty( $_GET['prodotto'] ) ) {
	$ms_slug    = sanitize_title( wp_unslash( (string) $_GET['prodotto'] ) );
	$ms_product = $ms_slug ? get_page_by_path( $ms_slug, OBJECT, 'product' ) : null;

	if ( $ms_product instanceof WP_Post ) {
		$ms_prodotto = get_the_title( $ms_product );
	}
}

get_header();
?>

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

					<h1 class="page-hero__title"><?php esc_html_e( 'Parliamo di segnaletica.', 'mondosegnaletica' ); ?></h1>

					<div class="page-hero__hud" aria-hidden="true">
						<span>Lucca · Toscana</span>
						<span>43.8438° N · 10.5061° E</span>
						<span><?php esc_html_e( 'Risposta entro 1 giorno lavorativo', 'mondosegnaletica' ); ?></span>
					</div>
				</header>

				<div class="page-split">

					<div class="contact-block">
						<div class="contact-block__group">
							<span class="contact-block__label"><?php esc_html_e( 'Telefono', 'mondosegnaletica' ); ?></span>
							<a class="contact-block__tel" href="tel:+3905831646327">0583 1646327</a>
							<span class="contact-block__hours"><?php esc_html_e( 'Lun–Ven · 08:00–18:00', 'mondosegnaletica' ); ?></span>
						</div>

						<div class="contact-block__group">
							<span class="contact-block__label"><?php esc_html_e( 'Email', 'mondosegnaletica' ); ?></span>
							<a class="contact-block__mail" href="mailto:info@mondosegnaletica.it">info@mondosegnaletica.it</a>
						</div>

						<div class="contact-block__group">
							<span class="contact-block__label"><?php esc_html_e( 'Sede e magazzino', 'mondosegnaletica' ); ?></span>
							<address class="contact-block__address">
								Mondo Segnaletica Soc. Coop.<br>
								Via Carlo Angeloni 360 — Lucca (LU)<br>
								Magazzino: Viale Europa 50 — Lammari (LU)<br>
								P.IVA 02629010460
							</address>
						</div>

						<div class="contact-block__group">
							<span class="contact-block__label"><?php esc_html_e( 'Ritiro in sede', 'mondosegnaletica' ); ?></span>
							<span class="contact-block__hours">
								<?php esc_html_e( 'Lun–Ven · 08:00–12:30 / 14:00–17:30', 'mondosegnaletica' ); ?><br>
								<?php esc_html_e( 'Su appuntamento telefonico', 'mondosegnaletica' ); ?>
							</span>
						</div>
					</div>

					<?php
					ms_get_template_part( 'template-parts/page/form-contatto', [
						'ms_form'     => $ms_form,
						'ms_prodotto' => $ms_prodotto,
					] );
					?>

				</div>

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

<?php get_footer(); ?>
