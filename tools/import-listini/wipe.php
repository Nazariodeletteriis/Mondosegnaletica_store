<?php
if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }
global $wpdb;
// Le variazioni e i prodotti PUBBLICATI vengono tutti dall'import interrotto:
// il catalogo precedente è in bozza e non va toccato.
$vars = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type='product_variation'" );
$prods = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type='product' AND post_status='publish'" );
WP_CLI::log( sprintf( 'da cancellare: %d prodotti, %d variazioni', count($prods), count($vars) ) );
foreach ( array_merge( $vars, $prods ) as $id ) { wp_delete_post( (int) $id, true ); }
$wpdb->query( "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE p.ID IS NULL" );
WP_CLI::success( 'pulito' );
