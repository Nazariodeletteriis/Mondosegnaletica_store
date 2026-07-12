<?php
/**
 * Enqueue scripts & styles.
 *
 * In dev: Vite dev server (hot reload).
 * In prod: Vite build manifest → hashed asset paths.
 */

declare(strict_types=1);

/**
 * Restituisce il path del manifest Vite o null se non esiste.
 */
function ms_get_vite_manifest(): ?array {
	$manifest_path = get_template_directory() . '/assets/dist/.vite/manifest.json';
	if ( ! file_exists( $manifest_path ) ) {
		return null;
	}
	return json_decode( file_get_contents( $manifest_path ), true ) ?: null;
}

function ms_enqueue_assets(): void {
	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();
	$version   = wp_get_theme()->get( 'Version' );

	// Material Symbols — caricato prima dei font per evitare FOUT sulle icone
	wp_enqueue_style(
		'mondosegnaletica-icons',
		'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300,0,0&display=block',
		[],
		null
	);

	// Google Fonts — caricato via WP per caching ottimale
	wp_enqueue_style(
		'mondosegnaletica-fonts',
		'https://fonts.googleapis.com/css2?family=Anton:wght@400&family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;1,400&family=JetBrains+Mono:wght@400;500&display=swap',
		[],
		null
	);

	$manifest = ms_get_vite_manifest();

	if ( $manifest ) {
		// --- PRODUZIONE: file hashati da Vite build ---
		$main_css = $manifest['assets/src/css/main.css']['file'] ?? null;
		$main_js  = $manifest['assets/src/js/main.js']['file']  ?? null;
		$hero_js  = $manifest['assets/src/js/hero.js']['file']  ?? null;

		if ( $main_css ) {
			wp_enqueue_style(
				'mondosegnaletica-main',
				$theme_uri . '/assets/dist/' . $main_css,
				[],
				null
			);
		}

		if ( $main_js ) {
			wp_enqueue_script(
				'mondosegnaletica-main',
				$theme_uri . '/assets/dist/' . $main_js,
				[],
				null,
				[ 'strategy' => 'defer', 'in_footer' => true ]
			);
		}

		// Hero JS — solo homepage, solo se presente nel manifest
		if ( $hero_js && is_front_page() ) {
			wp_enqueue_script(
				'ms-hero',
				$theme_uri . '/assets/dist/' . $hero_js,
				[],
				null,
				[ 'strategy' => 'defer', 'in_footer' => true ]
			);
		}
	} else {
		// --- DEV: file sorgente diretti (no Vite running) ---
		// Enqueue CSS sorgenti concatenati tramite @import
		wp_enqueue_style(
			'mondosegnaletica-tokens',
			$theme_uri . '/assets/src/css/tokens.css',
			[],
			$version
		);

		wp_enqueue_style(
			'mondosegnaletica-base',
			$theme_uri . '/assets/src/css/base.css',
			[ 'mondosegnaletica-tokens' ],
			$version
		);

		wp_enqueue_style(
			'mondosegnaletica-header',
			$theme_uri . '/assets/src/css/components/header.css',
			[ 'mondosegnaletica-base' ],
			$version
		);

		wp_enqueue_style(
			'mondosegnaletica-footer',
			$theme_uri . '/assets/src/css/components/footer.css',
			[ 'mondosegnaletica-base' ],
			$version
		);

		wp_enqueue_style(
			'mondosegnaletica-product-card',
			$theme_uri . '/assets/src/css/components/product-card.css',
			[ 'mondosegnaletica-base' ],
			$version
		);

		// CSS pagina-specifici
		if ( is_front_page() ) {
			wp_enqueue_style(
				'mondosegnaletica-home',
				$theme_uri . '/assets/src/css/pages/home.css',
				[ 'mondosegnaletica-base' ],
				$version
			);
		}

		if ( is_shop() || is_product_category() || is_product_tag() ) {
			wp_enqueue_style(
				'mondosegnaletica-archive',
				$theme_uri . '/assets/src/css/pages/archive.css',
				[ 'mondosegnaletica-base' ],
				$version
			);
		}

		if ( is_product() ) {
			wp_enqueue_style(
				'mondosegnaletica-single-product',
				$theme_uri . '/assets/src/css/pages/single-product.css',
				[ 'mondosegnaletica-base' ],
				$version
			);
		}

		// JS — type module per ES imports
		wp_enqueue_script(
			'mondosegnaletica-main',
			$theme_uri . '/assets/src/js/main.js',
			[],
			$version,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);
		// Aggiungi type="module" al tag script
		add_filter( 'script_loader_tag', 'ms_add_module_type', 10, 3 );

		// Hero JS — solo homepage, file statico in dev
		if ( is_front_page() ) {
			wp_enqueue_script(
				'ms-hero',
				$theme_uri . '/assets/src/js/hero.js',
				[],
				$version,
				[ 'strategy' => 'defer', 'in_footer' => true ]
			);
		}
	}

	// WooCommerce JS customizzazioni — solo pagine WC.
	// In produzione passa dal manifest Vite come gli altri entry, così è minificato e hashato.
	if ( class_exists( 'WooCommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() ) ) {
		$woo_js = $manifest['assets/src/js/woo-custom.js']['file'] ?? null;

		wp_enqueue_script(
			'mondosegnaletica-woo',
			$woo_js
				? $theme_uri . '/assets/dist/' . $woo_js
				: $theme_uri . '/assets/src/js/woo-custom.js',
			[ 'jquery', 'wc-add-to-cart' ],
			$woo_js ? null : $version,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);
	}

	// Script variazioni WC — WC 10.x non lo inietta automaticamente sui temi custom
	if ( class_exists( 'WooCommerce' ) && is_product() ) {
		$_pdp_product = wc_get_product( get_queried_object_id() );
		if ( $_pdp_product && $_pdp_product->is_type( 'variable' ) ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
		}
	}
}

add_action( 'wp_enqueue_scripts', 'ms_enqueue_assets' );

/**
 * Rimuove i fogli di stile WordPress core che interferiscono con il layout.
 * wp-block-library aggiunge max-width e margin: auto sui contenitori principali.
 * global-styles inietta il CSS dei blocchi Gutenberg che strozza il layout full-bleed.
 */
add_action( 'wp_enqueue_scripts', function(): void {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_deregister_style( 'classic-theme-styles' );

	// WooCommerce CSS non serve sulla homepage — tutto il layout è custom
	if ( is_front_page() ) {
		wp_dequeue_style( 'woocommerce-general' );
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-smallscreen' );
		wp_dequeue_style( 'wc-blocks-style' );
	}
}, 100 );

/**
 * Aggiunge type="module" allo script main per ES modules.
 */
function ms_add_module_type( string $tag, string $handle, string $src ): string {
	if ( $handle === 'mondosegnaletica-main' ) {
		return str_replace( ' src=', ' type="module" src=', $tag );
	}
	return $tag;
}
