<?php
/**
 * My Account — login e registrazione.
 *
 * Override di woocommerce/templates/myaccount/form-login.php
 * Mantiene nomi campo, nonce e hook nativi WooCommerce.
 *
 * @package mondosegnaletica
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );

$ms_registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$ms_generate_username    = 'yes' === get_option( 'woocommerce_registration_generate_username' );
$ms_generate_password    = 'yes' === get_option( 'woocommerce_registration_generate_password' );
?>

<div class="account-auth" id="customer_login">

	<!-- ACCESSO -->
	<div class="account-auth__col account-auth__col--login">
		<span class="account-auth__label"><?php esc_html_e( '01 / Accesso', 'mondosegnaletica' ); ?></span>
		<h2 class="account-auth__title"><?php esc_html_e( 'Entra nel tuo account', 'mondosegnaletica' ); ?></h2>
		<p class="account-auth__intro">
			<?php esc_html_e( 'Storico ordini, indirizzi di consegna e dati di fatturazione elettronica sempre a disposizione.', 'mondosegnaletica' ); ?>
		</p>

		<form class="woocommerce-form woocommerce-form-login login account-auth__form" method="post" novalidate>

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username">
					<?php esc_html_e( 'Email o nome utente', 'mondosegnaletica' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Obbligatorio', 'mondosegnaletica' ); ?></span>
				</label>
				<input
					type="text"
					class="woocommerce-Input woocommerce-Input--text input-text"
					name="username"
					id="username"
					autocomplete="username"
					value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
					required
					aria-required="true"
				/>
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password">
					<?php esc_html_e( 'Password', 'mondosegnaletica' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Obbligatorio', 'mondosegnaletica' ); ?></span>
				</label>
				<input
					class="woocommerce-Input woocommerce-Input--text input-text"
					type="password"
					name="password"
					id="password"
					autocomplete="current-password"
					required
					aria-required="true"
				/>
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="account-auth__row">
				<label class="account-auth__remember woocommerce-form-login__rememberme" for="rememberme">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
					<span><?php esc_html_e( 'Resta collegato', 'mondosegnaletica' ); ?></span>
				</label>

				<a class="account-auth__lost" href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
					<?php esc_html_e( 'Password dimenticata?', 'mondosegnaletica' ); ?>
				</a>
			</div>

			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

			<button
				type="submit"
				class="woocommerce-button button woocommerce-form-login__submit"
				name="login"
				value="<?php esc_attr_e( 'Accedi', 'mondosegnaletica' ); ?>"
			>
				<?php esc_html_e( 'Accedi', 'mondosegnaletica' ); ?>
			</button>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>
	</div>

	<!-- REGISTRAZIONE -->
	<div class="account-auth__col account-auth__col--register">
		<span class="account-auth__label"><?php esc_html_e( '02 / Registrazione', 'mondosegnaletica' ); ?></span>

		<?php if ( $ms_registration_enabled ) : ?>

			<h2 class="account-auth__title"><?php esc_html_e( 'Apri un account B2B', 'mondosegnaletica' ); ?></h2>
			<p class="account-auth__intro">
				<?php esc_html_e( 'Riservato a imprese, enti pubblici e professionisti. I dati di fatturazione (ragione sociale, P.IVA, codice SDI) si inseriscono al primo ordine.', 'mondosegnaletica' ); ?>
			</p>

			<form method="post" class="woocommerce-form woocommerce-form-register register account-auth__form" <?php do_action( 'woocommerce_register_form_tag' ); ?>>

				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( ! $ms_generate_username ) : ?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_username">
							<?php esc_html_e( 'Nome utente', 'mondosegnaletica' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
						</label>
						<input
							type="text"
							class="woocommerce-Input woocommerce-Input--text input-text"
							name="username"
							id="reg_username"
							autocomplete="username"
							value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
							required
							aria-required="true"
						/>
					</p>
				<?php endif; ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_email">
						<?php esc_html_e( 'Email aziendale', 'mondosegnaletica' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
					</label>
					<input
						type="email"
						class="woocommerce-Input woocommerce-Input--text input-text"
						name="email"
						id="reg_email"
						autocomplete="email"
						placeholder="ufficio@azienda.it"
						value="<?php echo ( ! empty( $_POST['email'] ) && is_string( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
						required
						aria-required="true"
					/>
				</p>

				<?php if ( ! $ms_generate_password ) : ?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_password">
							<?php esc_html_e( 'Password', 'mondosegnaletica' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
						</label>
						<input
							type="password"
							class="woocommerce-Input woocommerce-Input--text input-text"
							name="password"
							id="reg_password"
							autocomplete="new-password"
							required
							aria-required="true"
						/>
					</p>
				<?php else : ?>
					<p class="account-auth__intro">
						<?php esc_html_e( 'Riceverete via email il link per impostare la password.', 'mondosegnaletica' ); ?>
					</p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

				<button
					type="submit"
					class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit"
					name="register"
					value="<?php esc_attr_e( 'Registrati', 'mondosegnaletica' ); ?>"
				>
					<?php esc_html_e( 'Crea account', 'mondosegnaletica' ); ?>
				</button>

				<?php do_action( 'woocommerce_register_form_end' ); ?>

			</form>

		<?php else : ?>

			<h2 class="account-auth__title"><?php esc_html_e( 'Account su richiesta', 'mondosegnaletica' ); ?></h2>
			<p class="account-auth__intro">
				<?php esc_html_e( 'Gli account sono attivati dall\'ufficio commerciale previa verifica dei dati aziendali. Inviateci una richiesta: rispondiamo entro un giorno lavorativo.', 'mondosegnaletica' ); ?>
			</p>
			<a href="<?php echo esc_url( home_url( '/contatti/' ) ); ?>" class="button">
				<?php esc_html_e( 'Richiedi l\'attivazione', 'mondosegnaletica' ); ?>
			</a>

		<?php endif; ?>

		<p class="account-auth__note">
			<?php esc_html_e( 'Prezzi IVA esclusa · Sconti a fasce di quantità · Spedizione 24/48h da Lucca', 'mondosegnaletica' ); ?>
		</p>
	</div>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
