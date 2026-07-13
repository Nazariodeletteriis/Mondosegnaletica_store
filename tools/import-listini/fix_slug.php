<?php
/**
 * Rigenera gli slug dei prodotti dai titoli accorciati.
 *
 * Gli slug erano stati generati da WordPress quando il titolo era ancora la riga tecnica del
 * listino, quindi sono lunghi quanto quella:
 *   /prodotto/targa-monofacciale-in-lamiera-di-alluminio-spessore-25-10-colore-fondo-rosso.../
 * Accorciare il titolo non li tocca: il post_name resta quello, e titolo e URL divergono.
 *
 * Si rifanno adesso perché adesso è gratis. Dopo la messa online un cambio di slug rompe i
 * link già indicizzati e condivisi, e si paga con dei redirect da mantenere per sempre.
 *
 * Lo SKU entra nello slug come coda: due prodotti possono legittimamente avere lo stesso nome
 * breve (stesso cartello, materiali diversi), e senza un discriminante WordPress accoderebbe
 * -2, -3, -4 — numeri che non dicono niente a nessuno.
 *
 * Idempotente. Non tocca i prodotti il cui slug è già quello giusto.
 *
 *   wp eval-file tools/import-listini/fix_slug.php dry-run
 *   wp eval-file tools/import-listini/fix_slug.php
 */

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$dry = ( ( $args[0] ?? '' ) === 'dry-run' );

// Ordine fisso: a decidere chi si tiene lo slug pulito fra due omonimi dev'essere sempre lo
// stesso prodotto. Con l'ordine di default il vincitore cambia a ogni passata e gli slug
// ballano — e uno slug che balla, dopo la messa online, è un link che si rompe.
$ids = get_posts( [
	'post_type'      => 'product',
	'post_status'    => [ 'publish', 'draft' ],
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'orderby'        => 'ID',
	'order'          => 'ASC',
] );

WP_CLI::log( sprintf( 'Prodotti: %d %s', count( $ids ), $dry ? '(DRY RUN)' : '' ) );

$rifatti = $gia_ok = $collisioni = 0;
$visti   = [];
$esempi  = [];
$lavoro  = [];   // pid → slug definitivo, scritto in fondo in due passate

foreach ( $ids as $pid ) {
	$post = get_post( $pid );
	if ( ! $post ) { continue; }

	$sku  = get_post_meta( $pid, '_sku', true );
	$base = sanitize_title( $post->post_title );

	if ( ! $base ) { continue; }

	// Nome uguale ma prodotto diverso: si disambigua con lo SKU. Meglio "…-ms-ver-73" di "…-2".
	//
	// Quando lo SKU manca, il suffisso lo mette WordPress da sé (-2, -3, -4) — e allora lo
	// script non converge: alla passata dopo rilegge "…-4", non combacia col target "…", e
	// riscrive all'infinito. Quindi il discriminante lo scegliamo noi sempre, ripiegando
	// sull'ID del post: brutto ma stabile, e comunque solo per i doppioni.
	$slug = $base;
	if ( isset( $visti[ $base ] ) ) {
		$coda = $sku ? sanitize_title( $sku ) : (string) $pid;
		$slug = $base . '-' . $coda;
		$collisioni++;
	}
	$visti[ $base ] = true;

	if ( $post->post_name === $slug ) { $gia_ok++; continue; }

	if ( count( $esempi ) < 5 ) {
		$esempi[] = sprintf( '%s  →  %s', mb_substr( $post->post_name, 0, 44 ) . '…', $slug );
	}

	$lavoro[ $pid ] = $slug;
	$rifatti++;
}

// Due passate, e non è pignoleria.
//
// Lo slug che un prodotto deve prendere è spesso ancora in mano al prodotto che sta per
// liberarlo. Scrivendo di fila, WordPress trova la collisione e accoda un "-2" — così lo
// script non converge: alla passata dopo ripesca lo slug rimasto libero, ne riscrive un'altra
// manciata, e ci vogliono tre giri per fermarsi. Con gli URL già online, tre giri sono tre
// ondate di redirect.
//
// Quindi prima si parcheggiano tutti gli interessati su uno slug provvisorio — che nessun
// altro può volere, perché contiene l'ID — e solo dopo si scrive quello definitivo, su un
// campo ormai libero.
if ( ! $dry && $lavoro ) {
	foreach ( $lavoro as $pid => $_ ) {
		wp_update_post( [ 'ID' => $pid, 'post_name' => 'ms-tmp-' . $pid ] );
	}
	foreach ( $lavoro as $pid => $slug ) {
		wp_update_post( [ 'ID' => $pid, 'post_name' => $slug ] );
	}
}

if ( ! $dry ) { wc_delete_product_transients(); }

foreach ( $esempi as $e ) { WP_CLI::log( '  ' . $e ); }

WP_CLI::success( sprintf(
	'%d slug rigenerati · %d già a posto · %d disambiguati con lo SKU %s',
	$rifatti, $gia_ok, $collisioni, $dry ? '(DRY RUN)' : ''
) );
