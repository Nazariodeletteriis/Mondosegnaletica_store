<?php
/**
 * My Account — recupero password.
 *
 * Override di woocommerce/templates/myaccount/form-lost-password.php
 *
 * @package mondosegnaletica
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>

<div class="account-auth__col" style="max-width:560px">
	<span class="account-auth__label"><?php esc_html_e( 'Recupero accesso', 'mondosegnaletica' ); ?></span>
	<h2 class="account-auth__title"><?php esc_html_e( 'Reimposta la password', 'mondosegnaletica' ); ?></h2>
	<p class="account-auth__intro">
		<?php esc_html_e( 'Inserite l\'email o il nome utente dell\'account: riceverete un link per impostare una nuova password.', 'mondosegnaletica' ); ?>
	</p>

	<form method="post" class="woocommerce-ResetPassword lost_reset_password account-auth__form">

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="user_login">
				<?php esc_html_e( 'Email o nome utente', 'mondosegnaletica' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Obbligatorio', 'mondosegnaletica' ); ?></span>
			</label>
			<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" required aria-required="true" />
		</p>

		<?php do_action( 'woocommerce_lostpassword_form' ); ?>

		<input type="hidden" name="wc_reset_password" value="true" />

		<button type="submit" class="woocommerce-Button button" value="<?php esc_attr_e( 'Invia il link', 'mondosegnaletica' ); ?>">
			<?php esc_html_e( 'Invia il link', 'mondosegnaletica' ); ?>
		</button>

		<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

	</form>
</div>

<?php do_action( 'woocommerce_after_lost_password_form' ); ?>
