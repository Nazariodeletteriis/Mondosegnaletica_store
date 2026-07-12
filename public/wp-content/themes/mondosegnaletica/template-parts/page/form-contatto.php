<?php
/**
 * Form contatti.
 *
 * Attende:
 * @var array  $ms_form     Esito di ms_page_form_process( 'contact' ).
 * @var string $ms_prodotto Nome del prodotto di riferimento (da ?prodotto=<slug>).
 */

defined( 'ABSPATH' ) || exit;

$ms_form     = isset( $ms_form ) && is_array( $ms_form ) ? $ms_form : [ 'status' => 'idle', 'errors' => [], 'values' => [] ];
$ms_prodotto = isset( $ms_prodotto ) ? (string) $ms_prodotto : '';

if ( 'ok' === $ms_form['status'] ) : ?>

	<div class="form-success" role="status">
		<span class="form-success__label"><?php esc_html_e( 'Richiesta ricevuta', 'mondosegnaletica' ); ?></span>
		<h2 class="form-success__title"><?php esc_html_e( 'Grazie. Vi ricontattiamo.', 'mondosegnaletica' ); ?></h2>
		<p class="form-success__text">
			<?php esc_html_e( 'La vostra richiesta è stata inoltrata all\'ufficio commerciale. Rispondiamo entro un giorno lavorativo. Per urgenze operative chiamate lo 0583 1646327.', 'mondosegnaletica' ); ?>
		</p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ?: home_url( '/negozio' ) ); ?>" class="btn btn--outline">
			<?php esc_html_e( 'Torna al catalogo', 'mondosegnaletica' ); ?>
		</a>
	</div>

<?php else : ?>

	<form class="page-form" method="post" action="<?php echo esc_url( home_url( '/contatti/' ) ); ?>" novalidate>
		<?php wp_nonce_field( 'ms_contact_form', 'ms_contact_nonce' ); ?>

		<div class="page-form__header">
			<h2 class="page-form__title"><?php esc_html_e( 'Scriveteci', 'mondosegnaletica' ); ?></h2>
			<p class="page-form__hint"><?php esc_html_e( 'Risposta entro 1 giorno lavorativo · Campi con * obbligatori', 'mondosegnaletica' ); ?></p>
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

		<?php if ( $ms_prodotto ) : ?>
			<p class="page-form__product">
				<span class="material-symbols-outlined" aria-hidden="true">inventory_2</span>
				<?php
				/* translators: %s: nome prodotto */
				printf( esc_html__( 'Richiesta su: %s', 'mondosegnaletica' ), esc_html( $ms_prodotto ) );
				?>
			</p>
			<input type="hidden" name="prodotto" value="<?php echo esc_attr( $ms_prodotto ); ?>">
		<?php endif; ?>

		<div class="form-field">
			<label class="form-field__label" for="ms-ragione-sociale">
				<?php esc_html_e( 'Ragione sociale', 'mondosegnaletica' ); ?> <span class="page-form__required">*</span>
			</label>
			<input class="form-field__input" type="text" id="ms-ragione-sociale" name="ragione_sociale" required autocomplete="organization"
				placeholder="<?php esc_attr_e( 'Impresa, ente pubblico o studio', 'mondosegnaletica' ); ?>"
				value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'ragione_sociale' ) ); ?>">
		</div>

		<div class="page-form__row">
			<div class="form-field">
				<label class="form-field__label" for="ms-referente"><?php esc_html_e( 'Referente', 'mondosegnaletica' ); ?></label>
				<input class="form-field__input" type="text" id="ms-referente" name="referente" autocomplete="name"
					placeholder="<?php esc_attr_e( 'Nome e cognome', 'mondosegnaletica' ); ?>"
					value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'referente' ) ); ?>">
			</div>

			<div class="form-field">
				<label class="form-field__label" for="ms-telefono"><?php esc_html_e( 'Telefono', 'mondosegnaletica' ); ?></label>
				<input class="form-field__input" type="tel" id="ms-telefono" name="telefono" autocomplete="tel"
					placeholder="+39 0583 000000"
					value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'telefono' ) ); ?>">
			</div>
		</div>

		<div class="form-field">
			<label class="form-field__label" for="ms-email">
				<?php esc_html_e( 'Email', 'mondosegnaletica' ); ?> <span class="page-form__required">*</span>
			</label>
			<input class="form-field__input" type="email" id="ms-email" name="email" required autocomplete="email"
				placeholder="ufficio@azienda.it"
				value="<?php echo esc_attr( ms_page_form_value( $ms_form, 'email' ) ); ?>">
		</div>

		<div class="form-field">
			<label class="form-field__label" for="ms-messaggio"><?php esc_html_e( 'Richiesta', 'mondosegnaletica' ); ?></label>
			<textarea class="form-field__textarea" id="ms-messaggio" name="messaggio" rows="6"
				placeholder="<?php esc_attr_e( 'Tipologia di segnali, quantità, tempistiche di consegna, riferimenti di gara…', 'mondosegnaletica' ); ?>"><?php echo esc_textarea( ms_page_form_value( $ms_form, 'messaggio' ) ); ?></textarea>
		</div>

		<label class="page-form__consent">
			<input type="checkbox" name="privacy" value="1" required>
			<span>
				<?php esc_html_e( 'Acconsento al trattamento dei dati per la gestione della richiesta', 'mondosegnaletica' ); ?>
				<?php if ( get_privacy_policy_url() ) : ?>
					(<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'privacy policy', 'mondosegnaletica' ); ?></a>)
				<?php endif; ?>
			</span>
		</label>

		<div class="page-form__honeypot" aria-hidden="true">
			<label for="ms-hp-contact"><?php esc_html_e( 'Non compilare', 'mondosegnaletica' ); ?></label>
			<input type="text" id="ms-hp-contact" name="ms_hp" tabindex="-1" autocomplete="off">
		</div>

		<button type="submit" class="btn btn--primary btn--large">
			<?php esc_html_e( 'Invia richiesta', 'mondosegnaletica' ); ?>
			<span class="material-symbols-outlined" aria-hidden="true">send</span>
		</button>
	</form>

<?php endif; ?>
