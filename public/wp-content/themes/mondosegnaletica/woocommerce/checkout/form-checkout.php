<?php
/**
 * WooCommerce Checkout — override "Sistema Strada".
 *
 * FRAGMENT: nessun get_header()/get_footer() — la pagina /cassa/ è resa dallo
 * shortcode [woocommerce_checkout] dentro page.php (che stampa già header,
 * <main>, .container, <h1> e footer). Reintrodurli chiude il documento a metà.
 *
 * Delega interamente i campi e il pagamento ai template WC nativi: qui si tocca
 * solo la struttura visiva.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package mondosegnaletica
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

/** @var WC_Checkout $checkout */
$checkout = isset( $checkout ) && $checkout instanceof WC_Checkout ? $checkout : WC()->checkout();

/**
 * @hooked woocommerce_output_all_notices - 10
 * @hooked woocommerce_checkout_login_form - 10
 * @hooked woocommerce_checkout_coupon_form - 10
 */
do_action( 'woocommerce_before_checkout_form', $checkout );

// Gate nativo: registrazione disattivata + account obbligatorio + utente ospite → stop.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo '<p class="checkout-login-required">' . esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'Devi essere collegato per completare l\'ordine.', 'mondosegnaletica' ) ) ) . '</p>';
	return;
}
?>

<div class="checkout-wrap">

	<div class="checkout-intro">
		<span class="label-section"><?php esc_html_e( '02 / Cassa', 'mondosegnaletica' ); ?></span>
		<p class="checkout-intro__lede">
			<?php esc_html_e( 'Dati di fatturazione e spedizione. Tutti gli importi sono IVA esclusa: l\'imposta è calcolata nel riepilogo.', 'mondosegnaletica' ); ?>
		</p>
	</div>

	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php esc_attr_e( 'Cassa', 'mondosegnaletica' ); ?>">

		<div class="checkout-layout">

			<div class="checkout-fields">

				<?php if ( $checkout->get_checkout_fields() ) : ?>

					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

					<div class="col2-set" id="customer_details">

						<div class="col-1 checkout-block">
							<h2 class="checkout-block__title"><?php esc_html_e( 'Dati di fatturazione', 'mondosegnaletica' ); ?></h2>
							<?php
								// form-billing.php stampa un <h3> "Dettagli di fatturazione": nascosto via CSS
								// per non duplicare il titolo qui sopra.
								do_action( 'woocommerce_checkout_billing' );
							?>
						</div>

						<div class="col-2 checkout-block">
							<?php
								// Nessun titolo custom: form-shipping.php stampa già i propri <h3>
								// ("Spedisci a un indirizzo diverso?" / "Informazioni aggiuntive"),
								// che variano in base a needs_shipping_address(). Li stiliamo via CSS.
								do_action( 'woocommerce_checkout_shipping' );
							?>
						</div>

					</div>

					<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

				<?php endif; ?>

			</div><!-- /.checkout-fields -->

			<aside class="checkout-review" aria-labelledby="order_review_heading">

				<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

				<h2 id="order_review_heading" class="checkout-block__title"><?php esc_html_e( 'Riepilogo ordine', 'mondosegnaletica' ); ?></h2>

				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

				<div id="order_review" class="woocommerce-checkout-review-order">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>

				<p class="checkout-review__vat">
					<?php esc_html_e( 'Prezzi unitari IVA esclusa. L\'IVA è esposta nel totale.', 'mondosegnaletica' ); ?>
				</p>

				<p class="checkout-review__ship">
					<?php esc_html_e( 'Spedizione in 24/48h da Lucca · Prodotti omologati CdS', 'mondosegnaletica' ); ?>
				</p>

			</aside>

		</div><!-- /.checkout-layout -->

		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

	</form>

</div><!-- /.checkout-wrap -->

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
