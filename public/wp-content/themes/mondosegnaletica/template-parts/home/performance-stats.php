<?php
/**
 * Sezione 04 / PERFORMANCE — 4 stat Anton giallo.
 */

$stats = [
	[ 'number' => '1.200+', 'label' => 'Prodotti a Catalogo' ],
	[ 'number' => '24/48H', 'label' => 'Spedizione da Lucca' ],
	[ 'number' => '100%',   'label' => 'Omologati Codice della Strada' ],
	[ 'number' => '15+',    'label' => 'Anni di Esperienza' ],
];
?>

<section class="section-performance" aria-labelledby="performance-title">
	<div class="container--wide">

		<div class="section-header section-header--center" style="margin-bottom:var(--space-12)">
			<span class="label-section">06 / NUMERI</span>
			<h2 class="section-title sr-only" id="performance-title">Performance e Numeri</h2>
		</div>

		<div class="performance-grid" role="list">
			<?php foreach ( $stats as $stat ) : ?>
			<div class="perf-stat" role="listitem">
				<div class="perf-stat__number" aria-label="<?php echo esc_attr( $stat['number'] ); ?>">
					<?php echo esc_html( $stat['number'] ); ?>
				</div>
				<p class="perf-stat__label"><?php echo esc_html( $stat['label'] ); ?></p>
			</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
