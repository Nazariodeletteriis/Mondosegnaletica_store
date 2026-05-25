/**
 * Mobile nav toggle.
 */
export function initNav() {
	const toggle = document.querySelector('.nav-toggle');
	const nav    = document.querySelector('.nav-primary');

	if (!toggle || !nav) return;

	toggle.addEventListener('click', () => {
		const isOpen = nav.classList.toggle('nav-primary--open');
		toggle.setAttribute('aria-expanded', String(isOpen));
		document.body.style.overflow = isOpen ? 'hidden' : '';
	});

	// Close on Escape
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && nav.classList.contains('nav-primary--open')) {
			nav.classList.remove('nav-primary--open');
			toggle.setAttribute('aria-expanded', 'false');
			document.body.style.overflow = '';
		}
	});
}

initNav();
