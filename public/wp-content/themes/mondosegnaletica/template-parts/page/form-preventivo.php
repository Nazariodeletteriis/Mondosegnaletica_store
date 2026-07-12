<?php
/**
 * Form richiesta preventivo B2B.
 *
 * Attende:
 * @var array $ms_form Esito di ms_page_form_process( 'quote' ).
 */

defined( 'ABSPATH' ) || exit;

$ms_form = isset( $ms_form ) && is_array( $ms_form ) ? $ms_form : [ 'status' => 'idle', 'errors' => [], 'values' => [] ];

// Riepilogo carrello — il carrello linka a questa pagina per le grandi quantità.
$ms_cart_items = [];
if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
	foreach ( WC()->cart->get_cart() as $ms_item ) {
		if ( empty( $ms_item['data'] ) || ! $ms_item['data'] instanceof \WC_Product ) {
			continue;
		}
		$ms_cart_items[] = [
			'name' => $ms_item['data']->get_name(),
			'qty'  => (int) $ms_item['quantity'],
		];
	}
}

// Categorie merceologiche — dalle categorie WooCommerce reali, con fallback alle 6 MS.
$ms_categories = [];
if ( function_exists( 'get_terms' ) ) {
	$ms_terms = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => 0,
		'number'     => 12,
	] );
	if ( ! is_wp_error( $ms_terms ) ) {
		foreach ( $ms_terms as $ms_term ) {
			$ms_categories[] = $ms_term->name;
		}
	}
}
if ( empty( $ms_categories ) && function_exists( 'ms_get_default_categories' ) ) {
	$ms_categories = wp_list_pluck( ms_get_default_categories(), 'name' );
}

if ( 'ok' === $ms_form['status'] ) : ?>

	<div class="form-success" role="status">
		<span class="form-success__label"><?php esc_html_e( 'Preventivo in lavorazione', 'mondosegnaletica' ); ?></span>
		<h2 class="form-success__title"><?php esc_html_e( 'Richiesta inoltrata.', 'mondosegnaletica' ); ?></h2>
		<p class="form-success__text">
			<?php esc_html_e( 'L\'ufficio commerciale elabora il preventivo e risponde entro un giorno lavorativo, con prezzi a fasce di quantità e tempi di consegna dal magazzino di Lucca.', 'mondosegnaletica' ); ?>
		</p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ?: home_url( '/negozio' ) ); ?>" class="btn btn--outline">
			<?php esc_html_e( 'Torna al catalogo', 'mondosegnaletica' ); ?>
		</a>
	</div>

