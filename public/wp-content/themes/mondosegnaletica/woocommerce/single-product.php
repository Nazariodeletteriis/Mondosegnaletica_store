<?php
/**
 * WooCommerce — PDP Single Product.
 * Override di woocommerce/templates/single-product.php
 *
 * Rewrite completo: design "Sistema Strada" v2.
 * Layout: Gallery (45%) | Info (55%) — breadcrumb styled + HUD + qty tiers.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

while ( have_posts() ) :
	the_post();
	$product = wc_get_product( get_the_ID() );
	if ( ! $product instanceof WC_Product ) continue;

	$product_id   = $product->get_id();
	$name         = $product->get_name();
	$sku          = $product->get_sku();
	$price        = (float) $product->get_price();
	$description  = $product->get_description();
	$short_desc   = $product->get_short_description();
	$gallery_ids  = $product->get_gallery_image_ids();
	$main_img_id  = $product->get_image_id();
	$main_img_src = $main_img_id
		? wp_get_attachment_image_url( $main_img_id, 'ms-product-main' )
		: wc_placeholder_img_src();
	$main_img_alt = $main_img_id
		? (string) get_post_meta( $main_img_id, '_wp_attachment_image_alt', true )
		: $name;

	// Meta custom
	$fig_cds       = get_post_meta( $product_id, '_ms_fig_cds', true );
	$homologations = get_post_meta( $product_id, '_ms_homologations', true );
	$specs         = get_post_meta( $product_id, '_ms_specs', true );    // array serializzato
	$downloads     = get_post_meta( $product_id, '_ms_downloads', true ); // array serializzato
	?>

	<div class="pdp-wrap">

		<!-- ═══ TOPBAR: Breadcrumb + HUD ═══ -->
		<div class="pdp-topbar">
			<div class="pdp-breadcrumb">
				<span class="pdp-breadcrumb__prefix">01 / CATALOGO</span>
				<span class="pdp-breadcrumb__sep">›</span>
				<?php
				woocommerce_breadcrumb( [
					'delimiter'   => '<span class="pdp-breadcrumb__sep">›</span>',
					'wrap_before' => '<span class="pdp-breadcrumb__chain">',
					'wrap_after'  => '</span>',
					'before'      => '',
					'after'       => '',
				] );
				?>
			</div>
			<div class="pdp-hud" aria-hidden="true">
				<span>REF: 43.8376° N · 10.4951° E</span>
				<span>DOC_TYPE: PRODUCT_DETAIL</span>
			</div>
		</div>

		<!-- ═══ LAYOUT 2 COLONNE ═══ -->
		<div class="pdp-layout" itemscope itemtype="https://schema.org/Product">

			<!-- ─── GALLERY (sinistra 45%) ─── -->
			<div class="pdp-gallery" role="region" aria-label="Galleria immagini prodotto">

				<div class="pdp-gallery__main">
					<?php if ( $fig_cds ) : ?>
					<div class="pdp-gallery__badge" aria-label="Omologazione">
						OMOLOGATO CdS · FIG. <?php echo esc_html( $fig_cds ); ?>
					</div>
					<?php endif; ?>

					<img
						id="pdp-main-img"
						src="<?php echo esc_url( $main_img_src ); ?>"
						alt="<?php echo esc_attr( $main_img_alt ); ?>"
						loading="eager"
						fetchpriority="high"
						itemprop="image"
					>
				</div>

				<?php if ( ! empty( $gallery_ids ) ) : ?>
				<div class="pdp-gallery__thumbs" role="list" aria-label="Miniature prodotto">

					<!-- Thumb immagine principale -->
					<button
						class="pdp-gallery__thumb pdp-gallery__thumb--active"
						type="button"
						role="listitem"
						data-img-src="<?php echo esc_url( $main_img_src ); ?>"
						data-img-alt="<?php echo esc_attr( $main_img_alt ); ?>"
						aria-label="<?php esc_attr_e( 'Immagine principale', 'mondosegnaletica' ); ?>"
						aria-pressed="true"
					>
						<?php echo wp_get_attachment_image( $main_img_id, 'ms-product-thumb', false, [ 'loading' => 'eager' ] ); ?>
					</button>

					<?php foreach ( array_slice( $gallery_ids, 0, 3 ) as $gid ) :
						$gsrc = wp_get_attachment_image_url( $gid, 'ms-product-main' ) ?: '';
						$galt = (string) get_post_meta( $gid, '_wp_attachment_image_alt', true ) ?: $name;
					?>
					<button
						class="pdp-gallery__thumb"
						type="button"
						role="listitem"
						data-img-src="<?php echo esc_url( $gsrc ); ?>"
						data-img-alt="<?php echo esc_attr( $galt ); ?>"
						aria-label="<?php echo esc_attr( $galt ); ?>"
						aria-pressed="false"
					>
						<?php echo wp_get_attachment_image( $gid, 'ms-product-thumb', false, [ 'loading' => 'lazy' ] ); ?>
					</button>
					<?php endforeach; ?>

				</div>
				<?php endif; ?>

			</div><!-- /.pdp-gallery -->

			<!-- ─── INFO (destra 55%) ─── -->
			<div class="pdp-info">

				<?php if ( $sku ) : ?>
				<span class="pdp-sku" itemprop="sku"><?php echo esc_html( $sku ); ?></span>
				<?php endif; ?>

				<h1 class="pdp-title" itemprop="name"><?php echo esc_html( $name ); ?></h1>

				<?php if ( $homologations || $short_desc ) : ?>
				<p class="pdp-subtitle">
					<?php echo wp_kses_post( $homologations ?: $short_desc ); ?>
				</p>
				<?php endif; ?>

				<!-- Prezzo -->
				<div class="pdp-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
					<span class="pdp-price__amount"><?php echo esc_html( ms_format_price( $price ) ); ?></span>
					<span class="pdp-price__label">IVA ESCLUSA · PER UNITÀ</span>
					<meta itemprop="price" content="<?php echo esc_attr( $price ); ?>">
					<meta itemprop="priceCurrency" content="EUR">
					<link itemprop="availability" href="<?php echo $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>">
				</div>

				<!-- Tabella sconti quantità B2B -->
				<?php
				ms_get_template_part( 'template-parts/product/quantity-table', [
					'product'    => $product,
					'base_price' => $price,
				] );
				?>

				<!-- WooCommerce: variazioni + add-to-cart -->
				<div class="pdp-cart-block">
					<span class="pdp-cart-block__qty-label">QUANTITÀ</span>
					<?php woocommerce_template_single_add_to_cart(); ?>
				</div>

				<!-- CTA preventivo -->
				<a
					href="<?php echo esc_url( home_url( '/contatti?prodotto=' . urlencode( $name ) . '&sku=' . urlencode( $sku ) ) ); ?>"
					class="pdp-quote-btn"
				>
					RICHIEDI PREVENTIVO PER QUANTITÀ
				</a>

				<!-- Trust indicator -->
				<div class="pdp-trust" role="status">
					<?php echo wp_kses_post( ms_availability_badge( $product ) ); ?>
					<span class="pdp-trust__ship">· Spedizione 24/48h da Lucca</span>
				</div>

			</div><!-- /.pdp-info -->

		</div><!-- /.pdp-layout -->

		<!-- ═══ DESCRIZIONE PRODOTTO (opzionale) ═══ -->
		<?php if ( $description ) : ?>
		<div class="pdp-description">
			<div class="section-header">
				<span class="label-section">01 / DESCRIZIONE</span>
			</div>
			<div class="pdp-description__content">
				<?php echo wp_kses_post( wpautop( $description ) ); ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- ═══ SPECIFICHE TECNICHE ═══ -->
		<div class="pdp-specs">
			<div class="section-header">
				<span class="label-section">SPECIFICHE TECNICHE</span>
			</div>
			<div class="pdp-specs__layout">

				<!-- Colonna sinistra: download -->
				<?php if ( is_array( $downloads ) && ! empty( $downloads ) ) : ?>
				<div class="pdp-specs__downloads">
					<p class="pdp-specs__col-label">Documenti</p>
					<div class="pdp-download-list">
						<?php foreach ( $downloads as $dl ) : ?>
						<a
							href="<?php echo esc_url( $dl['url'] ); ?>"
							class="pdp-download"
							download
							target="_blank"
							rel="noopener noreferrer"
						>
							<span class="material-symbols-outlined" aria-hidden="true">download</span>
							<div class="pdp-download__info">
								<span class="pdp-download__name"><?php echo esc_html( $dl['name'] ); ?></span>
								<?php if ( ! empty( $dl['size'] ) ) : ?>
								<span class="pdp-download__size"><?php echo esc_html( $dl['size'] ); ?></span>
								<?php endif; ?>
							</div>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

				<!-- Colonna destra: tabella proprietà + normativa -->
				<div class="pdp-specs__table-wrap">
					<?php if ( is_array( $specs ) && ! empty( $specs ) ) : ?>
					<table class="specs-table" aria-label="Specifiche tecniche">
						<tbody>
							<?php foreach ( $specs as $label => $value ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $label ); ?></th>
								<td><?php echo esc_html( $value ); ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php else : ?>
					<?php wc_display_product_attributes( $product ); ?>
					<?php endif; ?>

					<?php if ( $fig_cds ) : ?>
					<div class="pdp-specs__normativa">
						<p class="pdp-specs__col-label">Riferimento normativo</p>
						<p>Conforme all'<strong>Art. <?php echo esc_html( $fig_cds ); ?></strong> del D.Lgs. 285/1992 (Codice della Strada) e relativo Regolamento di Attuazione (D.P.R. 495/1992). Omologato secondo il <strong>D.M. 31/03/1995</strong>.</p>
					</div>
					<?php endif; ?>
				</div>

			</div>
		</div><!-- /.pdp-specs -->

		<!-- ═══ PRODOTTI CORRELATI ═══ -->
		<div class="pdp-related">
			<div class="section-header">
				<span class="label-section">Correlati</span>
				<h2 class="section-title">Potrebbero servirti.</h2>
			</div>
			<?php
			$related_ids = wc_get_related_products( $product_id, 4 );
			if ( ! empty( $related_ids ) ) :
			?>
			<div class="products-grid">
				<?php foreach ( $related_ids as $related_id ) :
					$related = wc_get_product( $related_id );
					if ( $related instanceof WC_Product ) :
						ms_get_template_part( 'template-parts/product/card', [ 'product' => $related ] );
					endif;
				endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

	</div><!-- /.pdp-wrap -->

	<!-- ─── Gallery JS: thumb switching ─── -->
	<script>
	(function () {
		'use strict';
		var mainImg = document.getElementById('pdp-main-img');
		var thumbs  = document.querySelectorAll('.pdp-gallery__thumb');
		if (!mainImg || !thumbs.length) return;

		thumbs.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var src = btn.getAttribute('data-img-src');
				var alt = btn.getAttribute('data-img-alt');
				if (!src) return;

				// Aggiorna immagine principale
				mainImg.src = src;
				mainImg.alt = alt || '';

				// Stato active
				thumbs.forEach(function (b) {
					b.classList.remove('pdp-gallery__thumb--active');
					b.setAttribute('aria-pressed', 'false');
				});
				btn.classList.add('pdp-gallery__thumb--active');
				btn.setAttribute('aria-pressed', 'true');
			});
		});
	})();
	</script>

<?php endwhile; ?>

<?php get_footer( 'shop' ); ?>
