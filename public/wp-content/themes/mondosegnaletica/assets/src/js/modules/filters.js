/**
 * Sidebar filtri — collapse/expand gruppi, submit form su change.
 */
export function initFilters() {
	// Collapse toggle
	const filterGroups = document.querySelectorAll('.filter-group');
	filterGroups.forEach((group) => {
		const title = group.querySelector('.filter-group__title');
		if (!title) return;

		title.addEventListener('click', () => {
			group.classList.toggle('filter-group--collapsed');
		});
	});

	// Auto-submit filtri su change (WooCommerce widget compatibile)
	const filterForm = document.querySelector('.filters-form');
	if (!filterForm) return;

	const checkboxes = filterForm.querySelectorAll('input[type="checkbox"]');
	const priceSlider = filterForm.querySelector('.filter-price__slider');

	checkboxes.forEach(cb => {
		cb.addEventListener('change', () => filterForm.submit());
	});

	if (priceSlider) {
		// Debounce per lo slider prezzo
		let priceTimer;
		priceSlider.addEventListener('input', () => {
			clearTimeout(priceTimer);
			priceTimer = setTimeout(() => filterForm.submit(), 600);
		});
	}
}

initFilters();
