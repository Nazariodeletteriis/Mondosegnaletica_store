/**
 * Category tabs — homepage 02 / CATALOGO
 * Switcha pannelli prodotti per categoria senza page reload.
 */
export function initCategoryTabs() {
	const containers = document.querySelectorAll('.section-catalog');
	if (!containers.length) return;

	containers.forEach((section) => {
		const tabs   = section.querySelectorAll('.cat-tab');
		const panels = section.querySelectorAll('.cat-panel');

		if (!tabs.length) return;

		function activate(index) {
			tabs.forEach((t, i) => {
				const isActive = i === index;
				t.classList.toggle('cat-tab--active', isActive);
				t.setAttribute('aria-selected', isActive ? 'true' : 'false');
				t.setAttribute('tabindex', isActive ? '0' : '-1');
			});
			panels.forEach((p, i) => {
				const isActive = i === index;
				p.classList.toggle('cat-panel--active', isActive);
				p.hidden = !isActive;
			});
		}

		tabs.forEach((tab, i) => {
			tab.addEventListener('click', () => activate(i));
			tab.addEventListener('keydown', (e) => {
				if (e.key === 'ArrowRight') activate(Math.min(i + 1, tabs.length - 1));
				if (e.key === 'ArrowLeft')  activate(Math.max(i - 1, 0));
			});
		});

		activate(0);
	});
}

initCategoryTabs();
