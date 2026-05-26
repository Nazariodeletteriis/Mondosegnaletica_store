<?php
defined( 'ABSPATH' ) || exit;

global $wpdb;
$count = $wpdb->get_var("SELECT COUNT(*) FROM wp_postmeta WHERE meta_key='_thumbnail_id' AND CAST(meta_value AS UNSIGNED) > 0");
WP_CLI::log( "Prodotti con thumbnail > 0: $count" );

$total_q = $wpdb->get_var("SELECT COUNT(*) FROM wp_posts WHERE post_type='product' AND post_status='publish'");
WP_CLI::log( "Totale prodotti published: $total_q" );

$no_thumb = $wpdb->get_results("
    SELECT p.ID, p.post_title, sm.meta_value as sku
    FROM wp_posts p
    LEFT JOIN wp_postmeta tm ON p.ID = tm.post_id AND tm.meta_key = '_thumbnail_id'
    LEFT JOIN wp_postmeta sm ON p.ID = sm.post_id AND sm.meta_key = '_sku'
    WHERE p.post_type = 'product' AND p.post_status = 'publish'
    AND (tm.meta_value IS NULL OR CAST(tm.meta_value AS UNSIGNED) = 0)
    ORDER BY sm.meta_value
");
WP_CLI::log( "Prodotti senza thumbnail (" . count($no_thumb) . " totali):" );
foreach ( $no_thumb as $r ) {
    WP_CLI::log( sprintf( "  ID:%-4s SKU:%-35s %s", $r->ID, $r->sku ?: '(no sku)', mb_substr($r->post_title, 0, 60) ) );
}
