/**
 * PDP tab switcher — Descrizione / Specifiche / Normativa / Download
 */
export function initTabs() {
	const tabBtns   = document.querySelectorAll('.tab-btn');
	const tabPanels = document.querySelectorAll('.tab-panel');

	if (!tabBtns.length) return;

	tabBtns.forEach((btn) => {
		btn.addEventListener('click', () => {
			const target = btn.dataset.tab;

			tabBtns.forEach(b => {
				b.classList.remove('tab-btn--active');
				b.setAttribute('aria-selected', 'false');
			});

			tabPanels.forEach(p => {
				p.classList.remove('tab-panel--active');
				p.setAttribute('hidden', '');
			});

			btn.classList.add('tab-btn--active');
			btn.setAttribute('aria-selected', 'true');

			const panel = document.getElementById(`tab-${target}`);
			if (panel) {
				panel.classList.add('tab-panel--active');
				panel.removeAttribute('hidden');
			}
		});
	});
}

initTabs();
