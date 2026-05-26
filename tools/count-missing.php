<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;

// Query corretta: prodotti che NON hanno neanche un _thumbnail_id > 0
$ids = $wpdb->get_col("
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
    ORDER BY p.ID
");

WP_CLI::log( count($ids) . " prodotti senza thumbnail valido." );
foreach ($ids as $id) {
    $sku = get_post_meta($id, '_sku', true);
    WP_CLI::log( "  ID:$id SKU:$sku " . get_the_title($id) );
}
