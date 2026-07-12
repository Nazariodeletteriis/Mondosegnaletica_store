<?php
/**
 * Import del catalogo dai listini fornitore.
 *
 * Uso (wp eval-file accetta solo argomenti POSIZIONALI, non flag):
 *   wp eval-file tools/import-listini/import.php            → import completo
 *   wp eval-file tools/import-listini/import.php dry-run    → nessuna scrittura
 *   wp eval-file tools/import-listini/import.php '' 50      → importa solo i primi 50
 *
 * Legge tools/import-listini/out/prodotti.json (prodotto dal normalize.py) e crea
 * i prodotti WooCommerce. I prodotti con più di una variante diventano `variable`,
 * gli altri `simple`. I prodotti senza alcun prezzo (il listino stampa "CHIEDERE
 * PREVENTIVO", o la cella è vuota alla fonte) vengono creati SENZA prezzo: WooCommerce
 * li rende non acquistabili e il tema mostra la CTA preventivo al posto del carrello.
 */


if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$argv_ms = $args ?? [];
$dry_run = ( $argv_ms[0] ?? '' ) === 'dry-run';
$limit   = (int) ( $argv_ms[1] ?? 0 );

$json = dirname( __FILE__ ) . '/out/prodotti.json';
if ( ! file_exists( $json ) ) {
	// eval-file gira con cwd = docroot: risali al repo
	$json = '/var/www/html/tools/import-listini/out/prodotti.json';
}
if ( ! file_exists( $json ) ) {
	WP_CLI::error( "prodotti.json non trovato in $json" );
}

$prodotti = json_decode( (string) file_get_contents( $json ), true );
if ( ! is_array( $prodotti ) ) {
	WP_CLI::error( 'prodotti.json non valido' );
}

WP_CLI::log( sprintf( 'Prodotti da importare: %d %s', count( $prodotti ), $dry_run ? '(DRY RUN)' : '' ) );

// ─── attributi: nome leggibile → taxonomy ────────────────────────────────────
$ATTR_TAX = [
	'Dimensione'         => 'pa_dimensione',
	'Materiale'          => 'pa_materiale',
	'Classe rifrangenza' => 'pa_classe-rifrangenza',
	'Fissaggio'          => 'pa_fissaggio',
	'Versione'           => 'pa_versione',
	'Variante'           => 'pa_formato',
];

/** Restituisce lo slug del termine, creandolo se non esiste. */
function ms_term( string $taxonomy, string $name ): ?string {
	static $cache = [];
	$key = $taxonomy . '|' . $name;
	if ( isset( $cache[ $key ] ) ) { return $cache[ $key ]; }

	$term = get_term_by( 'name', $name, $taxonomy );
	if ( ! $term ) {
		$res = wp_insert_term( $name, $taxonomy );
		if ( is_wp_error( $res ) ) {
			// race o nome duplicato: ripesca
			$term = get_term_by( 'name', $name, $taxonomy );
			if ( ! $term ) { return $cache[ $key ] = null; }
		} else {
			$term = get_term( $res['term_id'], $taxonomy );
		}
	}
	return $cache[ $key ] = $term->slug;
}

/** term_id della categoria prodotto (devono già esistere). */
function ms_cat_id( string $name ): ?int {
	static $cache = [];
	if ( isset( $cache[ $name ] ) ) { return $cache[ $name ]; }
	$t = get_term_by( 'name', $name, 'product_cat' );
	return $cache[ $name ] = $t ? (int) $t->term_id : null;
}

function ms_descrizione( array $p ): string {
	$out   = [];
	$out[] = sprintf(
		'<p>Articolo a listino <strong>%s</strong>, sezione <em>%s</em> (pag. %s del listino %s).</p>',
		esc_html( $p['sku'] ),
		esc_html( $p['sezione'] ?? '' ),
		esc_html( (string) ( $p['pagina'] ?? '' ) ),
		esc_html( $p['listino'] ?? '' )
	);
	if ( ! empty( $p['figura'] ) ) {
		$out[] = sprintf( '<p>Codice figura Codice della Strada: <strong>FIG. %s</strong>.</p>', esc_html( (string) $p['figura'] ) );
	}
	$note = array_values( array_unique( array_filter( (array) ( $p['desc_note'] ?? [] ) ) ) );
	if ( $note ) {
		$out[] = '<ul>' . implode( '', array_map( fn( $n ) => '<li>' . esc_html( (string) $n ) . '</li>', $note ) ) . '</ul>';
	}
	$out[] = '<p>Prezzi IVA esclusa. Prodotto conforme al Codice della Strada.</p>';
	return implode( "\n", $out );
}

$creati = $variazioni = $senza_prezzo = $errori = 0;
$i = 0;

