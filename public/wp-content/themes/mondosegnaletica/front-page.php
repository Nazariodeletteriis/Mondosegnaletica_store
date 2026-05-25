<?php
/**
 * Homepage — front-page.php
 * Chiamato quando WordPress ha una pagina statica come home.
 */

get_header();
?>

<main id="main" class="site-main" role="main" aria-label="<?php esc_attr_e( 'Contenuto principale', 'mondosegnaletica' ); ?>">

	<?php get_template_part( 'template-parts/home/hero' ); ?>

	<?php get_template_part( 'template-parts/home/categories' ); ?>

	<?php get_template_part( 'template-parts/home/bestseller' ); ?>

	<?php get_template_part( 'template-parts/home/performance-stats' ); ?>

	<!-- 05 / CONTATTI -->
	<section class="section-contacts" id="contatti" aria-labelledby="contacts-title">
		<div class="container">
			<div class="section-header">
				<span class="label-section">05 / CONTATTI</span>
				<h2 class="section-title" id="contacts-title">Parliamo di<br>segnaletica.</h2>
			</div>

			<div class="contacts-grid">
				<div class="contacts-info">
					<a href="tel:+390583000000" class="contacts-info__tel">+39 0583 000 000</a>
					<address class="contacts-info__address">
						Mondo Segnaletica Soc. Coop.<br>
						Provincia di Lucca — Toscana<br>
						<a href="mailto:info@mondosegnaletica.it" style="color:inherit;">info@mondosegnaletica.it</a>
					</address>
					<p style="font-family:var(--font-mono);font-size:var(--text-xs);color:var(--color-text-muted);letter-spacing:0.1em;text-transform:uppercase;">
						Lun–Ven · 08:00–18:00
					</p>
				</div>

				<form class="contact-form" method="post" action="<?php echo esc_url( home_url( '/contatti' ) ); ?>" novalidate>
					<?php wp_nonce_field( 'ms_contact_form', 'ms_contact_nonce' ); ?>

					<div class="form-field">
						<label class="form-field__label" for="contact-ragione-sociale">Ragione Sociale *</label>
						<input class="form-field__input" type="text" id="contact-ragione-sociale" name="ragione_sociale" required autocomplete="organization" placeholder="Nome azienda / Ente pubblico">
					</div>

					<div class="form-field">
						<label class="form-field__label" for="contact-email">Email *</label>
						<input class="form-field__input" type="email" id="contact-email" name="email" required autocomplete="email" placeholder="email@azienda.it">
					</div>

					<div class="form-field">
						<label class="form-field__label" for="contact-messaggio">Richiesta</label>
						<textarea class="form-field__textarea" id="contact-messaggio" name="messaggio" placeholder="Descrivete la vostra esigenza: tipologia segnali, quantità, tempistiche..."></textarea>
					</div>

					<button type="submit" class="btn btn--primary btn--large">
						Invia Richiesta
						<span class="material-symbols-outlined" aria-hidden="true">send</span>
					</button>
				</form>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
