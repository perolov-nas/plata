<?php
/**
 * Huvudmall.
 *
 * @package Plata
 */

get_header();
?>

<main id="main" class="site-main">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<article <?php post_class(); ?>>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Inget innehåll hittades.', 'plata' ); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer();
