/**
 * Carousel generico — bestseller, nuovi arrivi.
 * initCarousel(el): cerca .carousel-track, .carousel-btn--prev, .carousel-btn--next nell'el.
 */
export function initCarousel(el) {
	const track = el.querySelector('.carousel-track');
	const prev  = el.querySelector('.carousel-btn--prev');
	const next  = el.querySelector('.carousel-btn--next');

	if (!track) return;

	function getCardWidth() {
		const first = track.firstElementChild;
		if (!first) return 0;
		const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
		return first.getBoundingClientRect().width + gap;
	}

	function updateButtons() {
		if (!prev || !next) return;
		prev.disabled = track.scrollLeft <= 1;
		next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
	}

	if (prev) prev.addEventListener('click', () => {
		track.scrollBy({ left: -getCardWidth(), behavior: 'smooth' });
	});

	if (next) next.addEventListener('click', () => {
		track.scrollBy({ left: getCardWidth(), behavior: 'smooth' });
	});

	track.addEventListener('scroll', updateButtons, { passive: true });
	updateButtons();
}

export function initAllCarousels() {
	document.querySelectorAll('.carousel').forEach(initCarousel);
}

initAllCarousels();
