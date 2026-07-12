<?php
/**
 * Toglie dai prodotti le immagini che la mappa corrente non giustifica.
 *
 * Serve a smaltire i residui degli agganci vecchi. L'aggancio precedente cercava il codice
 * figura in TUTTO il listino, e in sezioni come ACCESSORI la "figura" è una lettera che vale
 * solo dentro la sua pagina: la 'A' di pagina 3 è un segnale di velocità, la 'A' di pagina 6
 * è una tuta da lavoro. Quel modo di agganciare ha messo addosso a dei prodotti immagini che
 * non sono le loro, e quelle immagini restano attaccate anche dopo aver corretto la mappa,
 * perché nessuna voce nuova le sovrascrive.
 *
 * Regola: se un prodotto ha un'immagine ma non compare in out/figure_immagini.json, quella
 * immagine non è dimostrabile e va via. Meglio un prodotto senza foto che un prodotto con la
 * foto di un altro — a maggior ragione su merce omologata, dove la foto è parte di ciò che il
 * cliente sta comprando.
 *
 * Non tocca le immagini caricate a mano (allegati che non sono figli del prodotto).
 *
 *   wp eval-file tools/import-listini/purge_immagini.php dry-run
 *   wp eval-file tools/import-listini/purge_immagini.php
 */

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$dry  = ( ( $args[0] ?? '' ) === 'dry-run' );
$base = '/var/www/html/tools/import-listini';
$map  = $base . '/out/figure_immagini.json';

if ( ! file_exists( $map ) ) {
	WP_CLI::error( "Manca $map — generalo con link_figures.py" );
}

$voci = json_decode( (string) file_get_contents( $map ), true );
if ( ! is_array( $voci ) ) { WP_CLI::error( 'JSON non valido' ); }

$giustificati = [];
foreach ( $voci as $v ) {
	if ( ! empty( $v['sku'] ) ) { $giustificati[ (string) $v['sku'] ] = true; }
}
WP_CLI::log( sprintf( 'Immagini giustificate dalla mappa: %d %s', count( $giustificati ), $dry ? '(DRY RUN)' : '' ) );

$ids = get_posts( [
	'post_type'      => 'product',
	'post_status'    => [ 'publish', 'draft' ],
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'meta_key'       => '_thumbnail_id',
] );

$tolti = $tenuti = $manuali = $rotti = 0;

foreach ( $ids as $pid ) {
	$product = wc_get_product( $pid );
	if ( ! $product ) { continue; }

	if ( isset( $giustificati[ (string) $product->get_sku() ] ) ) { $tenuti++; continue; }

	// Riga _thumbnail_id con valore 0: non mostra niente in vetrina, ma fa risultare il
	// prodotto "con immagine" a chiunque conti dal database. Via.
	$img_id = $product->get_image_id();
	if ( ! $img_id ) {
		if ( ! $dry ) { delete_post_meta( $pid, '_thumbnail_id' ); }
		$rotti++;
		continue;
	}

	$att = get_post( $img_id );

	// Riferimento che punta a un allegato che non esiste più: in vetrina non si vede
	// nulla, ma il prodotto risulta "con immagine" a chiunque conti dal database. Va tolto
	// il riferimento, non l'allegato — che non c'è.
	if ( ! $att ) {
		if ( ! $dry ) {
			$product->set_image_id( 0 );
			$product->save();
			delete_post_meta( $pid, '_thumbnail_id' );
			delete_post_meta( $pid, '_ms_figura_file' );
		}
		$rotti++;
		continue;
	}

	// Un allegato che non è figlio di questo prodotto non l'abbiamo caricato noi:
	// è roba del cliente, non è nostra da cancellare.
	if ( (int) $att->post_parent !== (int) $pid ) { $manuali++; continue; }

	if ( $dry ) { $tolti++; continue; }

	wp_delete_attachment( $img_id, true );
	$product->set_image_id( 0 );
	$product->save();
	// set_image_id(0) + save() non toglie la riga: WooCommerce riscrive _thumbnail_id
	// col valore 0. Va cancellata dopo il save, o il prodotto continua a risultare
	// "con immagine" a chi conta dal database.
	delete_post_meta( $pid, '_thumbnail_id' );
	delete_post_meta( $pid, '_ms_figura_file' );
	$tolti++;
}

if ( ! $dry ) { wc_delete_product_transients(); }

WP_CLI::success( sprintf(
	'%d immagini non giustificate rimosse · %d riferimenti rotti ripuliti · %d confermate dalla mappa · %d caricate a mano (intoccate)',
	$tolti, $rotti, $tenuti, $manuali
) );
