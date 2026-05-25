<?php
/**
 * Sezione 05 / SOLUZIONI — 2 colonne: headline Anton + 3 blocchi servizi.
 */
?>

<section class="section-solutions" id="soluzioni" aria-labelledby="solutions-title">
	<div class="container">

		<span class="label-section">05 / SOLUZIONI</span>

		<div class="solutions-grid">

			<!-- Sinistra: headline Anton -->
			<div class="solutions-headline">
				<h2 class="solutions-headline__text" id="solutions-title">
					Engineering<br>della<br>
					<span class="solutions-headline__accent">Viabilità.</span>
				</h2>
				<p class="solutions-headline__sub">
					Forniamo segnaletica omologata Codice della Strada per cantieri temporanei,
					aree urbane e strade rurali. Dal singolo cartello al kit completo di cantiere.
				</p>
				<a href="<?php echo esc_url( home_url( '/negozio' ) ); ?>" class="btn btn--primary">
					Esplora il catalogo
					<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
				</a>
			</div>

			<!-- Destra: 3 blocchi servizio -->
			<div class="solutions-blocks">

				<div class="solution-block">
					<div class="solution-block__header">
						<span class="solution-block__num">01</span>
						<h3 class="solution-block__title">Cantieri Temporanei</h3>
					</div>
					<p class="solution-block__desc">
						Segnaletica per deviazioni, restringimenti di carreggiata e presegnalazione.
						Coni, transenne, delineatori e pannelli direzionali. Consegna 24/48h.
					</p>
					<a href="<?php echo esc_url( home_url( '/negozio/cantieristica' ) ); ?>" class="solution-block__link">
						Vai alla categoria
						<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
					</a>
				</div>

				<div class="solution-block">
					<div class="solution-block__header">
						<span class="solution-block__num">02</span>
						<h3 class="solution-block__title">Viabilità Urbana</h3>
					</div>
					<p class="solution-block__desc">
						Cartelli stradali per centri abitati, zone ZTL, piste ciclabili e aree pedonali.
						Tutti omologati DM 31/03/1995 e Codice della Strada.
					</p>
					<a href="<?php echo esc_url( home_url( '/negozio/segnaletica-verticale' ) ); ?>" class="solution-block__link">
						Vai alla categoria
						<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
					</a>
				</div>

				<div class="solution-block">
					<div class="solution-block__header">
						<span class="solution-block__num">03</span>
						<h3 class="solution-block__title">Strade Rurali e Provinciali</h3>
					</div>
					<p class="solution-block__desc">
						Segnaletica per strade extra-urbane: dissuasori, delineatori, guard rail e
						segnali di pericolo per tratti a visibilità ridotta.
					</p>
					<a href="<?php echo esc_url( home_url( '/negozio/delineatori-paletti' ) ); ?>" class="solution-block__link">
						Vai alla categoria
						<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
					</a>
				</div>

			</div>

		</div>

	</div>
</section>
