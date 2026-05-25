/**
 * woo-custom.js — customizzazioni WooCommerce lato client.
 * Caricato solo nelle pagine WooCommerce.
 */

/**
 * Aggiorna il contatore nel header cart quando WooCommerce
 * aggiorna il fragment (add to cart AJAX).
 */
document.addEventListener('DOMContentLoaded', () => {
	// WooCommerce emette 'wc_fragments_refreshed' dopo AJAX add-to-cart
	jQuery(document.body).on('wc_fragments_refreshed', () => {
		updateCartCount();
	});

	updateCartCount();
});

function updateCartCount() {
	// WooCommerce espone il count nel widget cart fragment
	const cartCountEl = document.querySelector('.header-cart__count');
	if (!cartCountEl) return;

	// Il valore viene aggiornato via WooCommerce fragment nativo
	// — questo è solo il fallback per il primo render
	const wooCartCount = document.querySelector('.woocommerce-mini-cart__total')
		?.dataset?.count;

	if (wooCartCount !== undefined) {
		cartCountEl.textContent = wooCartCount === '0' ? '' : wooCartCount;
		cartCountEl.dataset.count = wooCartCount;
	}
}
