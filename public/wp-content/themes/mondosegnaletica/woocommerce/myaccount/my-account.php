<?php
/**
 * My Account — shell area clienti.
 *
 * Override di woocommerce/templates/myaccount/my-account.php
 *
 * @package mondosegnaletica
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="account-grid">

	<?php
	/**
	 * My Account navigation.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_navigation' );
	?>

	<div class="woocommerce-MyAccount-content account-panel">
		<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
		?>
	</div>

</div>
