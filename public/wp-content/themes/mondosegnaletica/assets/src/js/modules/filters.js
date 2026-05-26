/**
 * Sidebar filtri:
 * - Collapse/expand gruppi
 * - Checkbox → URL redirect con ?filter_pa_X=slug1,slug2
 */
export function initFilters() {
	// Collapse toggle
	document.querySelectorAll('.filter-group').forEach((group) => {
		const title = group.querySelector('.filter-group__title');
		if (!title) return;
		title.addEventListener('click', () => {
			const collapsed = group.classList.toggle('filter-group--collapsed');
			title.setAttribute('aria-expanded', String(!collapsed));
		});
	});

	// Form intercept → costruisce URL con filter_pa_X=slug1,slug2
	const form = document.querySelector('.filters-form');
	if (!form) return;

	form.addEventListener('change', () => {
		const url = new URL(window.location.href);

		// Rimuove tutti i filter_pa_* esistenti
		for (const key of [...url.searchParams.keys()]) {
			if (key.startsWith('filter_pa_')) url.searchParams.delete(key);
		}
		url.searchParams.delete('paged');

		// Legge i checkbox selezionati e raggruppa per attributo
		const groups = {};
		form.querySelectorAll('input[type="checkbox"]:checked').forEach((cb) => {
			// name è "filter_pa_tipologia[]"
			const key = cb.name.replace('[]', '');
			if (!groups[key]) groups[key] = [];
			groups[key].push(cb.value);
		});

		for (const [key, values] of Object.entries(groups)) {
			url.searchParams.set(key, values.join(','));
		}

		window.location.href = url.toString();
	});
}

initFilters();
