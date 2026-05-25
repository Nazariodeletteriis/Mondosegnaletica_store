<?php
/**
 * Nav principale desktop + mobile toggle.
 */

$menu_items = [
	[ 'label' => 'Catalogo',  'url' => get_permalink( wc_get_page_id( 'shop' ) ) ?: home_url( '/negozio' ) ],
	[ 'label' => 'Soluzioni', 'url' => home_url( '/soluzioni' ) ],
	[ 'label' => 'Cantieri',  'url' => home_url( '/cantieri' )  ],
	[ 'label' => 'Azienda',   'url' => home_url( '/azienda' )   ],
	[ 'label' => 'Contatti',  'url' => home_url( '/contatti' )  ],
];
?>

<button
	class="nav-toggle"
	aria-label="<?php esc_attr_e( 'Apri menu di navigazione', 'mondosegnaletica' ); ?>"
	aria-expanded="false"
	aria-controls="nav-primary"
>
	<span class="nav-toggle__bar"></span>
	<span class="nav-toggle__bar"></span>
	<span class="nav-toggle__bar"></span>
</button>

<nav class="nav-primary" id="nav-primary" role="navigation" aria-label="<?php esc_attr_e( 'Menu principale', 'mondosegnaletica' ); ?>">
	<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<?php wp_nav_menu( [
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'nav-primary__list',
			'link_before'    => '',
			'link_after'     => '',
			'walker'         => null,
		] ); ?>
	<?php else : ?>
		<ul class="nav-primary__list">
			<?php foreach ( $menu_items as $item ) :
				$is_active = ( rtrim( $item['url'], '/' ) === rtrim( get_permalink() ?: '', '/' ) );
			?>
			<li class="nav-primary__item">
				<a
					href="<?php echo esc_url( $item['url'] ); ?>"
					class="nav-primary__link<?php echo $is_active ? ' nav-primary__link--active' : ''; ?>"
					<?php if ( $is_active ) : ?>aria-current="page"<?php endif; ?>
				>
					<?php echo esc_html( $item['label'] ); ?>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</nav>
