<?php
/**
 * Template pagina Richiedi Preventivo (slug: richiedi-preventivo).
 *
 * Destinazione della CTA B2B del carrello e delle PDP.
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/template-parts/page/form-handler.php';

$ms_form = ms_page_form_process( 'quote' );

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

					<h1 class="page-hero__title"><?php esc_html_e( 'Preventivo per grandi quantità.', 'mondosegnaletica' ); ?></h1>

					<div class="page-hero__hud" aria-hidden="true">
						<span><?php esc_html_e( 'Sconti a fasce da 10 pezzi', 'mondosegnaletica' ); ?></span>
						<span><?php esc_html_e( 'Fatturazione elettronica PA', 'mondosegnaletica' ); ?></span>
						<span><?php esc_html_e( 'Spedizione 24/48h da Lucca', 'mondosegnaletica' ); ?></span>
					</div>
				</header>

				<div class="page-split page-split--quote">

					<div class="page-body page-body--intro">
						<?php
						remove_filter( 'the_content', 'wpautop' );
						the_content();
						?>
					</div>

					<?php
					ms_get_template_part( 'template-parts/page/form-preventivo', [
						'ms_form' => $ms_form,
					] );
					?>

				</div>

			</article>
		<?php endwhile; ?>
	</div>
</main>

<?php get_footer(); ?>
