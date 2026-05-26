<?php
/**
 * Sideload solo i prodotti parent che nel CSV hanno un'immagine diretta
 * E attualmente non hanno thumbnail.
 */
defined( 'ABSPATH' ) || exit;

$csv_path = rtrim( ABSPATH, '/' ) . '/../assets/woocommerce-import.csv';
$handle   = fopen( $csv_path, 'r' );
$headers  = fgetcsv( $handle, 0, ',', '"', '\\' );
$sku_col  = array_search( 'SKU', $headers );
$img_col  = array_search( 'Images', $headers );
$type_col = array_search( 'Type', $headers );

$to_sideload = [];
while ( ( $row = fgetcsv( $handle, 0, ',', '"', '\\' ) ) !== false ) {
    $type = trim( $row[ $type_col ] ?? '' );
    $sku  = trim( $row[ $sku_col ] ?? '' );
    $img  = trim( $row[ $img_col ] ?? '' );
    // Solo parent (simple/variable) con immagine
    if ( in_array( $type, [ 'simple', 'variable' ] ) && $sku && $img ) {
        $to_sideload[ $sku ] = $img;
    }
}
fclose( $handle );

WP_CLI::log( count($to_sideload) . " parent con URL nel CSV." );

$done = 0; $skipped = 0; $errors = 0;

foreach ( $to_sideload as $sku => $img_url ) {
    $product_id = wc_get_product_id_by_sku( $sku );
    if ( ! $product_id ) { $skipped++; continue; }

    // Già ha thumbnail? Skip
    global $wpdb;
    $has = $wpdb->get_var( $wpdb->prepare(
        "SELECT 1 FROM wp_postmeta WHERE post_id=%d AND meta_key='_thumbnail_id' AND CAST(meta_value AS UNSIGNED) > 0",
        $product_id
    ) );
    if ( $has ) { $skipped++; continue; }

    if ( ! preg_match( '/\.(jpg|jpeg|png|webp|gif)(\?|$)/i', $img_url ) ) {
        $img_url .= '.jpg';
    }
    usleep( 600000 );

    $att = media_sideload_image( $img_url, $product_id, null, 'id' );
    if ( is_wp_error( $att ) ) {
        WP_CLI::warning( "ERRORE $sku: " . $att->get_error_message() );
        $errors++;
    } else {
        set_post_thumbnail( $product_id, $att );
        WP_CLI::log( "  ✓ $sku (ID:$product_id) → att:$att" );
        $done++;
    }
}
WP_CLI::success( "$done scaricate · $skipped saltate · $errors errori." );
