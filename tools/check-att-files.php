<?php
defined('ABSPATH') || exit;
global $wpdb;
$rows = $wpdb->get_results("
    SELECT p.ID, pm.meta_value as file
    FROM wp_posts p
    JOIN wp_postmeta pm ON p.ID=pm.post_id AND pm.meta_key='_wp_attached_file'
    WHERE p.post_type='attachment'
    ORDER BY p.ID
    LIMIT 15
");
foreach ($rows as $r) {
    WP_CLI::log("{$r->ID}: {$r->file}");
}
