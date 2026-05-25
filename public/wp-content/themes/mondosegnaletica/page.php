<?php
/**
 * Template pagine statiche WordPress.
 */
get_header();
?>

<main id="main" class="site-main" role="main">
	<div class="container" style="padding-block:var(--space-24)">
		<?php while ( have_posts() ) :
			the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header style="margin-bottom:var(--space-8)">
					<h1 class="section-title"><?php the_title(); ?></h1>
				</header>
				<div class="tab-panel__content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
</main>

<?php get_footer(); ?>
