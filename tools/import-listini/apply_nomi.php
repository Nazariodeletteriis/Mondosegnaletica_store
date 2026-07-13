<?php
/**
 * Accorcia il titolo dei prodotti e salva la riga di listino nella descrizione.
 *
 * La colonna ARTICOLO del listino è una riga tecnica, non un nome. L'import la usava tale e
 * quale come titolo, così l'H1 della scheda prodotto diventava «Targa Monofacciale in Lamiera
 * di Alluminio - Spessore 25/10 - Colore Fondo: "rosso Traffico" RAL 3020 (non Rifrangente) -
 * Colore Scritte: ... + Staffa di Ancoraggio (esclusa)»: 338 caratteri di titolo, illeggibile
 * in scheda, nelle card, nel carrello, nelle email d'ordine e in ricerca.
 *
 * Il nome corto lo calcola normalize.py (campo `nome_breve`), tenendo i segmenti che
 * distinguono davvero il prodotto. La riga per intero NON si perde: finisce in cima alla
 * descrizione come «Denominazione a listino», dove serve — è lì che il tecnico la cerca.
 *
 * Idempotente: rigira quante volte vuoi. Non tocca i prodotti già a posto e non duplica il
 * blocco nella descrizione.
 *
 *   wp eval-file tools/import-listini/apply_nomi.php dry-run
 *   wp eval-file tools/import-listini/apply_nomi.php
 */

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$dry  = ( ( $args[0] ?? '' ) === 'dry-run' );
$file = '/var/www/html/tools/import-listini/out/prodotti.json';

if ( ! file_exists( $file ) ) {
	WP_CLI::error( "Manca $file — generalo con normalize.py" );
}

$prodotti = json_decode( (string) file_get_contents( $file ), true );
if ( ! is_array( $prodotti ) ) { WP_CLI::error( 'JSON non valido' ); }

$MARCA = '<!-- ms-denominazione -->';

$rinominati = $descritti = $saltati = $gia_ok = 0;
$esempi = [];

foreach ( $prodotti as $sku => $p ) {
	$pid = wc_get_product_id_by_sku( (string) $sku );
	if ( ! $pid ) { $saltati++; continue; }

	$product = wc_get_product( $pid );
	if ( ! $product ) { $saltati++; continue; }

	$completo = trim( (string) ( $p['nome'] ?? '' ) );
	$breve    = trim( (string) ( $p['nome_breve'] ?? '' ) ) ?: $completo;
	if ( ! $breve ) { $saltati++; continue; }

	$cambia_nome = ( $product->get_name() !== $breve );

	// La riga di listino va in descrizione solo se è stata davvero accorciata: se il titolo
	// coincide col nome completo non c'è niente da recuperare, e ripeterlo sarebbe rumore.
	$serve_desc = ( $completo !== $breve )
		&& ( strpos( (string) $product->get_description(), $MARCA ) === false );

	if ( ! $cambia_nome && ! $serve_desc ) { $gia_ok++; continue; }

	if ( count( $esempi ) < 5 && $cambia_nome ) {
		$esempi[] = sprintf( '%s: %s  →  %s', $sku, mb_substr( $product->get_name(), 0, 46 ) . '…', $breve );
	}

	if ( ! $dry ) {
		if ( $cambia_nome ) { $product->set_name( $breve ); }

		if ( $serve_desc ) {
			$blocco = sprintf(
				'%s<p><strong>Denominazione a listino:</strong> %s</p>',
				$MARCA,
				esc_html( $completo )
			);
			$product->set_description( $blocco . "\n" . $product->get_description() );
		}

		$product->save();
	}

	if ( $cambia_nome ) { $rinominati++; }
	if ( $serve_desc )  { $descritti++; }
}

if ( ! $dry ) { wc_delete_product_transients(); }

foreach ( $esempi as $e ) { WP_CLI::log( '  ' . $e ); }

WP_CLI::success( sprintf(
	'%d titoli accorciati · %d denominazioni salvate in descrizione · %d già a posto · %d senza prodotto %s',
	$rinominati, $descritti, $gia_ok, $saltati, $dry ? '(DRY RUN)' : ''
) );
