<?php
/**
 * Azzera il catalogo importato: prodotti pubblicati, variazioni e termini attributo.
 *
 * I termini vanno azzerati insieme ai prodotti: un import sbagliato può aver creato
 * termini duplicati (name uguale allo slug, slug con suffisso "-2") che restano
 * appesi alla tassonomia e continuano a rompere i menu varianti anche dopo un
 * re-import corretto.
 *
 * NON tocca i prodotti in bozza: sono il catalogo precedente, archiviato.
 *
 *   wp eval-file tools/import-listini/wipe.php
 */

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

global $wpdb;

$vars  = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type='product_variation'" );
$prods = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type='product' AND post_status='publish'" );

WP_CLI::log( sprintf( 'cancello %d prodotti e %d variazioni', count( $prods ), count( $vars ) ) );

foreach ( array_merge( $vars, $prods ) as $id ) {
	wp_delete_post( (int) $id, true );
}

$taxonomies = [ 'pa_dimensione', 'pa_materiale', 'pa_classe-rifrangenza',
                'pa_fissaggio', 'pa_versione', 'pa_formato', 'pa_tipologia', 'pa_taglia' ];

$n = 0;
foreach ( $taxonomies as $tax ) {
	if ( ! taxonomy_exists( $tax ) ) { continue; }
	$terms = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => false, 'fields' => 'ids' ] );
	if ( is_wp_error( $terms ) ) { continue; }
	foreach ( $terms as $tid ) {
		wp_delete_term( (int) $tid, $tax );
		$n++;
	}
}
WP_CLI::log( sprintf( 'cancellati %d termini attributo', $n ) );

// meta rimasti orfani dopo la cancellazione dei post
$wpdb->query( "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL" );

wc_delete_product_transients();
WP_CLI::success( 'catalogo azzerato' );
