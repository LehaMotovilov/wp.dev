<?php
/**
 * Template Name: Home
 */
?>
<?php get_header(); ?>

	<?php if ( have_posts() ) : ?>

		<?php while ( have_posts() ) : the_post(); ?>
			<?php the_title(); ?>
		<?php endwhile; ?>

	<?php endif; ?>

<?php get_footer(); ?>