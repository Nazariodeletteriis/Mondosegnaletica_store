<?php
/**
 * Copia il thumbnail della prima variazione con immagine al prodotto parent.
 *
 * Uso: ddev exec wp --path=/var/www/html/public eval-file /var/www/html/tools/inherit-variation-thumbs.php
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

// Prodotti parent senza thumbnail
$missing_ids = $wpdb->get_col( "
    SELECT p.ID
    FROM wp_posts p
    WHERE p.post_type = 'product'
      AND p.post_status = 'publish'
      AND NOT EXISTS (
          SELECT 1 FROM wp_postmeta tm
          WHERE tm.post_id = p.ID
            AND tm.meta_key = '_thumbnail_id'
            AND CAST(tm.meta_value AS UNSIGNED) > 0
      )
" );

WP_CLI::log( count($missing_ids) . " prodotti parent senza thumbnail." );

$inherited = 0;
$still_missing = 0;

foreach ( $missing_ids as $parent_id ) {
    // Trova variazioni con thumbnail
    $variation_thumb = $wpdb->get_var( $wpdb->prepare( "
        SELECT tm.meta_value
        FROM wp_posts v
        JOIN wp_postmeta tm ON v.ID = tm.post_id AND tm.meta_key = '_thumbnail_id'
        WHERE v.post_parent = %d
          AND v.post_type = 'product_variation'
          AND v.post_status = 'publish'
          AND CAST(tm.meta_value AS UNSIGNED) > 0
        ORDER BY v.ID ASC
        LIMIT 1
    ", $parent_id ) );

    if ( $variation_thumb ) {
        set_post_thumbnail( $parent_id, (int) $variation_thumb );
        WP_CLI::log( "  ✓ ID:$parent_id ← variation thumbnail $variation_thumb" );
        $inherited++;
    } else {
        // Cerca attachment diretto nel media library per questo parent
        $att = $wpdb->get_var( $wpdb->prepare(
            "SELECT ID FROM wp_posts WHERE post_parent=%d AND post_type='attachment' LIMIT 1",
            $parent_id
        ) );
        if ( $att ) {
            set_post_thumbnail( $parent_id, (int) $att );
            WP_CLI::log( "  ✓ ID:$parent_id ← attachment diretto $att" );
            $inherited++;
        } else {
            $still_missing++;
        }
    }
}

WP_CLI::success( "Thumbnails ereditati dalle variazioni: $inherited · ancora mancanti: $still_missing" );
