<?php
/**
 * WooCommerce Cart vuoto — override "Sistema Strada".
 *
 * FRAGMENT: nessun get_header()/get_footer() — la pagina /carrello/ è resa
 * dallo shortcode [woocommerce_cart] dentro page.php.
 *
 * Nota: `woocommerce_cart_is_empty` stampa il messaggio nativo
 * (<p class="cart-empty wc-empty-cart-message ...>). Quel nodo serve a cart.js
 * (update_wc_div lo cerca per rimpiazzare il carrello via AJAX) → va mantenuto.
 * Lo stilizziamo come headline invece di duplicarne il testo.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package mondosegnaletica
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="cart-empty-wrap">

	<span class="label-section"><?php esc_html_e( '01 / Carrello', 'mondosegnaletica' ); ?></span>

	<?php
		/**
		 * @hooked wc_empty_cart_message - 10
		 */
		do_action( 'woocommerce_cart_is_empty' );
	?>

	<p class="cart-empty-wrap__lede">
		<?php esc_html_e( 'Nessun articolo selezionato. Sfoglia il catalogo: oltre 1.400 referenze omologate secondo il Codice della Strada, pronte a magazzino.', 'mondosegnaletica' ); ?>
	</p>

	<div class="cart-empty-wrap__actions">
		<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
			<a class="btn btn--primary return-to-shop" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
				<?php echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Vai al catalogo', 'mondosegnaletica' ) ) ); ?>
			</a>
		<?php endif; ?>

		<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/richiedi-preventivo' ) ); ?>">
			<?php esc_html_e( 'Richiedi preventivo', 'mondosegnaletica' ); ?>
		</a>
	</div>

	<p class="cart-empty-wrap__hud">
		<?php esc_html_e( '43.8438° N · 10.5061° E — Spedizione 24/48h da Lucca', 'mondosegnaletica' ); ?>
	</p>

</div>
