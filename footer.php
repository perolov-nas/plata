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

		<?php
		if ( function_exists( 'plata_render_footer_social' ) ) {
			plata_render_footer_social();
		}
		?>

		<div class="footer-meta">
			<?php
			$logo_id = function_exists( 'plata_get_logo_id' ) ? plata_get_logo_id() : 0;

			if ( $logo_id ) :
				?>
				<a class="footer-branding" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php
					echo wp_get_attachment_image(
						$logo_id,
						'full',
						false,
						array(
							'class'    => 'site-logo site-logo--footer',
							'alt'      => '',
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					);
					?>
				</a>
			<?php endif; ?>

			<p class="footer-copy">&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
