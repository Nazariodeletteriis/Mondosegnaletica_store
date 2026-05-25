<?php
/**
 * Pagina risultati ricerca.
 */
get_header();
?>

<main id="main" class="site-main" role="main">
	<div class="container" style="padding-block:var(--space-16)">
		<div class="section-header" style="margin-bottom:var(--space-8)">
			<span class="label-section">Ricerca</span>
			<h1 class="section-title">
				<?php
				printf(
					esc_html__( 'Risultati per: %s', 'mondosegnaletica' ),
					'<span style="color:var(--color-accent)">' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</div>

		<?php if ( have_posts() ) : ?>
		<div class="products-grid">
			<?php while ( have_posts() ) :
				the_post();
				if ( get_post_type() === 'product' ) :
					$product = wc_get_product( get_the_ID() );
					if ( $product instanceof WC_Product ) :
						ms_get_template_part( 'template-parts/product/card', [ 'product' => $product ] );
					endif;
				else : ?>
				<article style="padding:var(--space-6);background:var(--color-surface);border:var(--border-hairline);border-radius:var(--radius);">
					<h2 style="font-family:var(--font-display);font-size:var(--text-xl);text-transform:uppercase;">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<p style="margin-top:var(--space-3);"><?php the_excerpt(); ?></p>
				</article>
				<?php endif;
			endwhile;
			the_posts_pagination();
		else : ?>
			<p style="color:var(--color-text-muted)">Nessun risultato trovato per "<?php echo esc_html( get_search_query() ); ?>".</p>
		<?php endif; ?>
		</div>
	</div>
</main>

<?php get_footer(); ?>
