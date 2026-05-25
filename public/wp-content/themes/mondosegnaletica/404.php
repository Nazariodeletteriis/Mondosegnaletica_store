<?php
/**
 * 404 — pagina non trovata.
 */
get_header();
?>

<main id="main" class="site-main" role="main">
	<div class="container" style="padding-block:var(--space-32);text-align:center;">
		<span class="label-mono" style="color:var(--color-error);">ERRORE 404</span>
		<h1 class="section-title" style="font-size:clamp(64px,12vw,160px);margin-top:var(--space-4);margin-bottom:var(--space-6);">
			Strada<br>non trovata.
		</h1>
		<p style="color:var(--color-text-muted);max-width:40ch;margin-inline:auto;margin-bottom:var(--space-8);">
			La pagina che cerchi non esiste o è stata spostata. Torna al catalogo.
		</p>
		<div style="display:flex;justify-content:center;gap:var(--space-4);">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary btn--large">
				Torna all'Home
			</a>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ?: home_url( '/negozio' ) ); ?>" class="btn btn--outline btn--large">
				Vai al Catalogo
			</a>
		</div>
	</div>
</main>

<?php get_footer(); ?>
