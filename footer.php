<?php
/**
 * Sidfot.
 *
 * @package Plata
 */
?>

<footer class="site-footer">
	<div class="footer-content">
		<?php if ( has_nav_menu( 'secondary' ) ) : ?>
			<?php
			wp_nav_menu(
				array(
					'theme_location'       => 'secondary',
					'container'            => 'nav',
					'container_class'      => 'footer-nav',
					'container_aria_label' => __( 'Sidfotsmeny', 'plata' ),
					'menu_class'           => 'footer-nav__list',
					'depth'                => 1,
					'fallback_cb'          => false,
				)
			);
			?>
		<?php endif; ?>

		<p class="footer-copy">&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
