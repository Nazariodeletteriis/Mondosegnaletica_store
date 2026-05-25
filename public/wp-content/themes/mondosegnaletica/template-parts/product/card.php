<?php
/**
 * Product card — riusabile in listing, bestseller, related.
 *
 * @var WC_Product $product  Passato da ms_get_template_part() o via setup_postdata
 */

if ( ! isset( $product ) ) {
	global $product;
}

if ( ! $product instanceof WC_Product ) return;

$product_id  = $product->get_id();
$sku         = $product->get_sku() ?: '—';
$name        = $product->get_name();
$price       = (float) $product->get_price();
$link        = get_permalink( $product_id );
$img_id      = $product->get_image_id();
$img_src     = $img_id ? wp_get_attachment_image_url( $img_id, 'ms-product-card' ) : wc_placeholder_img_src( 'ms-product-card' );
$img_alt     = $img_id ? get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';

// FIG. CdS dal product meta (opzionale)
$fig_cds     = get_post_meta( $product_id, '_ms_fig_cds', true );

// Badge
$is_on_sale  = $product->is_on_sale();
$is_in_stock = $product->is_in_stock();
?>

<article class="product-card" itemscope itemtype="https://schema.org/Product">

	<a href="<?php echo esc_url( $link ); ?>" class="product-card__image" tabindex="-1" aria-hidden="true">
		<img
			src="<?php echo esc_url( $img_src ); ?>"
			alt="<?php echo esc_attr( $img_alt ?: $name ); ?>"
			loading="lazy"
			itemprop="image"
		>

		<?php if ( $is_on_sale ) : ?>
			<span class="product-card__badge product-card__badge--sale">Offerta</span>
		<?php elseif ( ! $is_in_stock ) : ?>
			<span class="product-card__badge product-card__badge--out">Esaurito</span>
		<?php endif; ?>
	</a>

	<div class="product-card__body">
		<span class="product-card__sku"><?php echo esc_html( $sku ); ?></span>

		<?php if ( $fig_cds ) : ?>
			<span class="product-card__fig">FIG. <?php echo esc_html( $fig_cds ); ?> C.d.S.</span>
		<?php endif; ?>

		<h3 class="product-card__name" itemprop="name">
			<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $name ); ?></a>
		</h3>

		<div class="product-card__meta">
			<div class="product-card__price-wrap" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
				<span class="product-card__price">
					<?php echo esc_html( ms_format_price( $price ) ); ?>
				</span>
				<span class="product-card__price-vat">+ IVA</span>
				<meta itemprop="price" content="<?php echo esc_attr( $price ); ?>">
				<meta itemprop="priceCurrency" content="EUR">
			</div>

			<?php if ( $is_in_stock ) : ?>
				<button
					class="product-card__add"
					data-product-id="<?php echo esc_attr( $product_id ); ?>"
					aria-label="<?php echo esc_attr( sprintf( __( 'Aggiungi %s al carrello', 'mondosegnaletica' ), $name ) ); ?>"
					title="Aggiungi al carrello"
				>
					<span class="material-symbols-outlined" aria-hidden="true">add_shopping_cart</span>
				</button>
			<?php else : ?>
				<span class="availability-dot availability-dot--out" role="status">Esaurito</span>
			<?php endif; ?>
		</div>

	</div>

</article>
