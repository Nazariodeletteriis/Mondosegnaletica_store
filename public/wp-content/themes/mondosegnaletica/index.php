<?php
/**
 * Fallback template — quasi mai usato, WordPress lo richiede però.
 */
get_header();
?>

<main id="main" class="site-main" role="main">
	<div class="container" style="padding-block:var(--space-24)">
		<?php if ( have_posts() ) :
			while ( have_posts() ) :
				the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile;
			the_posts_pagination();
		else : ?>
			<p style="color:var(--color-text-muted)">Nessun contenuto trovato.</p>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
