<?php
/**
 * Sideload immagini da assets/epanza-extra-images.csv
 * Formato: SKU,epanza_url
 * Sovrascrive sempre il thumbnail esistente (per correggere match sbagliati).
 *
 * Uso: ddev exec wp --path=/var/www/html/public eval-file /var/www/html/tools/sideload-extra.php
 */

defined('ABSPATH') || exit;

$csv_path = rtrim(ABSPATH, '/') . '/../assets/epanza-extra-images.csv';
if (!file_exists($csv_path)) {
    WP_CLI::error("File non trovato: $csv_path");
    return;
}

$handle = fopen($csv_path, 'r');
$headers = fgetcsv($handle);
$sku_col = array_search('SKU', $headers);
$url_col = array_search('epanza_url', $headers);
$done = 0; $errors = 0;

while (($row = fgetcsv($handle)) !== false) {
    $sku = trim($row[$sku_col] ?? '');
    $url = trim($row[$url_col] ?? '');
    if (!$sku || !$url) continue;

    $product_id = wc_get_product_id_by_sku($sku);
    if (!$product_id) {
        WP_CLI::warning("SKU non trovato: $sku");
        continue;
    }

    // Rimuovi thumbnail precedente (potrebbe essere sbagliato)
    delete_post_meta($product_id, '_thumbnail_id');

    usleep(600000);
    $att = media_sideload_image($url, $product_id, null, 'id');
    if (is_wp_error($att)) {
        WP_CLI::warning("ERRORE $sku: " . $att->get_error_message());
        $errors++;
    } else {
        set_post_thumbnail($product_id, $att);
        WP_CLI::log("  ✓ $sku (ID:$product_id) → att:$att");
        $done++;
    }
}
fclose($handle);
WP_CLI::success("Fine: $done scaricate · $errors errori.");
