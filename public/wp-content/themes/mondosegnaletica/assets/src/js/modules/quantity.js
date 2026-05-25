/**
 * Quantity selector +/- buttons.
 * Compatibile con WooCommerce (aggiorna input[name="quantity"]).
 */
export function initQuantity() {
	document.querySelectorAll('.quantity-selector').forEach((selector) => {
		const input   = selector.querySelector('.qty-input');
		const btnMinus = selector.querySelector('[data-qty="minus"]');
		const btnPlus  = selector.querySelector('[data-qty="plus"]');

		if (!input) return;

		const min = parseInt(input.min || '1', 10);
		const max = parseInt(input.max || '9999', 10);

		function updateQty(delta) {
			const current = parseInt(input.value || '1', 10);
			const next = Math.min(max, Math.max(min, current + delta));
			input.value = next;
			// Trigger change per WooCommerce
			input.dispatchEvent(new Event('change', { bubbles: true }));
		}

		btnMinus?.addEventListener('click', () => updateQty(-1));
		btnPlus?.addEventListener('click', ()  => updateQty(1));

		// Prevent invalid manual input
		input.addEventListener('blur', () => {
			const val = parseInt(input.value, 10);
			if (isNaN(val) || val < min) input.value = min;
			if (val > max) input.value = max;
		});
	});
}

initQuantity();
