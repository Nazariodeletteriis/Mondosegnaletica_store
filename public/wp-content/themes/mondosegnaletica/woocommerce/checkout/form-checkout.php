<?php
/**
 * WooCommerce Checkout — override.
 * Delega ai form WC nativi per sicurezza payment — struttura visiva custom.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() && 'no' === get_option( 'woocommerce_enable_guest_checkout' ) && ! WC()->cart->needs_payment() ) {
	echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', esc_html__( 'Devi essere collegato per completare il checkout.', 'mondosegnaletica' ) ); // phpcs:ignore
	return;
}

get_header( 'shop' );
?>

<div class="container" style="padding-block:var(--space-16)">

	<div class="section-header" style="margin-bottom:var(--space-8)">
		<span class="label-section">Checkout</span>
		<h1 class="section-title">Completa l'ordine.</h1>
	</div>

	<?php woocommerce_output_all_notices(); ?>

	<?php do_action( 'woocommerce_before_checkout_form', WC()->checkout() ); ?>

	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-12);align-items:start;">

			<div>
				<h2 style="font-family:var(--font-mono);font-size:var(--text-sm);letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-muted);margin-bottom:var(--space-6);">
					Dati di Fatturazione
				</h2>
				<?php do_action( 'woocommerce_checkout_billing' ); ?>

				<h2 style="font-family:var(--font-mono);font-size:var(--text-sm);letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-muted);margin-top:var(--space-8);margin-bottom:var(--space-6);">
					Spedizione
				</h2>
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>

				<?php do_action( 'woocommerce_checkout_order_notes' ); ?>
			</div>

			<div>
				<h2 style="font-family:var(--font-mono);font-size:var(--text-sm);letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-muted);margin-bottom:var(--space-6);">
					Riepilogo Ordine
				</h2>
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>

		</div>

		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_checkout_form', WC()->checkout() ); ?>

</div>

<?php get_footer( 'shop' ); ?>
