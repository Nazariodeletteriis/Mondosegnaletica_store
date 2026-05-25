<?php
/**
 * WooCommerce Cart — override minimalista.
 * Mantiene i form WC nativi, adatta solo la struttura visiva.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_cart' );
?>

<div class="container" style="padding-block:var(--space-16)">

	<div class="section-header" style="margin-bottom:var(--space-8)">
		<span class="label-section">Carrello</span>
		<h1 class="section-title">Il tuo ordine.</h1>
	</div>

	<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
		<?php do_action( 'woocommerce_before_cart_table' ); ?>

		<table class="woocommerce-cart-form__contents" cellspacing="0" style="width:100%;border-collapse:collapse;">
			<thead>
				<tr>
					<th style="font-family:var(--font-mono);font-size:var(--text-xs);letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-muted);padding:var(--space-3) 0;border-bottom:var(--border-hairline);text-align:left;">Prodotto</th>
					<th style="font-family:var(--font-mono);font-size:var(--text-xs);letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-muted);padding:var(--space-3) 0;border-bottom:var(--border-hairline);">Prezzo</th>
					<th style="font-family:var(--font-mono);font-size:var(--text-xs);letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-muted);padding:var(--space-3) 0;border-bottom:var(--border-hairline);">Quantità</th>
					<th style="font-family:var(--font-mono);font-size:var(--text-xs);letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-muted);padding:var(--space-3) 0;border-bottom:var(--border-hairline);">Totale</th>
				</tr>
			</thead>
			<tbody>
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<?php
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

					if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] === 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) continue;

					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<tr style="border-bottom:var(--border-hairline);">
					<td style="padding:var(--space-4) 0;">
						<div style="display:flex;align-items:center;gap:var(--space-4);">
							<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( [ 64, 64 ] ), $cart_item, $cart_item_key );
							if ( $product_permalink ) {
								echo '<a href="' . esc_url( $product_permalink ) . '">' . $thumbnail . '</a>'; // phpcs:ignore
							} else {
								echo $thumbnail; // phpcs:ignore
							}
							?>
							<div>
								<p style="font-family:var(--font-display);font-size:var(--text-base);text-transform:uppercase;">
									<?php
									if ( $product_permalink ) {
										echo '<a href="' . esc_url( $product_permalink ) . '" style="color:var(--color-text);text-decoration:none;">' . wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '</a>';
									} else {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) );
									}
									?>
								</p>
								<?php if ( $_product->get_sku() ) : ?>
									<span style="font-family:var(--font-mono);font-size:var(--text-xs);color:var(--color-accent);"><?php echo esc_html( $_product->get_sku() ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</td>
					<td style="padding:var(--space-4);text-align:center;font-family:var(--font-display);font-size:var(--text-xl);">
						<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore ?>
					</td>
					<td style="padding:var(--space-4);text-align:center;">
						<?php
						if ( $_product->is_sold_individually() ) {
							echo '<span>1</span>';
							echo '<input type="hidden" name="cart[' . esc_attr( $cart_item_key ) . '][qty]" value="1">';
						} else {
							woocommerce_quantity_input( [
								'input_name'   => "cart[{$cart_item_key}][qty]",
								'input_value'  => $cart_item['quantity'],
								'max_value'    => $_product->get_max_purchase_quantity(),
								'min_value'    => '0',
								'product_name' => $_product->get_name(),
							], $_product );
						}
						?>
					</td>
					<td style="padding:var(--space-4);text-align:center;font-family:var(--font-display);font-size:var(--text-xl);">
						<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore ?>
					</td>
				</tr>
				<?php endforeach; ?>

				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</tbody>
		</table>

		<div style="display:flex;justify-content:flex-end;margin-top:var(--space-8);">
			<button type="submit" class="btn btn--outline" name="update_cart" value="<?php esc_attr_e( 'Aggiorna carrello', 'mondosegnaletica' ); ?>">
				<?php esc_html_e( 'Aggiorna carrello', 'mondosegnaletica' ); ?>
			</button>
		</div>

		<?php do_action( 'woocommerce_cart_contents' ); ?>
		<?php do_action( 'woocommerce_after_cart_table' ); ?>
		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
	</form>

	<div class="cart-collaterals" style="margin-top:var(--space-12);display:flex;justify-content:flex-end;">
		<?php do_action( 'woocommerce_cart_collaterals' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart' ); ?>

</div>

<?php get_footer( 'shop' ); ?>
