/**
 * Toggle vista griglia/lista nel listing categoria.
 * Persiste preferenza in sessionStorage.
 */
export function initViewToggle() {
	const grid     = document.querySelector('.products-grid');
	const btnGrid  = document.querySelector('[data-view="grid"]');
	const btnList  = document.querySelector('[data-view="list"]');

	if (!grid || !btnGrid || !btnList) return;

	const savedView = sessionStorage.getItem('ms-listing-view') || 'grid';
	applyView(savedView);

	btnGrid.addEventListener('click', () => {
		applyView('grid');
		sessionStorage.setItem('ms-listing-view', 'grid');
	});

	btnList.addEventListener('click', () => {
		applyView('list');
		sessionStorage.setItem('ms-listing-view', 'list');
	});

	function applyView(view) {
		if (view === 'list') {
			grid.classList.add('products-grid--list');
			btnList.classList.add('view-toggle__btn--active');
			btnGrid.classList.remove('view-toggle__btn--active');
		} else {
			grid.classList.remove('products-grid--list');
			btnGrid.classList.add('view-toggle__btn--active');
			btnList.classList.remove('view-toggle__btn--active');
		}
	}
}

initViewToggle();