foreach ( $prodotti as $sku => $p ) {
	if ( $limit && $i >= $limit ) { break; }
	$i++;

	$varianti = array_values( array_filter(
		$p['varianti'] ?? [],
		fn( $v ) => isset( $v['attrs'] ) && is_array( $v['attrs'] )
	) );
	$con_prezzo = array_values( array_filter( $varianti, fn( $v ) => null !== ( $v['euro'] ?? null ) ) );

	if ( $dry_run ) {
		WP_CLI::log( sprintf(
			'  %-22s %-46s %s  var=%d prezzo=%d',
			$sku, mb_substr( $p['nome'], 0, 44 ), $p['cat'], count( $varianti ), count( $con_prezzo )
		) );
		continue;
	}

	// idempotenza: se lo SKU esiste già, salta
	if ( wc_get_product_id_by_sku( (string) $sku ) ) { continue; }

	try {
		$is_variable = count( $con_prezzo ) > 1;
		$product = $is_variable ? new WC_Product_Variable() : new WC_Product_Simple();

		$product->set_name( (string) $p['nome'] );
		$product->set_sku( (string) $sku );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_description( ms_descrizione( $p ) );
		$product->set_short_description( sprintf(
			'%s — omologato Codice della Strada. Spedizione 24/48h da Lucca.',
			$p['sezione'] ?? ''
		) );

		$cat_id = ms_cat_id( (string) $p['cat'] );
		if ( $cat_id ) { $product->set_category_ids( [ $cat_id ] ); }

		// ─── attributi ──────────────────────────────────────────────────────
		$valori = []; // taxonomy → [slug]
		foreach ( $varianti as $v ) {
			foreach ( $v['attrs'] as $nome_attr => $valore ) {
				$tax = $ATTR_TAX[ $nome_attr ] ?? null;
				if ( ! $tax || '' === trim( (string) $valore ) ) { continue; }
				$slug = ms_term( $tax, (string) $valore );
				if ( $slug ) { $valori[ $tax ][ $slug ] = true; }
			}
		}

		$attributes = [];
		foreach ( $valori as $tax => $slugs ) {
			$a = new WC_Product_Attribute();
			$a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
			$a->set_name( $tax );
			$a->set_options( array_keys( $slugs ) );
			$a->set_visible( true );
			$a->set_variation( $is_variable );
			$attributes[] = $a;
		}
		$product->set_attributes( $attributes );

		// prodotto semplice: prezzo dall'unica variante, se c'è
		if ( ! $is_variable && $con_prezzo ) {
			$product->set_regular_price( (string) $con_prezzo[0]['euro'] );
		}

		$product->set_manage_stock( false );
		$product->set_stock_status( 'instock' );

		$pid = $product->save();
		if ( ! $pid ) { throw new RuntimeException( 'save() ha restituito 0' ); }
		$creati++;

		if ( ! $con_prezzo ) { $senza_prezzo++; }

		// ─── variazioni ─────────────────────────────────────────────────────
		if ( $is_variable ) {
			foreach ( $con_prezzo as $n => $v ) {
				$var = new WC_Product_Variation();
				$var->set_parent_id( $pid );
				$var->set_status( 'publish' );

				$attr_var = [];
				foreach ( $v['attrs'] as $nome_attr => $valore ) {
					$tax = $ATTR_TAX[ $nome_attr ] ?? null;
					if ( ! $tax ) { continue; }
					$slug = ms_term( $tax, (string) $valore );
					if ( $slug ) { $attr_var[ $tax ] = $slug; }
				}
				if ( ! $attr_var ) { continue; }

				$var->set_attributes( $attr_var );
				$var->set_regular_price( (string) $v['euro'] );
				$var->set_manage_stock( false );
				$var->set_stock_status( 'instock' );
				$var->set_sku( $sku . '-' . str_pad( (string) ( $n + 1 ), 2, '0', STR_PAD_LEFT ) );
				$var->save();
				$variazioni++;
			}
			// ricalcola range prezzi e cache lookup
			WC_Product_Variable::sync( $pid );
		}
	} catch ( Throwable $e ) {
		$errori++;
		WP_CLI::warning( sprintf( '%s: %s', $sku, $e->getMessage() ) );
	}

	if ( 0 === $creati % 50 && $creati ) {
		WP_CLI::log( sprintf( '  … %d prodotti, %d variazioni', $creati, $variazioni ) );
	}
}

if ( $dry_run ) {
	WP_CLI::success( 'Dry run completato.' );
	return;
}

wc_delete_product_transients();
WP_CLI::success( sprintf(
	'Creati %d prodotti · %d variazioni · %d senza prezzo (preventivo) · %d errori',
	$creati, $variazioni, $senza_prezzo, $errori
) );
