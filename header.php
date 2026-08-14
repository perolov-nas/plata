<?php
/**
 * Sidhuvud.
 *
 * @package Plata
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>document.documentElement.className = document.documentElement.className.replace( /\bno-js\b/, 'js' );</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="header-content">
		<a class="site-branding" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php
			$logo_id = function_exists( 'plata_get_logo_id' ) ? plata_get_logo_id() : 0;

			if ( $logo_id ) {
				echo wp_get_attachment_image(
					$logo_id,
					'full',
					false,
					array(
						'class'   => 'site-logo',
						'alt'     => '',
						'loading' => 'eager',
					)
				);
			}
			?>
			<!-- <span class="site-title"><?php bloginfo( 'name' ); ?></span> -->
		</a>
		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<button
				class="site-nav-toggle"
				type="button"
				aria-expanded="false"
				aria-controls="site-nav"
			>
				<svg class="site-nav-toggle__open" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path d="M4 7h16" />
					<path d="M4 12h16" />
					<path d="M4 17h16" />
				</svg>
				<svg class="site-nav-toggle__close" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path d="m6 6 12 12" />
					<path d="m18 6-12 12" />
				</svg>
				<span class="screen-reader-text"><?php esc_html_e( 'Meny', 'plata' ); ?></span>
			</button>

			<?php
			wp_nav_menu(
				array(
					'theme_location'       => 'primary',
					'container'            => 'nav',
					'container_class'      => 'site-nav',
					'container_id'         => 'site-nav',
					'container_aria_label' => __( 'Huvudmeny', 'plata' ),
					'menu_class'           => 'site-nav__list',
					'depth'                => 2,
					'fallback_cb'          => false,
				)
			);
			?>
		<?php endif; ?>
		<?php if ( shortcode_exists( 'vader' ) ) : ?>
			<div class="weather">
				<?php echo do_shortcode( '[vader plats="Tällberg"]' ); ?>
			</div>
		<?php endif; ?>
	</div>
</header>
