<?php get_header(); ?>

	<?php if ( have_posts() ) : ?>

		<?php while ( have_posts() ) : the_post(); ?>
			<?php the_post_thumbnail(); ?>
			<?php the_title(); ?>
			<?php the_excerpt(); ?>
		<?php endwhile; ?>

	<?php endif; ?>

<?php get_footer(); ?>