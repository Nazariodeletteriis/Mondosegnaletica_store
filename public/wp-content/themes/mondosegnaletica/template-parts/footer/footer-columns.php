<?php
/**
 * Footer 4 colonne: Navigazione / Supporto / Certificazioni / Indirizzo
 */
?>
<div class="footer-grid">

	<!-- COL 1: Navigazione -->
	<div class="footer-col">
		<p class="footer-col__title">Navigazione</p>
		<nav aria-label="<?php esc_attr_e( 'Footer — Navigazione', 'mondosegnaletica' ); ?>">
			<ul class="footer-nav__list">
				<li><a class="footer-nav__link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ?: home_url( '/negozio' ) ); ?>">Catalogo Prodotti</a></li>
				<li><a class="footer-nav__link" href="<?php echo esc_url( home_url( '/soluzioni' ) ); ?>">Soluzioni Viabilità</a></li>
				<li><a class="footer-nav__link" href="<?php echo esc_url( home_url( '/cantieri' ) ); ?>">Cantieristica</a></li>
				<li><a class="footer-nav__link" href="<?php echo esc_url( home_url( '/azienda' ) ); ?>">Chi Siamo</a></li>
				<li><a class="footer-nav__link" href="<?php echo esc_url( home_url( '/contatti' ) ); ?>">Contatti</a></li>
			</ul>
		</nav>
	</div>

	<!-- COL 2: Supporto -->
	<div class="footer-col">
		<p class="footer-col__title">Supporto</p>
		<nav aria-label="<?php esc_attr_e( 'Footer — Supporto', 'mondosegnaletica' ); ?>">
			<ul class="footer-nav__list">
				<li><a class="footer-nav__link" href="<?php echo esc_url( home_url( '/richiedi-preventivo' ) ); ?>">Richiedi Preventivo</a></li>
				<li><a class="footer-nav__link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ?: home_url( '/account' ) ); ?>">Area Clienti</a></li>
				<li><a class="footer-nav__link" href="<?php echo esc_url( home_url( '/spedizioni' ) ); ?>">Spedizioni e Resi</a></li>
				<li><a class="footer-nav__link" href="<?php echo esc_url( home_url( '/faq' ) ); ?>">FAQ</a></li>
				<li><a class="footer-nav__link" href="<?php echo esc_url( home_url( '/download' ) ); ?>">Download Schede Tecniche</a></li>
			</ul>
		</nav>
	</div>

	<!-- COL 3: Certificazioni -->
	<div class="footer-col">
		<p class="footer-col__title">Certificazioni</p>
		<ul class="footer-cert-list">
			<li class="footer-cert-item">
				<span class="material-symbols-outlined" aria-hidden="true">verified</span>
				DM 31/03/1995 — Segnali Stradali
			</li>
			<li class="footer-cert-item">
				<span class="material-symbols-outlined" aria-hidden="true">verified</span>
				D.Lgs. 285/1992 — Codice della Strada
			</li>
			<li class="footer-cert-item">
				<span class="material-symbols-outlined" aria-hidden="true">verified</span>
				EN 12899-1:2007 — Segnaletica verticale
			</li>
			<li class="footer-cert-item">
				<span class="material-symbols-outlined" aria-hidden="true">verified</span>
				ISO 9001:2015 — Sistema Qualità
			</li>
			<li class="footer-cert-item">
				<span class="material-symbols-outlined" aria-hidden="true">verified</span>
				Omologazione ANAS / MIT
			</li>
		</ul>
	</div>

	<!-- COL 4: Indirizzo -->
	<div class="footer-col">
		<p class="footer-col__title">Sede Operativa</p>
		<address class="footer-address">
			Mondo Segnaletica Soc. Coop.<br>
			Via Carlo Angeloni 360, Lucca<br>
			Magazzino: Viale Europa 50<br>
			Lammari (LU) — Toscana<br>
			P.IVA 02629010460<br>
			<br>
			<a href="tel:+390583164632" style="color: inherit;">0583 1646327</a><br>
			<a href="mailto:info@mondosegnaletica.it" style="color: inherit;">info@mondosegnaletica.it</a>
		</address>
	</div>

</div>
