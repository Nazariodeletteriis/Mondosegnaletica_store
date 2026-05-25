/**
 * PDP thumbnail gallery — swap main image on thumb click.
 */
export function initGallery() {
	const mainImg = document.querySelector('.product-gallery__main img');
	const thumbs  = document.querySelectorAll('.product-gallery__thumb');

	if (!mainImg || !thumbs.length) return;

	thumbs.forEach((thumb) => {
		thumb.addEventListener('click', () => {
			const src = thumb.querySelector('img')?.src;
			if (!src) return;

			mainImg.src = src;
			mainImg.alt = thumb.querySelector('img')?.alt ?? '';

			thumbs.forEach(t => t.classList.remove('product-gallery__thumb--active'));
			thumb.classList.add('product-gallery__thumb--active');
		});
	});
}

initGallery();
