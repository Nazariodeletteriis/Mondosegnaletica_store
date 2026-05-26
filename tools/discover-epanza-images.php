<?php
/**
 * Scopre immagini epanza.com per i prodotti senza thumbnail.
 *
 * Strategia:
 * 1. Costruisce slug dal titolo prodotto (normalizzazione italiana)
 * 2. Prova HEAD request su epanza.com con slug + range ID da $id_start a $id_end
 * 3. Quando trova un match (HTTP 200), scarica e associa al prodotto
 *
 * ATTENZIONE: fa molte HTTP requests — usa su ambiente locale/dev.
 * ID range noti dal CSV: 1759–4200 circa.
 *
 * Uso: ddev exec wp --path=/var/www/html/public eval-file /var/www/html/tools/discover-epanza-images.php
 */

defined('ABSPATH') || exit;

// ── Config ────────────────────────────────────────────────────────────────
$id_start  = 1700;
$id_end    = 5000;
$step      = 1;      // controlla ogni ID (lento ma preciso)
$limit     = 5;      // max prodotti da processare in questa run (per test)
$delay_ms  = 200000; // 200ms tra probe

// ── Slug builder ──────────────────────────────────────────────────────────
function ms_epanza_slug( string $title ): string {
    // Rimuovi contenuto tra parentesi e em-dash
    $s = preg_replace('/\s*[\(—–\-]+.*$/', '', $title);
    $s = mb_strtolower( $s, 'UTF-8' );
    // Transliterate accented chars
    $map = ['à'=>'a','è'=>'e','é'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',
            'á'=>'a','ä'=>'a','ö'=>'o','ü'=>'u','ß'=>'ss',
            'ñ'=>'n','ç'=>'c'];
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
    $s = preg_replace('/\s+/', '-', trim($s));
    return $s;
}

// ── Trova prodotti mancanti ───────────────────────────────────────────────
global $wpdb;
$missing = $wpdb->get_results("
    SELECT p.ID, p.post_title, sm.meta_value AS sku
    FROM wp_posts p
    LEFT JOIN wp_postmeta sm ON p.ID = sm.post_id AND sm.meta_key = '_sku'
    WHERE p.post_type = 'product'
      AND p.post_status = 'publish'
      AND NOT EXISTS (
          SELECT 1 FROM wp_postmeta tm
          WHERE tm.post_id = p.ID
            AND tm.meta_key = '_thumbnail_id'
            AND CAST(tm.meta_value AS UNSIGNED) > 0
      )
    GROUP BY p.ID
    ORDER BY p.ID
    LIMIT $limit
");

WP_CLI::log( count($missing) . " prodotti da processare (limit $limit)." );

$found_total = 0;

foreach ( $missing as $row ) {
    $product_id = (int) $row->ID;
    $title      = $row->post_title;
    $slug       = ms_epanza_slug( $title );

    WP_CLI::log( "\n→ ID:$product_id | $title" );
    WP_CLI::log( "  slug: $slug" );

    $img_url = null;

    // Probe: prova .webp poi .jpg per ogni ID
    for ( $id = $id_start; $id <= $id_end; $id += $step ) {
        foreach ( ['.webp', '.jpg'] as $ext ) {
            $url = "https://epanza.com/{$id}-large_default/{$slug}{$ext}";
            $response = wp_remote_head( $url, [
                'timeout'    => 5,
                'user-agent' => 'Mozilla/5.0 (compatible; MondoSegnaletica/1.0)',
            ] );

            if ( ! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200 ) {
                $img_url = $url;
                WP_CLI::log( "  ✓ Trovato: $url" );
                break 2;
            }
        }
        usleep( $delay_ms );
    }

    if ( ! $img_url ) {
        WP_CLI::warning( "  ✗ Non trovato per: $slug" );
        continue;
    }

    // Scarica e associa
    $att = media_sideload_image( $img_url, $product_id, null, 'id' );
    if ( is_wp_error($att) ) {
        WP_CLI::warning( "  Errore download: " . $att->get_error_message() );
    } else {
        set_post_thumbnail( $product_id, $att );
        WP_CLI::log( "  ✓ Thumbnail impostato att:$att" );
        $found_total++;
    }
}

WP_CLI::success( "Fine: $found_total immagini trovate e scaricate." );