<?php else : ?>

	<form class="page-form" method="post" action="<?php echo esc_url( home_url( '/richiedi-preventivo/' ) ); ?>" novalidate>
		<?php wp_nonce_field( 'ms_quote_form', 'ms_quote_nonce' ); ?>

		<div class="page-form__header">
			<h2 class="page-form__title"><?php esc_html_e( 'Richiedi preventivo', 'mondosegnaletica' ); ?></h2>
			<p class="page-form__hint"><?php esc_html_e( 'Ordini da 10 pezzi in su · Risposta entro 1 giorno lavorativo', 'mondosegnaletica' ); ?></p>
		</div>

		<?php if ( 'error' === $ms_form['status'] && ! empty( $ms_form['errors'] ) ) : ?>
			<div class="form-notice form-notice--error" role="alert">
				<span class="material-symbols-outlined" aria-hidden="true">error</span>
				<div>
					<strong><?php esc_html_e( 'Richiesta non inviata.', 'mondosegnaletica' ); ?></strong>
					<div class="form-notice__list">
						<?php foreach ( $ms_form['errors'] as $ms_error ) : ?>
							<span><?php echo esc_html( $ms_error ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $ms_cart_items ) ) : ?>
			<div class="quote-cart">
				<span class="quote-cart__label"><?php esc_html_e( 'Allegato: carrello in corso', 'mondosegnaletica' ); ?></span>
				<div class="quote-cart__list">
					<?php foreach ( $ms_cart_items as $ms_cart_item ) : ?>
						<div class="quote-cart__item">
							<span><?php echo esc_html( $ms_cart_item['name'] ); ?></span>
							<span class="quote-cart__qty">× <?php echo esc_html( (string) $ms_cart_item['qty'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="page-form__row">
			<div class="form-field">
				<label class="form-field__label" for="ms-q-ragione-sociale">
					<?php esc_html_e( 'Ragione sociale', 'mondosegnaletica' ); ?> <span class="page-form__required">*</span>
				</label>
				<input class="form-field__input" type="text" id="ms-q-ragione-sociale" name="ragione_sociale" required autocomplete="organization"
					placeholder="<?php esc_attr_e( 'Impresa, ente o studio', 'mondosegnaletica' ); ?>"
					value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'ragione_sociale' ) ); ?>">
			</div>

			<div class="form-field">
				<label class="form-field__label" for="ms-q-tipo"><?php esc_html_e( 'Tipologia cliente', 'mondosegnaletica' ); ?></label>
				<select class="form-field__select" id="ms-q-tipo" name="tipo_cliente">
					<?php
					$ms_tipi     = [ '', 'Ente pubblico', 'Impresa di costruzione', 'Impresa di manutenzione', 'Professionista / Studio tecnico', 'Rivenditore' ];
					$ms_tipo_sel = ms_page_form_value( $ms_form, 'tipo_cliente' );
					foreach ( $ms_tipi as $ms_tipo ) :
						?>
						<option value="<?php echo esc_attr( $ms_tipo ); ?>" <?php selected( $ms_tipo_sel, $ms_tipo ); ?>>
							<?php echo $ms_tipo ? esc_html( $ms_tipo ) : esc_html__( 'Selezionate…', 'mondosegnaletica' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="page-form__row">
			<div class="form-field">
				<label class="form-field__label" for="ms-q-piva"><?php esc_html_e( 'P.IVA / Codice Fiscale', 'mondosegnaletica' ); ?></label>
				<input class="form-field__input" type="text" id="ms-q-piva" name="piva" placeholder="IT00000000000"
					value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'piva' ) ); ?>">
			</div>

			<div class="form-field">
				<label class="form-field__label" for="ms-q-referente"><?php esc_html_e( 'Referente', 'mondosegnaletica' ); ?></label>
				<input class="form-field__input" type="text" id="ms-q-referente" name="referente" autocomplete="name"
					placeholder="<?php esc_attr_e( 'Nome e cognome', 'mondosegnaletica' ); ?>"
					value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'referente' ) ); ?>">
			</div>
		</div>

		<div class="page-form__row">
			<div class="form-field">
				<label class="form-field__label" for="ms-q-email">
					<?php esc_html_e( 'Email', 'mondosegnaletica' ); ?> <span class="page-form__required">*</span>
				</label>
				<input class="form-field__input" type="email" id="ms-q-email" name="email" required autocomplete="email"
					placeholder="ufficio@azienda.it"
					value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'email' ) ); ?>">
			</div>

			<div class="form-field">
				<label class="form-field__label" for="ms-q-telefono">
					<?php esc_html_e( 'Telefono', 'mondosegnaletica' ); ?> <span class="page-form__required">*</span>
				</label>
				<input class="form-field__input" type="tel" id="ms-q-telefono" name="telefono" required autocomplete="tel"
					placeholder="+39 0583 000000"
					value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'telefono' ) ); ?>">
			</div>
		</div>

		<div class="page-form__row">
			<div class="form-field">
				<label class="form-field__label" for="ms-q-categoria"><?php esc_html_e( 'Categoria merceologica', 'mondosegnaletica' ); ?></label>
				<select class="form-field__select" id="ms-q-categoria" name="categoria">
					<option value=""><?php esc_html_e( 'Selezionate…', 'mondosegnaletica' ); ?></option>
					<?php
					$ms_cat_sel = ms_page_form_value( $ms_form, 'categoria' );
					foreach ( $ms_categories as $ms_cat ) :
						?>
						<option value="<?php echo esc_attr( $ms_cat ); ?>" <?php selected( $ms_cat_sel, $ms_cat ); ?>><?php echo esc_html( $ms_cat ); ?></option>
					<?php endforeach; ?>
					<option value="Fornitura mista" <?php selected( $ms_cat_sel, 'Fornitura mista' ); ?>><?php esc_html_e( 'Fornitura mista', 'mondosegnaletica' ); ?></option>
				</select>
			</div>

			<div class="form-field">
				<label class="form-field__label" for="ms-q-quantita"><?php esc_html_e( 'Quantità stimata', 'mondosegnaletica' ); ?></label>
				<input class="form-field__input" type="text" id="ms-q-quantita" name="quantita"
					placeholder="<?php esc_attr_e( 'Es. 40 cartelli + 60 coni', 'mondosegnaletica' ); ?>"
					value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'quantita' ) ); ?>">
			</div>
		</div>

		<div class="form-field">
			<label class="form-field__label" for="ms-q-consegna"><?php esc_html_e( 'Consegna richiesta entro', 'mondosegnaletica' ); ?></label>
			<input class="form-field__input" type="date" id="ms-q-consegna" name="consegna"
				value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'consegna' ) ); ?>">
		</div>

		<div class="form-field">
			<label class="form-field__label" for="ms-q-messaggio"><?php esc_html_e( 'Dettaglio richiesta', 'mondosegnaletica' ); ?></label>
			<textarea class="form-field__textarea" id="ms-q-messaggio" name="messaggio" rows="6"
				placeholder="<?php esc_attr_e( 'Elenco articoli, misure, classe di rifrangenza, riferimenti di gara o CIG, luogo di consegna…', 'mondosegnaletica' ); ?>"><?php echo esc_textarea( ms_page_form_value( $ms_form, 'messaggio' ) ); ?></textarea>
		</div>

		<label class="page-form__consent">
			<input type="checkbox" name="privacy" value="1" required>
			<span>
				<?php esc_html_e( 'Acconsento al trattamento dei dati per la formulazione del preventivo', 'mondosegnaletica' ); ?>
				<?php if ( get_privacy_policy_url() ) : ?>
					(<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'privacy policy', 'mondosegnaletica' ); ?></a>)
				<?php endif; ?>
			</span>
		</label>

		<div class="page-form__honeypot" aria-hidden="true">
			<label for="ms-hp-quote"><?php esc_html_e( 'Non compilare', 'mondosegnaletica' ); ?></label>
			<input type="text" id="ms-hp-quote" name="ms_hp" tabindex="-1" autocomplete="off">
		</div>

		<button type="submit" class="btn btn--primary btn--large">
			<?php esc_html_e( 'Invia richiesta di preventivo', 'mondosegnaletica' ); ?>
			<span class="material-symbols-outlined" aria-hidden="true">send</span>
		</button>
	</form>

<?php endif; ?>
