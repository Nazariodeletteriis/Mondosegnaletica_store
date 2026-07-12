<?php
/**
 * My Account — navigazione.
 *
 * Override di woocommerce/templates/myaccount/navigation.php
 *
 * @package mondosegnaletica
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );

// Icone Material Symbols per endpoint.
$ms_icons = [
	'dashboard'       => 'dashboard',
	'orders'          => 'receipt_long',
	'downloads'       => 'download',
	'edit-address'    => 'local_shipping',
	'payment-methods' => 'credit_card',
	'edit-account'    => 'badge',
	'customer-logout' => 'logout',
];
?>

<nav class="woocommerce-MyAccount-navigation account-nav" aria-label="<?php esc_attr_e( 'Navigazione area clienti', 'mondosegnaletica' ); ?>">

	<span class="account-nav__label"><?php esc_html_e( 'Il tuo account', 'mondosegnaletica' ); ?></span>

	<ul class="account-nav__list">
		<?php foreach ( wc_get_account_menu_items() as $ms_endpoint => $ms_label ) : ?>
			<li class="account-nav__item <?php echo esc_attr( wc_get_account_menu_item_classes( $ms_endpoint ) ); ?><?php echo 'customer-logout' === $ms_endpoint ? ' account-nav__item--logout' : ''; ?><?php echo wc_is_current_account_menu_item( $ms_endpoint ) ? ' is-active' : ''; ?>">
				<a
					class="account-nav__link"
					href="<?php echo esc_url( wc_get_account_endpoint_url( $ms_endpoint ) ); ?>"
					<?php echo wc_is_current_account_menu_item( $ms_endpoint ) ? 'aria-current="page"' : ''; ?>
				>
					<span class="material-symbols-outlined" aria-hidden="true"><?php echo esc_html( $ms_icons[ $ms_endpoint ] ?? 'chevron_right' ); ?></span>
					<span><?php echo esc_html( $ms_label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
