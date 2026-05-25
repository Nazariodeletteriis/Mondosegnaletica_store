<?php
/**
 * Tabella sconti quantità B2B.
 *
 * @var WC_Product $product
 * @var float      $base_price
 */

if ( ! isset( $product ) || ! $product instanceof WC_Product ) return;

$base_price = isset( $base_price ) ? (float) $base_price : (float) $product->get_price();
$tiers      = ms_get_qty_discounts( $product->get_id() );
?>

<div class="qty-discounts">
	<div class="qty-discounts__header">
		<span class="material-symbols-outlined" style="font-size:16px;color:var(--color-accent);" aria-hidden="true">local_offer</span>
		<p class="qty-discounts__title">Sconti Quantità B2B</p>
	</div>

	<table class="qty-discounts__table" aria-label="Sconti per quantità">
		<thead>
			<tr>
				<th scope="col">Quantità</th>
				<th scope="col">Sconto</th>
				<th scope="col">Prezzo unitario</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $tiers as $tier ) :
				$range_label = $tier['max'] === null
					? $tier['min'] . '+ pz'
					: $tier['min'] . '–' . $tier['max'] . ' pz';

				$discounted = $tier['pct'] > 0
					? $base_price * ( 1 - $tier['pct'] / 100 )
					: $base_price;
			?>
			<tr<?php echo $tier['pct'] === 0 ? '' : ''; ?>>
				<td><?php echo esc_html( $range_label ); ?></td>
				<td>
					<?php if ( $tier['pct'] > 0 ) : ?>
						<span class="discount-badge">–<?php echo esc_html( $tier['pct'] ); ?>%</span>
					<?php else : ?>
						<span style="color:var(--color-text-muted)">—</span>
					<?php endif; ?>
				</td>
				<td>
					<?php echo esc_html( ms_format_price( $discounted ) ); ?>
					<span class="price__vat"> + IVA</span>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
