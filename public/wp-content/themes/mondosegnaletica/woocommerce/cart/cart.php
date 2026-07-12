<?php
/**
 * WooCommerce Cart — override "Sistema Strada".
 *
 * IMPORTANTE: questo template è un FRAGMENT.
 * La pagina /carrello/ usa lo shortcode [woocommerce_cart] dentro page.php,
 * che stampa già header, <main>, .container, <h1> e footer.
 * Chiamare qui get_header()/get_footer() chiude il documento a metà pagina
 * (WP usa require_once: il footer finisce dentro l'<article>). NON reintrodurli.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package mondosegnaletica
 * @version 7.9.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<div class="cart-wrap">

	<div class="cart-intro">
		<span class="label-section"><?php esc_html_e( '01 / Carrello', 'mondosegnaletica' ); ?></span>
		<p class="cart-intro__lede">
			<?php esc_html_e( 'Verifica gli articoli e le quantità prima di procedere. Tutti gli importi sono IVA esclusa.', 'mondosegnaletica' ); ?>
		</p>
	</div>

	<div class="cart-layout">

		<div class="cart-main">

			<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
				<?php do_action( 'woocommerce_before_cart_table' ); ?>

				<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents cart-table" cellspacing="0">
					<thead>
						<tr>
							<th scope="col" class="cart-table__th cart-table__th--thumb">
								<span class="sr-only"><?php esc_html_e( 'Immagine', 'mondosegnaletica' ); ?></span>
							</th>
							<th scope="col" class="cart-table__th"><?php esc_html_e( 'Prodotto', 'mondosegnaletica' ); ?></th>
							<th scope="col" class="cart-table__th cart-table__th--num"><?php esc_html_e( 'Prezzo', 'mondosegnaletica' ); ?></th>
							<th scope="col" class="cart-table__th cart-table__th--num"><?php esc_html_e( 'Quantità', 'mondosegnaletica' ); ?></th>
							<th scope="col" class="cart-table__th cart-table__th--num"><?php esc_html_e( 'Totale', 'mondosegnaletica' ); ?></th>
							<th scope="col" class="cart-table__th cart-table__th--remove">
								<span class="sr-only"><?php esc_html_e( 'Rimuovi', 'mondosegnaletica' ); ?></span>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php do_action( 'woocommerce_before_cart_contents' ); ?>

						<?php
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
							$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

							if ( ! $_product || ! $_product->exists() || 0 === $cart_item['quantity'] || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
								continue;
							}

							$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
							$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
							$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( [ 72, 72 ] ), $cart_item, $cart_item_key );
							?>
							<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

								<td class="product-thumbnail">
									<?php
									if ( $product_permalink ) {
										printf( '<a href="%s" tabindex="-1" aria-hidden="true">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									?>
								</td>

								<td class="product-name" data-title="<?php esc_attr_e( 'Prodotto', 'mondosegnaletica' ); ?>">
									<?php
									if ( $product_permalink ) {
										printf( '<a class="product-name__link" href="%s">%s</a>', esc_url( $product_permalink ), wp_kses_post( $product_name ) );
									} else {
										echo wp_kses_post( $product_name );
									}

									// Meta dati (varianti, ecc.).
									echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

									if ( $_product->get_sku() ) {
										printf( '<span class="product-name__sku">%s</span>', esc_html( $_product->get_sku() ) );
									}

									if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
										printf( '<span class="product-name__backorder">%s</span>', esc_html__( 'Disponibile su ordinazione', 'mondosegnaletica' ) );
									}
									?>
								</td>

								<td class="product-price" data-title="<?php esc_attr_e( 'Prezzo', 'mondosegnaletica' ); ?>">
									<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>

								<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantità', 'mondosegnaletica' ); ?>">
									<?php
									if ( $_product->is_sold_individually() ) {
										$min_quantity = 1;
										$max_quantity = 1;
									} else {
										$min_quantity = 0;
										$max_quantity = $_product->get_max_purchase_quantity();
									}

									$product_quantity = woocommerce_quantity_input(
										[
											'input_name'   => "cart[{$cart_item_key}][qty]",
											'input_value'  => $cart_item['quantity'],
											'max_value'    => $max_quantity,
											'min_value'    => $min_quantity,
											'product_name' => $product_name,
										],
										$_product,
										false
									);

									echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</td>

								<td class="product-subtotal" data-title="<?php esc_attr_e( 'Totale', 'mondosegnaletica' ); ?>">
									<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>

								<td class="product-remove">
									<?php
									echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											/* translators: %s: nome prodotto */
											esc_attr( sprintf( __( 'Rimuovi %s dal carrello', 'mondosegnaletica' ), wp_strip_all_tags( $product_name ) ) ),
											esc_attr( $product_id ),
											esc_attr( $_product->get_sku() )
										),
										$cart_item_key
									);
									?>
								</td>

							</tr>
							<?php
						}
						?>

						<?php do_action( 'woocommerce_cart_contents' ); ?>

						<tr>
							<td colspan="6" class="actions">

								<?php if ( wc_coupons_enabled() ) { ?>
									<div class="coupon">
										<label for="coupon_code" class="sr-only"><?php esc_html_e( 'Codice coupon', 'mondosegnaletica' ); ?></label>
										<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Codice coupon', 'mondosegnaletica' ); ?>" />
										<button type="submit" class="button btn btn--outline" name="apply_coupon" value="<?php esc_attr_e( 'Applica coupon', 'mondosegnaletica' ); ?>">
											<?php esc_html_e( 'Applica', 'mondosegnaletica' ); ?>
										</button>
										<?php do_action( 'woocommerce_cart_coupon' ); ?>
									</div>
								<?php } ?>

								<button type="submit" class="button btn btn--outline cart-update-btn" name="update_cart" value="<?php esc_attr_e( 'Aggiorna carrello', 'mondosegnaletica' ); ?>">
									<?php esc_html_e( 'Aggiorna carrello', 'mondosegnaletica' ); ?>
								</button>

								<?php do_action( 'woocommerce_cart_actions' ); ?>
								<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
							</td>
						</tr>

						<?php do_action( 'woocommerce_after_cart_contents' ); ?>
					</tbody>
				</table>

				<?php do_action( 'woocommerce_after_cart_table' ); ?>
			</form>

		</div><!-- /.cart-main -->

		<aside class="cart-aside" aria-label="<?php esc_attr_e( 'Riepilogo ordine', 'mondosegnaletica' ); ?>">

			<div class="cart-collaterals">
				<?php
					/**
					 * @hooked woocommerce_cross_sell_display
					 * @hooked woocommerce_cart_totals - 10
					 */
					do_action( 'woocommerce_cart_collaterals' );
				?>
			</div>

			<!-- Fuori da .cart_totals: WC sostituisce quel nodo via AJAX -->
			<div class="cart-aside__b2b">
				<p class="cart-aside__vat">
					<?php esc_html_e( 'Importi IVA esclusa. L\'IVA viene calcolata in fase di checkout.', 'mondosegnaletica' ); ?>
				</p>

				<a class="cart-quote-btn" href="<?php echo esc_url( home_url( '/richiedi-preventivo' ) ); ?>">
					<span class="material-symbols-outlined" aria-hidden="true">request_quote</span>
					<?php esc_html_e( 'Richiedi preventivo per quantità', 'mondosegnaletica' ); ?>
				</a>

				<p class="cart-aside__ship">
					<?php esc_html_e( 'Spedizione in 24/48h da Lucca · Prodotti omologati CdS', 'mondosegnaletica' ); ?>
				</p>
			</div>

		</aside>

	</div><!-- /.cart-layout -->

</div><!-- /.cart-wrap -->

<?php do_action( 'woocommerce_after_cart' ); ?>
