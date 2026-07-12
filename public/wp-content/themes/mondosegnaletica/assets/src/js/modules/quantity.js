/**
 * Quantity selector +/- buttons.
 * Compatibile con WooCommerce (aggiorna input[name="quantity"]).
 *
 * Delega su document invece di bind per-elemento: WooCommerce rimpiazza
 * .woocommerce-cart-form via AJAX a ogni aggiornamento del carrello, e un
 * listener attaccato al singolo bottone morirebbe con il nodo sostituito.
 */

const clampQty = (input, delta) => {
	const min = parseInt(input.min || '1', 10);
	const max = parseInt(input.max || '9999', 10);
	const current = parseInt(input.value || String(min), 10);
	const next = Math.min(max, Math.max(min, (isNaN(current) ? min : current) + delta));

	input.value = next;
	input.dispatchEvent(new Event('change', { bubbles: true }));
};

export function initQuantity() {
	document.addEventListener('click', (e) => {
		const btn = e.target.closest('.quantity-selector [data-qty]');
		if (!btn) return;

		const input = btn.closest('.quantity-selector')?.querySelector('.qty-input');
		if (!input) return;

		e.preventDefault();
		clampQty(input, btn.dataset.qty === 'plus' ? 1 : -1);
	});

	// Input manuale fuori range
	document.addEventListener(
		'blur',
		(e) => {
			const input = e.target.closest?.('.quantity-selector .qty-input');
			if (!input) return;

			const min = parseInt(input.min || '1', 10);
			const max = parseInt(input.max || '9999', 10);
			const val = parseInt(input.value, 10);

			if (isNaN(val) || val < min) input.value = min;
			else if (val > max) input.value = max;
		},
		true // blur non fa bubbling: serve la fase di capture
	);
}

initQuantity();
