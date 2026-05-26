<?php
/**
 * Copia thumbnail da prodotti "VRT" agli stessi prodotti con SKU diverso.
 *
 * Strategia: per ogni prodotto senza thumbnail, cerca il prodotto più simile
 * per nome tra quelli con thumbnail e copia il thumb_id.
 *
 * Uso: ddev exec wp --path=/var/www/html/public eval-file /var/www/html/tools/match-copy-thumbs.php
 */

defined('ABSPATH') || exit;

global $wpdb;

// ── Prodotti CON thumbnail ────────────────────────────────────────────────
$with_thumb = $wpdb->get_results("
    SELECT p.ID, p.post_title, tm.meta_value AS thumb_id
    FROM wp_posts p
    JOIN wp_postmeta tm ON p.ID = tm.post_id AND tm.meta_key = '_thumbnail_id'
    WHERE p.post_type = 'product' AND p.post_status = 'publish'
      AND CAST(tm.meta_value AS UNSIGNED) > 0
    GROUP BY p.ID
");

// ── Prodotti SENZA thumbnail ──────────────────────────────────────────────
$without_thumb = $wpdb->get_results("
    SELECT p.ID, p.post_title, sm.meta_value AS sku
    FROM wp_posts p
    LEFT JOIN wp_postmeta sm ON p.ID = sm.post_id AND sm.meta_key = '_sku'
    WHERE p.post_type = 'product' AND p.post_status = 'publish'
      AND NOT EXISTS (
          SELECT 1 FROM wp_postmeta tm
          WHERE tm.post_id = p.ID
            AND tm.meta_key = '_thumbnail_id'
            AND CAST(tm.meta_value AS UNSIGNED) > 0
      )
    GROUP BY p.ID
");

WP_CLI::log(sprintf("Con thumb: %d  |  Senza: %d", count($with_thumb), count($without_thumb)));

// ── Normalizza titolo per matching ────────────────────────────────────────
function ms_norm( string $s ): string {
    $s = mb_strtolower($s, 'UTF-8');
    // Rimuovi fig. numerica, codici, formati dimensione
    $s = preg_replace('/fig\.\s*[\d\/]+[a-z]*/i', '', $s);
    $s = preg_replace('/[\d]+\s*[xX]\s*[\d]+/', '', $s);
    $s = preg_replace('/\b(classe|cm|mm|h)\s*\d+/i', '', $s);
    // Transliterate
    $map = ['à'=>'a','è'=>'e','é'=>'e','ì'=>'i','ò'=>'o','ù'=>'u'];
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
    $s = preg_replace('/\s+/', ' ', trim($s));
    return $s;
}

// ── Best match finder usando similar_text ────────────────────────────────
function ms_best_match( string $title, array $candidates ): ?object {
    $norm_title = ms_norm($title);
    $best_score = 0;
    $best       = null;
    foreach ($candidates as $c) {
        $norm = ms_norm($c->post_title);
        similar_text($norm_title, $norm, $pct);
        if ($pct > $best_score) {
            $best_score = $pct;
            $best       = $c;
            $best->score = $pct;
        }
    }
    return ($best_score >= 55) ? $best : null;
}

// ── Loop ────────────────────────────────────────────────────────────────
$copied  = 0;
$no_match = 0;

foreach ($without_thumb as $row) {
    $match = ms_best_match($row->post_title, $with_thumb);
    if ($match) {
        update_post_meta($row->ID, '_thumbnail_id', (int)$match->thumb_id);
        WP_CLI::log(sprintf(
            "  ✓ ID:%-4d ← ID:%-4d (%.0f%%) | %s ← %s",
            $row->ID, $match->ID, $match->score,
            mb_substr($row->post_title, 0, 45),
            mb_substr($match->post_title, 0, 45)
        ));
        $copied++;
    } else {
        WP_CLI::warning(sprintf("  ✗ No match: %s", mb_substr($row->post_title, 0, 60)));
        $no_match++;
    }
}

WP_CLI::success("Thumbnail copiati: $copied  |  Senza match: $no_match");
