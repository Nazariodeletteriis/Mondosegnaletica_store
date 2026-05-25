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

	$manifest = ms_get_vite_manifest();

	if ( $manifest ) {
		// --- PRODUZIONE: file hashati da Vite build ---
		$main_css = $manifest['src/css/main.css']['file'] ?? null;
		$main_js  = $manifest['src/js/main.js']['file']  ?? null;

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
	}

	// WooCommerce JS customizzazioni — solo pagine WC
	if ( class_exists( 'WooCommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() ) ) {
		wp_enqueue_script(
			'mondosegnaletica-woo',
			$theme_uri . '/assets/src/js/woo-custom.js',
			[ 'jquery', 'wc-add-to-cart' ],
			$version,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);
	}
}

add_action( 'wp_enqueue_scripts', 'ms_enqueue_assets' );

/**
 * Aggiunge type="module" allo script main per ES modules.
 */
function ms_add_module_type( string $tag, string $handle, string $src ): string {
	if ( $handle === 'mondosegnaletica-main' ) {
		return str_replace( ' src=', ' type="module" src=', $tag );
	}
	return $tag;
}
