<?php
/**
 * Hero Homepage — Sistema Strada v2
 * Video full-screen raw, content bottom-anchored, 2-col desktop.
 * Animazioni: char-by-char headline + fadein progressive via hero.js (vanilla).
 * GSAP ScrollTrigger integrato in fase 2.
 */

$shop_url        = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/negozio' );
$contatti_url    = home_url( '/contatti' );
?>

<section class="hero" id="hero" aria-label="<?php esc_attr_e( 'Mondo Segnaletica — Segnaletica stradale omologata B2B', 'mondosegnaletica' ); ?>">

	<!-- Background image -->
	<div class="hero__media" aria-hidden="true">
		<picture>
			<source srcset="<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-bg.webp' ); ?>" type="image/webp">
			<img
				class="hero__bg-img"
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-bg.webp' ); ?>"
				alt=""
				loading="eager"
				decoding="async"
				width="1672"
				height="941"
			>
		</picture>
	</div>

	<!-- Content: ancorato al bottom viewport — solo colonna testo -->
	<div class="hero__body container">
		<div class="hero__grid">

			<!-- Headline + subtitle + CTA: larghezza piena -->
			<div class="hero__left">

				<!-- Label sezione mono giallo -->
				<span class="label-section hero__eyebrow">01 / SISTEMA STRADA</span>

				<!-- Headline — Anton, char-by-char animation via JS -->
				<h1 class="hero__headline js-char-animate" data-delay="200">
					La segnaletica<br>che tiene la strada<br><em>in ordine.</em>
				</h1>

				<!-- Subtitle -->
				<p class="hero__sub js-fadein" data-delay="800">
					Fornitura B2B di segnaletica stradale omologata Codice della Strada.
					1.200+ prodotti in pronta consegna. Spedizione 24/48h da Lucca.
				</p>

				<!-- CTA -->
				<div class="hero__actions js-fadein" data-delay="1200">
					<a href="<?php echo esc_url( $shop_url ?: home_url( '/negozio' ) ); ?>" class="btn btn--primary btn--large">
						Esplora il Catalogo
						<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
					</a>
					<a href="<?php echo esc_url( $contatti_url ); ?>" class="btn hero__btn-glass">
						Richiedi Preventivo
					</a>
				</div>

			</div>

		</div>
	</div>

	<!-- Tag glass categorie — fuori da .hero__body, position: absolute rispetto a .hero -->
	<div class="hero__right js-fadein" data-delay="1400" aria-hidden="true">
		<div class="liquid-glass hero__tag">
			<span>Segnaletica Verticale</span>
			<span class="hero__tag-sep" aria-hidden="true">·</span>
			<span>Orizzontale</span>
			<span class="hero__tag-sep" aria-hidden="true">·</span>
			<span>Cantieristica</span>
		</div>
	</div>

	<!-- Scroll hint -->
	<div class="hero__scroll-hint" aria-hidden="true">Scroll</div>

</section>
