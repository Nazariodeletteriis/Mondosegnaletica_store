/**
 * Carousel generico — bestseller, nuovi arrivi.
 * Autoplay loop con pausa su hover.
 */
const AUTOPLAY_INTERVAL = 3500;

export function initCarousel(el) {
	const track = el.querySelector('.carousel-track');
	const prev  = el.querySelector('.carousel-btn--prev');
	const next  = el.querySelector('.carousel-btn--next');

	if (!track) return;

	let autoplayTimer = null;
	let isHovered = false;

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

	function scrollNext() {
		const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
		if (atEnd) {
			track.scrollTo({ left: 0, behavior: 'smooth' });
		} else {
			track.scrollBy({ left: getCardWidth(), behavior: 'smooth' });
		}
	}

	function startAutoplay() {
		stopAutoplay();
		autoplayTimer = setInterval(() => {
			if (!isHovered) scrollNext();
		}, AUTOPLAY_INTERVAL);
	}

	function stopAutoplay() {
		if (autoplayTimer) {
			clearInterval(autoplayTimer);
			autoplayTimer = null;
		}
	}

	if (prev) prev.addEventListener('click', () => {
		track.scrollBy({ left: -getCardWidth(), behavior: 'smooth' });
	});

	if (next) next.addEventListener('click', () => {
		scrollNext();
	});

	track.addEventListener('scroll', updateButtons, { passive: true });

	el.addEventListener('mouseenter', () => { isHovered = true; });
	el.addEventListener('mouseleave', () => { isHovered = false; });

	// Pausa autoplay quando l'utente interagisce manualmente con la track
	track.addEventListener('pointerdown', stopAutoplay);
	track.addEventListener('pointerup', startAutoplay);

	updateButtons();
	startAutoplay();
}

export function initAllCarousels() {
	document.querySelectorAll('.carousel').forEach(initCarousel);
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initAllCarousels);
} else {
	initAllCarousels();
}
