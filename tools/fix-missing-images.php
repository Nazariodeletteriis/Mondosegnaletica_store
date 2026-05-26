<?php
/**
 * Fix immagini mancanti — cerca prodotti senza thumbnail e tenta di scaricare da epanza.com
 * per SKU o per corrispondenza nome.
 *
 * Uso: ddev exec wp --path=/var/www/html/public eval-file /var/www/html/tools/fix-missing-images.php
 */

defined( 'ABSPATH' ) || exit;

$dry_run = false;

// ── Trova prodotti senza thumbnail ─────────────────────────────────────
global $wpdb;
$no_thumb = $wpdb->get_results("
    SELECT DISTINCT p.ID, p.post_title,
        sm.meta_value as sku
    FROM wp_posts p
    LEFT JOIN wp_postmeta tm ON p.ID = tm.post_id AND tm.meta_key = '_thumbnail_id'
    LEFT JOIN wp_postmeta sm ON p.ID = sm.post_id AND sm.meta_key = '_sku'
    WHERE p.post_type = 'product'
      AND p.post_status = 'publish'
      AND (tm.meta_value IS NULL OR CAST(tm.meta_value AS UNSIGNED) = 0)
    ORDER BY p.ID
");

WP_CLI::log( count($no_thumb) . " prodotti senza thumbnail." );

if ( empty($no_thumb) ) {
    WP_CLI::success( "Nessun prodotto mancante." );
    return;
}

// ── CSV SKU → URL immagine ──────────────────────────────────────────────
$csv_path = rtrim( ABSPATH, '/' ) . '/../assets/woocommerce-import.csv';
$sku_images = [];
if ( file_exists($csv_path) ) {
    $handle = fopen( $csv_path, 'r' );
    $headers = fgetcsv( $handle, 0, ',', '"', '\\' );
    $sku_col = array_search( 'SKU', $headers );
    $img_col = array_search( 'Images', $headers );
    if ( $sku_col !== false && $img_col !== false ) {
        while ( ($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false ) {
            $sku = trim($row[$sku_col] ?? '');
            $img = trim($row[$img_col] ?? '');
            if ($sku && $img) $sku_images[$sku] = $img;
        }
    }
    fclose($handle);
    WP_CLI::log( count($sku_images) . " URL nel CSV." );
}

// ── Epanza slug patterns ────────────────────────────────────────────────
// Fallback: costruisce URL epanza dal nome prodotto se non c'è nel CSV
function ms_guess_epanza_url( string $title ): string {
    $slug = strtolower($title);
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s]+/', '-', trim($slug));
    $slug = preg_replace('/-+/', '-', $slug);
    return 'https://www.epanza.com/it/' . $slug . '/img/main.jpg';
}

// ── Loop ────────────────────────────────────────────────────────────────
$done = 0; $errors = 0;

foreach ( $no_thumb as $row ) {
    $product_id = (int) $row->ID;
    $sku        = $row->sku ?: '';
    $title      = $row->post_title;

    // 1. Cerca URL nel CSV per SKU esatto
    $img_url = $sku_images[$sku] ?? null;

    // 2. Fallback: cerca nel CSV per SKU parziale (es. MS-SGV-STP-001 → cerca prefix)
    if ( ! $img_url ) {
        foreach ( $sku_images as $csv_sku => $csv_url ) {
            if ( stripos($csv_sku, $sku) !== false || stripos($sku, $csv_sku) !== false ) {
                $img_url = $csv_url;
                break;
            }
        }
    }

    // 3. Log e skip se dry_run
    $source = $img_url ? 'CSV' : 'skip';
    WP_CLI::log( sprintf( "[%s] SKU:%-35s %s", $source, $sku, mb_substr($title,0,50) ) );

    if ( ! $img_url ) {
        $errors++;
        continue;
    }

    if ( $dry_run ) { $done++; continue; }

    // Normalizza URL
    if ( ! preg_match('/\.(jpg|jpeg|png|webp|gif)(\?|$)/i', $img_url) ) {
        $img_url .= '.jpg';
    }

    usleep( 500000 ); // 500ms tra download

    $attachment_id = media_sideload_image( $img_url, $product_id, null, 'id' );

    if ( is_wp_error($attachment_id) ) {
        WP_CLI::warning( "  Errore download: " . $attachment_id->get_error_message() );
        $errors++;
    } else {
        set_post_thumbnail( $product_id, $attachment_id );
        WP_CLI::log( "  ✓ Scaricata attachment ID $attachment_id → prodotto $product_id" );
        $done++;
    }
}

WP_CLI::success( "Completato: $done scaricate · $errors saltate/errori." );
