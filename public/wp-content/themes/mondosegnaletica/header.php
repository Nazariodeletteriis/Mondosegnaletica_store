<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="sr-only" href="#main-content"><?php esc_html_e( 'Vai al contenuto principale', 'mondosegnaletica' ); ?></a>

	<?php get_template_part( 'template-parts/header/hud-strip' ); ?>

	<header class="site-header" role="banner">
		<div class="site-header__inner">

				<!-- Logo -->
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ' — Homepage' ); ?>">
					<?php
					$logo_id = get_theme_mod( 'custom_logo' );
					if ( $logo_id ) :
						echo wp_get_attachment_image( $logo_id, 'full', false, [ 'class' => 'site-logo__svg', 'loading' => 'eager' ] );
					else :
					?>
					<!-- Logo placeholder SVG — sostituire con logo reale -->
					<svg class="site-logo__svg" viewBox="0 0 120 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<rect width="120" height="40" fill="transparent"/>
						<path d="M8 32V8l10 14L28 8v24M36 8v24M36 8h12c3.3 0 6 2.7 6 6s-2.7 6-6 6H36M36 20h12" stroke="#F5F4F0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<text x="64" y="28" font-family="'Anton', sans-serif" font-size="20" fill="#FFCC00" letter-spacing="-0.5">MS</text>
					</svg>
					<?php endif; ?>
				</a>

				<!-- Nav Principale -->
				<?php get_template_part( 'template-parts/header/nav-primary' ); ?>

				<!-- Header Actions -->
				<div class="header-actions">
					<a href="<?php echo esc_url( home_url( '/contatti' ) ); ?>" class="btn btn--primary btn--header-cta">
						Richiedi Preventivo
					</a>

					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="header-cart" aria-label="<?php esc_attr_e( 'Carrello', 'mondosegnaletica' ); ?>">
						<span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
						<span class="header-cart__count" aria-live="polite" data-count="<?php echo esc_attr( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ); ?>">
							<?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : ''; ?>
						</span>
					</a>
					<?php endif; ?>
				</div>

		</div>
	</header>

	<div id="main-content" class="site-content">
