<?php
/**
 * Hero Homepage — full-viewport, video/foto placeholder.
 * GSAP ScrollTrigger + Lenis smooth scroll vengono in fase 2.
 */

$hero_video = get_theme_mod( 'ms_hero_video', '' );
$hero_image = get_theme_mod( 'ms_hero_image', '' );
?>

<section class="hero" aria-label="<?php esc_attr_e( 'Mondo Segnaletica — Hero', 'mondosegnaletica' ); ?>">

	<!-- Media: video > immagine > fallback colore -->
	<div class="hero__media" aria-hidden="true">
		<?php if ( $hero_video ) : ?>
			<video
				autoplay
				muted
				loop
				playsinline
				preload="metadata"
				aria-hidden="true"
			>
				<source src="<?php echo esc_url( $hero_video ); ?>" type="video/mp4">
			</video>
		<?php elseif ( $hero_image ) : ?>
			<img
				src="<?php echo esc_url( $hero_image ); ?>"
				alt=""
				aria-hidden="true"
				loading="eager"
				fetchpriority="high"
			>
		<?php else : ?>
			<!-- Placeholder cinematico: gradiente asfalto -->
			<div style="width:100%;height:100%;background:linear-gradient(135deg,#0A0A0A 0%,#1a1a1a 50%,#0A0A0A 100%);"></div>
		<?php endif; ?>
	</div>

	<div class="hero__overlay" aria-hidden="true"></div>

	<div class="hero__content container">
		<div class="hero__eyebrow">
			<span class="label-section">01 / SISTEMA STRADA</span>
		</div>

		<h1 class="hero__headline">
			La segnaletica<br>
			che tiene la strada<br>
			<em>in ordine.</em>
		</h1>

		<p class="hero__sub">
			Fornitura B2B di segnaletica stradale omologata Codice della Strada. 1.200+ prodotti in pronta consegna. Spedizione in 24/48h da Lucca.
		</p>

		<div class="hero__actions">
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ?: home_url( '/negozio' ) ); ?>" class="btn btn--primary btn--large">
				Esplora il Catalogo
				<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
			</a>
			<a href="<?php echo esc_url( home_url( '/contatti' ) ); ?>" class="btn btn--outline btn--large">
				Richiedi Preventivo
			</a>
		</div>
	</div>

	<div class="hero__scroll-hint" aria-hidden="true">Scroll</div>

</section>
