<?php
/**
 * Tabella sconti quantità B2B — layout 3 colonne orizzontale always-visible.
 * Design "Sistema Strada" v2: wrapper .qty-tiers + grid .qty-tiers__grid.
 *
 * @var WC_Product $product
 * @var float      $base_price
 */

if ( ! isset( $product ) || ! $product instanceof WC_Product ) return;

$base_price = isset( $base_price ) ? (float) $base_price : (float) $product->get_price();
$tiers      = ms_get_qty_discounts( $product->get_id() );

if ( empty( $tiers ) ) return;
?>

<div class="qty-tiers">
	<span class="qty-tiers__header">SCONTI PER QUANTITÀ</span>
	<div class="qty-tiers__grid">
		<?php
		$total = count( $tiers );
		foreach ( $tiers as $i => $tier ) :
			$range_label = $tier['max'] === null
				? $tier['min'] . '+ PZ'
				: $tier['min'] . '–' . $tier['max'] . ' PZ';

			$discounted = $tier['pct'] > 0
				? $base_price * ( 1 - $tier['pct'] / 100 )
				: $base_price;

			// L'ultimo tier con sconto è il "best"
			$is_best = ( $i === $total - 1 ) && $tier['pct'] > 0;
			$classes  = 'qty-tier';
			if ( $is_best ) $classes .= ' qty-tier--best';
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<span class="qty-tier__label"><?php echo esc_html( $range_label ); ?></span>
			<span class="qty-tier__price"><?php echo esc_html( ms_format_price( $discounted ) ); ?></span>
			<?php if ( $tier['pct'] > 0 ) : ?>
			<span class="qty-tier__discount">–<?php echo esc_html( $tier['pct'] ); ?>%</span>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
</div>
