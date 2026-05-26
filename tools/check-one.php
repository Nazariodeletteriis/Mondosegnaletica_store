<?php
defined('ABSPATH') || exit;
global $wpdb;

// Check product ID 12 (STOP sign)
$product_id = 12;
WP_CLI::log("Product $product_id: " . get_the_title($product_id));
WP_CLI::log("get_post_thumbnail_id: " . var_export(get_post_thumbnail_id($product_id), true));
WP_CLI::log("has_post_thumbnail: " . var_export(has_post_thumbnail($product_id), true));

$metas = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id=%d AND meta_key IN ('_thumbnail_id','_product_image_gallery') ORDER BY meta_key",
    $product_id
));
foreach ($metas as $m) {
    WP_CLI::log("  meta [{$m->meta_key}] = {$m->meta_value}");
}

// Check if any attachment is associated
$attachments = $wpdb->get_results($wpdb->prepare(
    "SELECT ID, guid FROM wp_posts WHERE post_parent=%d AND post_type='attachment' LIMIT 5",
    $product_id
));
WP_CLI::log("Attachments sotto questo prodotto: " . count($attachments));
foreach ($attachments as $a) {
    WP_CLI::log("  attachment ID:{$a->ID} — {$a->guid}");
}
