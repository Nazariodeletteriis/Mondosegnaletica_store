<?php
/**
 * Theme setup — menus, image sizes, features.
 */

declare(strict_types=1);

if ( ! function_exists( 'ms_setup' ) ) :
	function ms_setup(): void {
		load_theme_textdomain( 'mondosegnaletica', get_template_directory() . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'html5', [
			'comment-list',
			'comment-form',
			'search-form',
			'gallery',
			'caption',
			'style',
			'script',
		] );

		// Immagini prodotto — dimensioni custom per il tema
		add_image_size( 'ms-product-card',  600,  600, true );
		add_image_size( 'ms-product-thumb', 160,  160, true );
		add_image_size( 'ms-product-main',  900,  900, false );
		add_image_size( 'ms-category-card', 800,  600, true );
		add_image_size( 'ms-hero',         1920, 1080, true );

		register_nav_menus( [
			'primary' => __( 'Menu Principale', 'mondosegnaletica' ),
			'footer'  => __( 'Menu Footer', 'mondosegnaletica' ),
		] );
	}
endif;

add_action( 'after_setup_theme', 'ms_setup' );
