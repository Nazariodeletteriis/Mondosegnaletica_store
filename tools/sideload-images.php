<?php
/**
 * Sideload immagini prodotti da URL nel CSV.
 *
 * Uso: ddev wp eval-file tools/sideload-images.php
 *      ddev wp eval-file tools/sideload-images.php -- --dry-run
 *      ddev wp eval-file tools/sideload-images.php -- --limit=20
 *
 * Lo script:
 * 1. Legge il CSV assets/woocommerce-import.csv
 * 2. Per ogni riga con SKU + Images URL:
 *    - Cerca il prodotto WC per SKU
 *    - Se non ha già un thumbnail, scarica l'immagine e la associa
 */

defined( 'ABSPATH' ) || exit;

// ── Configurazione ───────────────────────────────────────────────────────
// Modifica qui prima di eseguire lo script.
$dry_run = false; // true = mostra cosa farebbe, senza scaricare
$limit   = 0;    // 0 = tutti; es. 10 = scarica solo i primi 10

// ── Percorso CSV ────────────────────────────────────────────────────────
// ABSPATH = /var/www/html/public/ dentro DDEV
// Il CSV è in /var/www/html/assets/woocommerce-import.csv
$csv_path = rtrim( ABSPATH, '/' ) . '/../assets/woocommerce-import.csv';

if ( ! file_exists( $csv_path ) ) {
	WP_CLI::error( "CSV non trovato: $csv_path\nAssicurati di eseguire: ddev wp eval-file tools/sideload-images.php" );
	return;
}

// ── Carica WP media helpers ─────────────────────────────────────────────
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// ── Parse CSV: SKU → primo URL immagine ─────────────────────────────────
$handle  = fopen( $csv_path, 'r' );
$header  = fgetcsv( $handle, 0, ',', '"', '\\' );
$img_col = array_search( 'Images', $header, true );
$sku_col = array_search( 'SKU', $header, true );

if ( $img_col === false || $sku_col === false ) {
	WP_CLI::error( "Colonne Images o SKU non trovate nel CSV." );
	fclose( $handle );
	return;
}

$sku_images = [];
while ( ( $row = fgetcsv( $handle, 0, ',', '"', '\\' ) ) !== false ) {
	$sku = trim( $row[ $sku_col ] ?? '' );
	$img = trim( $row[ $img_col ] ?? '' );
	if ( $sku && $img && ! isset( $sku_images[ $sku ] ) ) {
		$sku_images[ $sku ] = $img;
	}
}
fclose( $handle );

$total_csv = count( $sku_images );
WP_CLI::log( sprintf( "CSV letto: %d SKU con URL immagine.", $total_csv ) );

if ( $dry_run ) {
	WP_CLI::log( "[DRY RUN — nessuna modifica verrà salvata]" );
}

// ── Loop ────────────────────────────────────────────────────────────────
$done    = 0;
$skipped = 0;
$errors  = 0;
$missing = 0;

$progress = WP_CLI\Utils\make_progress_bar( 'Download immagini', $total_csv );

foreach ( $sku_images as $sku => $img_url ) {
	$progress->tick();

	// Trova prodotto WC per SKU
	$product_id = wc_get_product_id_by_sku( $sku );
	if ( ! $product_id ) {
		$missing++;
		continue;
	}

	// Già ha un thumbnail? Skip
	if ( get_post_thumbnail_id( $product_id ) ) {
		$skipped++;
		continue;
	}

	if ( $dry_run ) {
		WP_CLI::log( sprintf( "[DRY] %-40s → %s", $sku, $img_url ) );
		$done++;
		if ( $limit && $done >= $limit ) break;
		continue;
	}

	// Normalizza URL: PrestaShop non sempre ha estensione — prova con .jpg
	$url = $img_url;
	if ( ! preg_match( '/\.(jpg|jpeg|png|webp|gif)(\?|$)/i', $url ) ) {
		$url .= '.jpg';
	}

	// Rate limit gentile verso epanza.com
	usleep( 300000 ); // 300ms

	// Sideload
	$attachment_id = media_sideload_image( $url, $product_id, null, 'id' );

	if ( is_wp_error( $attachment_id ) ) {
		// Prova senza estensione .jpg se il primo tentativo fallisce
		if ( str_ends_with( $url, '.jpg' ) && $url !== $img_url ) {
			$attachment_id = media_sideload_image( $img_url, $product_id, null, 'id' );
		}

		if ( is_wp_error( $attachment_id ) ) {
			WP_CLI::warning( sprintf( "ERRORE %-35s: %s", $sku, $attachment_id->get_error_message() ) );
			$errors++;
			if ( $limit && ( $done + $errors ) >= $limit ) break;
			continue;
		}
	}

	set_post_thumbnail( $product_id, $attachment_id );

	WP_CLI::success( sprintf( "OK %-40s (attachment #%d)", $sku, $attachment_id ) );
	$done++;

	if ( $limit && $done >= $limit ) break;
}

$progress->finish();

WP_CLI::log( '' );
WP_CLI::log( sprintf(
	"Risultato: %d scaricate · %d saltate (già avevano immagine) · %d SKU non trovati in WC · %d errori",
	$done, $skipped, $missing, $errors
) );
