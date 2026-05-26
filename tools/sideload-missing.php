<?php
/**
 * Sideload immagini per i 122 prodotti senza thumbnail.
 * Usa NOT EXISTS per trovare prodotti senza _thumbnail_id valido,
 * poi cerca l'URL nel CSV per SKU.
 *
 * Uso: ddev exec wp --path=/var/www/html/public eval-file /var/www/html/tools/sideload-missing.php
 */

defined( 'ABSPATH' ) || exit;

// ── Config ───────────────────────────────────────────────────────────────
$rate_ms = 400000; // usleep tra download (ms)

// ── Carica CSV ───────────────────────────────────────────────────────────
$csv_path = rtrim( ABSPATH, '/' ) . '/../assets/woocommerce-import.csv';
if ( ! file_exists( $csv_path ) ) {
    WP_CLI::error( "CSV non trovato: $csv_path" );
    return;
}

$handle  = fopen( $csv_path, 'r' );
$headers = fgetcsv( $handle, 0, ',', '"', '\\' );
$sku_col = array_search( 'SKU', $headers );
$img_col = array_search( 'Images', $headers );

if ( $sku_col === false || $img_col === false ) {
    WP_CLI::error( "Colonne SKU o Images non trovate nel CSV." );
    return;
}

$sku_to_url = [];
while ( ( $row = fgetcsv( $handle, 0, ',', '"', '\\' ) ) !== false ) {
    $sku = trim( $row[ $sku_col ] ?? '' );
    $img = trim( $row[ $img_col ] ?? '' );
    if ( $sku && $img && ! isset( $sku_to_url[ $sku ] ) ) {
        $sku_to_url[ $sku ] = $img;
    }
}
fclose( $handle );
WP_CLI::log( count( $sku_to_url ) . " SKU con URL nel CSV." );

// ── Trova prodotti senza thumbnail valido ────────────────────────────────
global $wpdb;
$missing = $wpdb->get_results( "
    SELECT p.ID, p.post_title, sm.meta_value AS sku
    FROM wp_posts p
    LEFT JOIN wp_postmeta sm ON p.ID = sm.post_id AND sm.meta_key = '_sku'
    WHERE p.post_type = 'product'
      AND p.post_status = 'publish'
      AND NOT EXISTS (
          SELECT 1 FROM wp_postmeta tm
          WHERE tm.post_id = p.ID
            AND tm.meta_key = '_thumbnail_id'
            AND CAST(tm.meta_value AS UNSIGNED) > 0
      )
    GROUP BY p.ID
    ORDER BY p.ID
" );

WP_CLI::log( count( $missing ) . " prodotti senza thumbnail da processare." );

$done    = 0;
$skipped = 0;
$errors  = 0;

$progress = WP_CLI\Utils\make_progress_bar( 'Sideload', count( $missing ) );

foreach ( $missing as $row ) {
    $progress->tick();

    $product_id = (int) $row->ID;
    $sku        = $row->sku ?: '';
    $title      = $row->post_title;

    // Cerca URL nel CSV per SKU esatto
    $img_url = $sku_to_url[ $sku ] ?? null;

    if ( ! $img_url ) {
        $skipped++;
        continue;
    }

    // Normalizza estensione
    if ( ! preg_match( '/\.(jpg|jpeg|png|webp|gif)(\?|$)/i', $img_url ) ) {
        $img_url .= '.jpg';
    }

    usleep( $rate_ms );

    $attachment_id = media_sideload_image( $img_url, $product_id, null, 'id' );

    if ( is_wp_error( $attachment_id ) ) {
        WP_CLI::warning( "ERRORE ID:$product_id SKU:$sku — " . $attachment_id->get_error_message() );
        $errors++;
    } else {
        set_post_thumbnail( $product_id, $attachment_id );
        WP_CLI::log( "  ✓ ID:$product_id SKU:$sku attachment:$attachment_id" );
        $done++;
    }
}

$progress->finish();
WP_CLI::success( "Fine: $done scaricate · $skipped senza URL · $errors errori." );
