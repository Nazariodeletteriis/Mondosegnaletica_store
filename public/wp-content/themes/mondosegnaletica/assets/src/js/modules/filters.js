/**
 * Sidebar filtri — collapse/expand gruppi.
 * I filtri usano URL query params (link-based), non form submit.
 */
export function initFilters() {
	const filterGroups = document.querySelectorAll('.filter-group');
	filterGroups.forEach((group) => {
		const title = group.querySelector('.filter-group__title');
		if (!title) return;

		title.addEventListener('click', () => {
			const collapsed = group.classList.toggle('filter-group--collapsed');
			title.setAttribute('aria-expanded', String(!collapsed));
		});
	});
}

initFilters();
