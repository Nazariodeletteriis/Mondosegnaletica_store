<?php
/**
 * Mondo Segnaletica — functions.php
 *
 * Carica i moduli inc/ nell'ordine corretto.
 * Non inserire logica diretta qui.
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

// Costanti tema
define( 'MS_THEME_DIR', get_template_directory() );
define( 'MS_THEME_URI', get_template_directory_uri() );
define( 'MS_VERSION',   wp_get_theme()->get( 'Version' ) );

// Moduli
require_once MS_THEME_DIR . '/inc/setup.php';
require_once MS_THEME_DIR . '/inc/enqueue.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once MS_THEME_DIR . '/inc/woocommerce.php';
}

// ─────────────────────────────────────────────
// Helper template
// ─────────────────────────────────────────────

/**
 * Recupera il template part con $args passati come variabili locali.
 *
 * @param string $slug   Path relativo senza .php (es. "template-parts/home/hero")
 * @param array  $args   Variabili iniettate nel template
 */
function ms_get_template_part( string $slug, array $args = [] ): void {
	$template_file = locate_template( $slug . '.php' );
	if ( ! $template_file ) return;
	if ( ! empty( $args ) ) {
		extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
	}
	include $template_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
}

/**
 * Ritorna il badge disponibilità prodotto.
 */
function ms_availability_badge( \WC_Product $product ): string {
	if ( ! $product->is_in_stock() ) {
		return '<span class="availability-dot availability-dot--out">Esaurito</span>';
	}

	$stock = $product->get_stock_quantity();

	if ( $stock !== null && $stock <= 10 ) {
		return '<span class="availability-dot availability-dot--limited">Disponibilità limitata</span>';
	}

	return '<span class="availability-dot availability-dot--in">Disponibile</span>';
}

/**
 * Formatta il prezzo B2B (con IVA esclusa).
 */
function ms_format_price( float $price ): string {
	return '€ ' . number_format( $price, 2, ',', '.' );
}

/**
 * Ritorna i dati delle 6 categorie principali MS.
 * Usato come fallback se WooCommerce non ha ancora i dati.
 */
function ms_get_default_categories(): array {
	return [
		[ 'code' => 'CAT-01', 'name' => 'Segnaletica Verticale',   'count' => 412, 'slug' => 'segnaletica-verticale'   ],
		[ 'code' => 'CAT-02', 'name' => 'Segnaletica Orizzontale', 'count' => 156, 'slug' => 'segnaletica-orizzontale' ],
		[ 'code' => 'CAT-03', 'name' => 'Coni & Transenne',        'count' => 184, 'slug' => 'coni-transenne'          ],
		[ 'code' => 'CAT-04', 'name' => 'Delineatori & Paletti',   'count' => 96,  'slug' => 'delineatori-paletti'     ],
		[ 'code' => 'CAT-05', 'name' => 'Cantieristica',           'count' => 312, 'slug' => 'cantieristica'           ],
		[ 'code' => 'CAT-06', 'name' => 'Dissuasori & Accessori',  'count' => 245, 'slug' => 'dissuasori-accessori'    ],
	];
}
