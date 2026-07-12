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

$img_ok = $nome_ok = $saltati = $errori = $sostituiti = 0;

foreach ( $voci as $v ) {
	$sku = (string) ( $v['sku'] ?? '' );
	$pid = $sku ? wc_get_product_id_by_sku( $sku ) : 0;

	if ( ! $pid ) { $saltati++; continue; }

	$product = wc_get_product( $pid );
	if ( ! $product ) { $saltati++; continue; }

	// ─── nome ────────────────────────────────────────────────────────────────
	// Il nome viene allineato a quello del catalogo, senza aggiungerci nulla.
	//
	// La versione precedente ci appiccicava in coda "— FIG. <codice>", perché il nome
	// arrivava da una lettura a vista del disegno e il codice andava rimesso. Adesso arriva
	// da out/prodotti.json, cioè dalla stessa fonte che ha battezzato il prodotto durante
	// l'import — e il codice figura, dov'è previsto, è già dentro. Riappenderlo produceva
	// "Segnale di divieto — FIG. 73 — FIG. 73".
	//
	// Riallineare e basta ripara anche i nomi già raddoppiati dalle passate precedenti.
	$nome_nuovo = trim( (string) ( $v['nome'] ?? '' ) );

	if ( $nome_nuovo && $nome_nuovo !== $product->get_name() ) {
		if ( ! $dry ) {
			$product->set_name( $nome_nuovo );
			$product->save();
		}
		$nome_ok++;
	}

	// ─── immagine ────────────────────────────────────────────────────────────
	// Su ogni prodotto resta scritto QUALE ritaglio gli è stato applicato. Serve a far
	// convergere lo script quando la mappatura cambia: la versione precedente si limitava
	// a saltare i prodotti che avevano già un'immagine, così un prodotto a cui l'aggancio
	// posizionale aveva messo il cartello sbagliato se lo sarebbe tenuto per sempre —
	// proprio i casi che il nuovo aggancio serve a correggere.
	$nome_file = (string) ( $v['file'] ?? '' );
	$applicato = (string) get_post_meta( $pid, '_ms_figura_file', true );
	$img_id    = $product->get_image_id();

	if ( $img_id && $applicato === $nome_file ) { continue; }   // già quella giusta

	$file = $base . '/figures/' . $nome_file;
	if ( ! $nome_file || ! file_exists( $file ) ) { $saltati++; continue; }

	if ( $dry ) { $img_ok++; continue; }

	try {
		// L'immagine vecchia va rimossa, non lasciata orfana in libreria. Si cancella solo
		// se è figlia di questo prodotto, cioè se l'abbiamo caricata noi: un'immagine
		// caricata a mano dal cliente non è nostra da buttare.
		if ( $img_id ) {
			$att = get_post( $img_id );
			if ( $att && (int) $att->post_parent === (int) $pid ) {
				wp_delete_attachment( $img_id, true );
			}
			$product->set_image_id( 0 );
			$sostituiti++;
		}

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
		update_post_meta( $pid, '_ms_figura_file', $nome_file );
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
	'%d immagini (%d sostituite) · %d nomi aggiornati · %d saltati · %d errori',
	$img_ok, $sostituiti, $nome_ok, $saltati, $errori
) );
