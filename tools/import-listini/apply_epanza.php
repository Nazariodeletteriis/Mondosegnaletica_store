<?php
/**
 * Mette le FOTOGRAFIE dei cartelli al posto dei disegni di listino.
 *
 * Quello che il sito mostra oggi sono i pittogrammi ritagliati dal PDF del fornitore:
 * disegni al tratto su fondo bianco, che dentro le card scure si vedono male. Epanza — che è
 * il competitor — dei cartelli ha le foto vere, e le sue schede portano il codice figura
 * nell'URL. Dove il codice combacia col nostro, la foto è dello STESSO cartello: non è una
 * somiglianza, è un'identità. Dove non combacia non si tocca niente.
 *
 * Perché un meta diverso da apply_images.php:
 *   apply_images.php scrive `_ms_figura_file` = il ritaglio di listino che ha applicato, e lo
 *   usa per non rifare due volte lo stesso lavoro. Se qui riscrivessimo QUEL meta, al prossimo
 *   giro apply_images.php si accorgerebbe che "l'immagine applicata non è la mia" e rimetterebbe
 *   il disegno sopra la fotografia. Quindi qui si scrive `_ms_epanza_file`, e apply_images.php
 *   impara a non toccare i prodotti che ce l'hanno.
 *
 *   wp eval-file tools/import-listini/apply_epanza.php dry-run
 *   wp eval-file tools/import-listini/apply_epanza.php
 */

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$dry     = ( ( $args[0] ?? '' ) === 'dry-run' );
$base    = '/var/www/html/tools/import-listini';
$mapfile = $base . '/out/epanza_proposte.json';
$imgdir  = $base . '/epanza-img/';

if ( ! file_exists( $mapfile ) ) {
	WP_CLI::error( "Manca $mapfile — generalo con: python3 scrape_epanza.py --scarica" );
}

$voci = json_decode( (string) file_get_contents( $mapfile ), true );
if ( ! is_array( $voci ) ) { WP_CLI::error( 'JSON non valido' ); }

// Senza 'file' la proposta è solo un abbinamento sulla carta: l'immagine non è stata scaricata.
$voci = array_values( array_filter( $voci, static fn( $v ) => ! empty( $v['file'] ) ) );

WP_CLI::log( sprintf( 'Foto da applicare: %d %s', count( $voci ), $dry ? '(DRY RUN)' : '' ) );

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$fatti = $sostituiti = $saltati = $errori = 0;

foreach ( $voci as $v ) {
	$sku = (string) ( $v['sku'] ?? '' );
	$pid = $sku ? wc_get_product_id_by_sku( $sku ) : 0;
	if ( ! $pid ) { $saltati++; continue; }

	$product = wc_get_product( $pid );
	if ( ! $product ) { $saltati++; continue; }

	$nome_file = (string) $v['file'];
	$file      = $imgdir . $nome_file;
	if ( ! file_exists( $file ) ) { $saltati++; continue; }

	// Già questa foto, su questo prodotto: non si rifà.
	$applicato = (string) get_post_meta( $pid, '_ms_epanza_file', true );
	$img_id    = $product->get_image_id();
	if ( $img_id && $applicato === $nome_file ) { continue; }

	if ( $dry ) { $fatti++; continue; }

	try {
		// Il disegno di listino va rimosso, non lasciato orfano in libreria — ma si cancella
		// solo se è figlio di questo prodotto, cioè se l'abbiamo caricato noi. Un'immagine
		// caricata a mano dal cliente non è nostra da buttare.
		if ( $img_id ) {
			$att = get_post( $img_id );
			if ( $att && (int) $att->post_parent === (int) $pid ) {
				wp_delete_attachment( $img_id, true );
			}
			$product->set_image_id( 0 );
			$sostituiti++;
		}

		$tmp = wp_tempnam( $nome_file );
		copy( $file, $tmp );

		$att_id = media_handle_sideload(
			[ 'name' => $nome_file, 'tmp_name' => $tmp ],
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
		update_post_meta( $pid, '_ms_epanza_file', $nome_file );
		$fatti++;
	} catch ( Throwable $e ) {
		$errori++;
		WP_CLI::warning( sprintf( '%s: %s', $sku, $e->getMessage() ) );
	}
}

if ( ! $dry ) { wc_delete_product_transients(); }

WP_CLI::success( sprintf(
	'%d foto applicate (%d al posto del disegno) · %d saltati · %d errori',
	$fatti, $sostituiti, $saltati, $errori
) );
