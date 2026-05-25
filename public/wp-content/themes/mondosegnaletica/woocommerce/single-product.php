<?php
/**
 * WooCommerce — PDP Single Product.
 * Override di woocommerce/templates/single-product.php
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

while ( have_posts() ) :
	the_post();
	$product = wc_get_product( get_the_ID() );
	if ( ! $product instanceof WC_Product ) continue;

	$product_id    = $product->get_id();
	$name          = $product->get_name();
	$sku           = $product->get_sku();
	$price         = (float) $product->get_price();
	$description   = $product->get_description();
	$short_desc    = $product->get_short_description();
	$gallery_ids   = $product->get_gallery_image_ids();
	$main_img_id   = $product->get_image_id();
	$main_img_src  = $main_img_id ? wp_get_attachment_image_url( $main_img_id, 'ms-product-main' ) : wc_placeholder_img_src();
	$main_img_alt  = $main_img_id ? get_post_meta( $main_img_id, '_wp_attachment_image_alt', true ) : $name;

	// Meta custom
	$fig_cds       = get_post_meta( $product_id, '_ms_fig_cds', true );
	$homologations = get_post_meta( $product_id, '_ms_homologations', true );
	$specs         = get_post_meta( $product_id, '_ms_specs', true ); // array serializzato
	$downloads     = get_post_meta( $product_id, '_ms_downloads', true ); // array serializzato
	?>

	<div class="container">

		<!-- Breadcrumb -->
		<?php woocommerce_breadcrumb(); ?>

		<!-- Layout prodotto -->
		<div class="product-layout" itemscope itemtype="https://schema.org/Product">

			<!-- GALLERY (sinistra) -->
			<div class="product-gallery">
				<div class="product-gallery__main">
					<img
						src="<?php echo esc_url( $main_img_src ); ?>"
						alt="<?php echo esc_attr( $main_img_alt ); ?>"
						id="product-main-img"
						loading="eager"
						fetchpriority="high"
						itemprop="image"
					>
				</div>

				<?php if ( ! empty( $gallery_ids ) ) : ?>
				<div class="product-gallery__thumbs" role="list">
					<!-- Thumb immagine principale -->
					<button
						class="product-gallery__thumb product-gallery__thumb--active"
						role="listitem"
						aria-label="<?php esc_attr_e( 'Immagine principale', 'mondosegnaletica' ); ?>"
					>
						<?php echo wp_get_attachment_image( $main_img_id, 'ms-product-thumb', false, [ 'loading' => 'eager' ] ); ?>
					</button>

					<?php foreach ( array_slice( $gallery_ids, 0, 3 ) as $gid ) : ?>
					<button
						class="product-gallery__thumb"
						role="listitem"
						aria-label="<?php echo esc_attr( get_post_meta( $gid, '_wp_attachment_image_alt', true ) ?: __( 'Immagine prodotto', 'mondosegnaletica' ) ); ?>"
					>
						<?php echo wp_get_attachment_image( $gid, 'ms-product-thumb', false, [ 'loading' => 'lazy' ] ); ?>
					</button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>

			<!-- INFO (destra) -->
			<div class="product-info">

				<div class="product-info__meta">
					<?php if ( $sku ) : ?>
						<span class="product-info__sku"><?php echo esc_html( $sku ); ?></span>
					<?php endif; ?>

					<?php if ( $fig_cds ) : ?>
						<span class="product-info__fig">ART. <?php echo esc_html( $fig_cds ); ?> · CODICE DELLA STRADA</span>
					<?php endif; ?>
				</div>

				<h1 class="product-info__title" itemprop="name"><?php echo esc_html( $name ); ?></h1>

				<?php if ( $homologations ) : ?>
				<div class="product-info__homologations">
					<?php echo wp_kses_post( $homologations ); ?>
				</div>
				<?php elseif ( $short_desc ) : ?>
				<div class="product-info__homologations">
					<?php echo wp_kses_post( $short_desc ); ?>
				</div>
				<?php endif; ?>

				<!-- Prezzo -->
				<div class="product-info__price-section" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
					<div class="product-info__price">
						<?php echo esc_html( ms_format_price( $price ) ); ?>
						<span class="product-info__price-vat">+ IVA</span>
					</div>
					<?php echo ms_availability_badge( $product ); ?>
					<meta itemprop="price" content="<?php echo esc_attr( $price ); ?>">
					<meta itemprop="priceCurrency" content="EUR">
					<link itemprop="availability" href="<?php echo $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>">
				</div>

				<!-- Acquisto -->
				<div class="product-purchase">
					<?php if ( $product->is_in_stock() ) : ?>
					<div class="quantity-selector" role="group" aria-label="<?php esc_attr_e( 'Quantità', 'mondosegnaletica' ); ?>">
						<button type="button" class="qty-btn" data-qty="minus" aria-label="Riduci quantità">
							<span class="material-symbols-outlined" aria-hidden="true">remove</span>
						</button>
						<input
							type="number"
							class="qty-input"
							name="quantity"
							value="1"
							min="1"
							max="<?php echo esc_attr( $product->get_max_purchase_quantity() ?: 9999 ); ?>"
							step="1"
							aria-label="Quantità"
						>
						<button type="button" class="qty-btn" data-qty="plus" aria-label="Aumenta quantità">
							<span class="material-symbols-outlined" aria-hidden="true">add</span>
						</button>
					</div>
					<?php endif; ?>

					<div class="purchase-actions">
						<?php woocommerce_template_single_add_to_cart(); ?>

						<a href="<?php echo esc_url( home_url( '/contatti?prodotto=' . urlencode( $name ) . '&sku=' . urlencode( $sku ) ) ); ?>" class="btn btn--outline btn--request-quote">
							<span class="material-symbols-outlined" aria-hidden="true">request_quote</span>
							Richiedi preventivo per quantità
						</a>
					</div>
				</div>

				<!-- Tabella sconti quantità B2B -->
				<?php
				ms_get_template_part( 'template-parts/product/quantity-table', [
					'product'    => $product,
					'base_price' => $price,
				] );
				?>

			</div><!-- /.product-info -->

		</div><!-- /.product-layout -->

		<!-- Tab: Descrizione / Specifiche / Normativa / Download -->
		<div class="product-tabs">
			<div class="tabs-nav" role="tablist" aria-label="<?php esc_attr_e( 'Informazioni prodotto', 'mondosegnaletica' ); ?>">
				<button class="tab-btn tab-btn--active" role="tab" data-tab="descrizione" aria-selected="true" aria-controls="tab-descrizione" id="tab-btn-descrizione">
					Descrizione
				</button>
				<button class="tab-btn" role="tab" data-tab="specifiche" aria-selected="false" aria-controls="tab-specifiche" id="tab-btn-specifiche" tabindex="-1">
					Specifiche Tecniche
				</button>
				<button class="tab-btn" role="tab" data-tab="normativa" aria-selected="false" aria-controls="tab-normativa" id="tab-btn-normativa" tabindex="-1">
					Normativa C.d.S.
				</button>
				<button class="tab-btn" role="tab" data-tab="download" aria-selected="false" aria-controls="tab-download" id="tab-btn-download" tabindex="-1">
					Download
				</button>
			</div>

			<!-- Descrizione -->
			<div class="tab-panel tab-panel--active" id="tab-descrizione" role="tabpanel" aria-labelledby="tab-btn-descrizione">
				<div class="tab-panel__content">
					<?php
					if ( $description ) {
						echo wp_kses_post( wpautop( $description ) );
					} else {
						echo '<p style="color:var(--color-text-muted)">Descrizione non disponibile.</p>';
					}
					?>
				</div>
			</div>

			<!-- Specifiche -->
			<div class="tab-panel" id="tab-specifiche" role="tabpanel" aria-labelledby="tab-btn-specifiche" hidden>
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
				<!-- Fallback: attributi WooCommerce standard -->
				<?php wc_display_product_attributes( $product ); ?>
				<?php endif; ?>
			</div>

			<!-- Normativa -->
			<div class="tab-panel" id="tab-normativa" role="tabpanel" aria-labelledby="tab-btn-normativa" hidden>
				<div class="tab-panel__content">
					<?php if ( $fig_cds ) : ?>
						<p>Prodotto conforme all'<strong>Art. <?php echo esc_html( $fig_cds ); ?></strong> del D.Lgs. 285/1992 (Codice della Strada) e relativo Regolamento di Attuazione (D.P.R. 495/1992).</p>
						<p>Omologato secondo il <strong>D.M. 31/03/1995</strong> — Caratteristiche degli apparecchi di illuminazione e di segnalazione per i veicoli.</p>
					<?php else : ?>
						<p>Documentazione normativa in aggiornamento. Contattaci per informazioni specifiche di conformità.</p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Download -->
			<div class="tab-panel" id="tab-download" role="tabpanel" aria-labelledby="tab-btn-download" hidden>
				<?php if ( is_array( $downloads ) && ! empty( $downloads ) ) : ?>
				<ul class="download-list">
					<?php foreach ( $downloads as $dl ) : ?>
					<li>
						<a href="<?php echo esc_url( $dl['url'] ); ?>" class="download-item" download target="_blank" rel="noopener">
							<span class="material-symbols-outlined" aria-hidden="true">download</span>
							<span class="download-item__name"><?php echo esc_html( $dl['name'] ); ?></span>
							<?php if ( ! empty( $dl['size'] ) ) : ?>
								<span class="download-item__size"><?php echo esc_html( $dl['size'] ); ?></span>
							<?php endif; ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php else : ?>
					<p style="color:var(--color-text-muted);font-family:var(--font-mono);font-size:var(--text-sm);">Nessun documento disponibile per questo prodotto.</p>
				<?php endif; ?>
			</div>

		</div><!-- /.product-tabs -->

		<!-- Prodotti correlati -->
		<div class="related-products">
			<div class="section-header">
				<span class="label-section">Correlati</span>
				<h2 class="section-title">Prodotti correlati.</h2>
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

	</div><!-- /.container -->

<?php endwhile; ?>

<?php get_footer( 'shop' ); ?>
