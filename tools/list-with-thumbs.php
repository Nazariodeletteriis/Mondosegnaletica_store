<?php
defined('ABSPATH') || exit;
global $wpdb;
$rows = $wpdb->get_results("
    SELECT p.ID, p.post_title, sm.meta_value AS sku, tm.meta_value AS thumb_id
    FROM wp_posts p
    JOIN wp_postmeta tm ON p.ID = tm.post_id AND tm.meta_key = '_thumbnail_id'
    LEFT JOIN wp_postmeta sm ON p.ID = sm.post_id AND sm.meta_key = '_sku'
    WHERE p.post_type = 'product' AND p.post_status = 'publish'
      AND CAST(tm.meta_value AS UNSIGNED) > 0
    GROUP BY p.ID
    ORDER BY p.ID
");
foreach ($rows as $r) {
    $file = get_post_meta((int)$r->thumb_id, '_wp_attached_file', true);
    WP_CLI::log(sprintf("ID:%-4d SKU:%-35s thumb:%s", $r->ID, $r->sku ?: '', basename($file)));
}
WP_CLI::log("\nTotale con thumbnail: " . count($rows));
