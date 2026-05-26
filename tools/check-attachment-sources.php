<?php
defined('ABSPATH') || exit;
global $wpdb;

// Cerca prodotti con thumbnail e guarda la sorgente dell'immagine
$products_with_thumb = $wpdb->get_results("
    SELECT p.ID as product_id, p.post_title,
           tm.meta_value as thumbnail_id
    FROM wp_posts p
    JOIN wp_postmeta tm ON p.ID = tm.post_id AND tm.meta_key = '_thumbnail_id'
    WHERE p.post_type = 'product'
      AND p.post_status = 'publish'
      AND CAST(tm.meta_value AS UNSIGNED) > 0
    GROUP BY p.ID
    LIMIT 10
");

foreach ($products_with_thumb as $r) {
    $att_id = (int)$r->thumbnail_id;
    $guid = $wpdb->get_var($wpdb->prepare("SELECT guid FROM wp_posts WHERE ID=%d", $att_id));
    // Cerca sorgente originale nei meta dell'attachment
    $source = get_post_meta($att_id, '_source_url', true);
    WP_CLI::log(sprintf("  Product:%d att:%d guid:%s source:%s", $r->product_id, $att_id, substr($guid,0,80), $source));
}
