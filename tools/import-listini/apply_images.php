<?php
/**
 * Assegna immagine e nome ai prodotti, agganciando per CODICE FIGURA.
 *
 * Il codice figura viene LETTO dalla didascalia stampata dentro il ritaglio, non
 * dedotto dalla posizione della cella nella pagina: l'aggancio posizionale slittava
 * (il rilevatore scambiava le celle di intestazione della tabella per cartelli) e
 * finiva per mettere l'immagine del cartello sbagliato sul prodotto sbagliato.
 *
 *   wp eval-file tools/import-listini/apply_images.php
 *   wp eval-file tools/import-listini/apply_images.php dry-run
 */

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$dry     = ( ( $args[0] ?? '' ) === 'dry-run' );
$base    = '/var/www/html/tools/import-listini';
$mapfile = $base . '/out/figure_immagini.json';

if ( ! file_exists( $mapfile ) ) {
	WP_CLI::error( "Manca $mapfile — generalo con link_figures.py" );
}

$voci = json_decode( (string) file_get_contents( $mapfile ), true );
if ( ! is_array( $voci ) ) { WP_CLI::error( 'JSON non valido' ); }

WP_CLI::log( sprintf( 'Voci da applicare: %d %s', count( $voci ), $dry ? '(DRY RUN)' : '' ) );

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$img_ok = $nome_ok = $saltati = $errori = 0;

foreach ( $voci as $v ) {
	$sku = (string) ( $v['sku'] ?? '' );
	$pid = $sku ? wc_get_product_id_by_sku( $sku ) : 0;

	if ( ! $pid ) { $saltati++; continue; }

	$product = wc_get_product( $pid );
	if ( ! $product ) { $saltati++; continue; }

	// ─── nome ────────────────────────────────────────────────────────────────
	// Solo se il prodotto ha ancora il nome di ripiego ("… — FIG. 12"): un nome
	// vero, letto dalla tabella del listino, non va sovrascritto da uno dedotto
	// guardando il disegno.
	$nome_nuovo = trim( (string) ( $v['nome'] ?? '' ) );
	$ha_ripiego = (bool) preg_match( '/ — FIG\. /u', $product->get_name() );

	if ( ! $dry && $nome_nuovo && $ha_ripiego ) {
		$fig = trim( (string) ( $v['figura'] ?? '' ) );
		$product->set_name( $fig ? sprintf( '%s — FIG. %s', $nome_nuovo, $fig ) : $nome_nuovo );
		$product->save();
		$nome_ok++;
	} elseif ( $nome_nuovo && $ha_ripiego ) {
		$nome_ok++;
	}

	// ─── immagine ────────────────────────────────────────────────────────────
	if ( $product->get_image_id() ) { continue; }   // già assegnata

	$file = $base . '/figures/' . ( $v['file'] ?? '' );
	if ( ! $file || ! file_exists( $file ) ) { $saltati++; continue; }

	if ( $dry ) { $img_ok++; continue; }

	try {
		$tmp = wp_tempnam( basename( $file ) );
		copy( $file, $tmp );

		$att_id = media_handle_sideload(
			[ 'name' => basename( $file ), 'tmp_name' => $tmp ],
			$pid,
			$product->get_name()
		);

		if ( is_wp_error( $att_id ) ) {
			@unlink( $tmp );
			throw new RuntimeException( $att_id->get_error_message() );
		}

		update_post_meta( $att_id, '_wp_attachment_image_alt', $product->get_name() );
		$product->set_image_id( $att_id );
		$product->save();
		$img_ok++;
	} catch ( Throwable $e ) {
		$errori++;
		WP_CLI::warning( sprintf( '%s: %s', $sku, $e->getMessage() ) );
	}

	if ( 0 === $img_ok % 100 && $img_ok ) {
		WP_CLI::log( sprintf( '  … %d immagini, %d nomi', $img_ok, $nome_ok ) );
	}
}

if ( ! $dry ) { wc_delete_product_transients(); }

WP_CLI::success( sprintf(
	'%d immagini · %d nomi aggiornati · %d saltati · %d errori',
	$img_ok, $nome_ok, $saltati, $errori
) );
