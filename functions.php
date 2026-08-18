<?php
/**
 * Tema-funktioner.
 *
 * @package Plata
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PLATA_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/theme-settings.php';
require_once get_template_directory() . '/inc/toc.php';
require_once get_template_directory() . '/inc/tables.php';
require_once get_template_directory() . '/inc/links.php';
require_once get_template_directory() . '/inc/social.php';

/**
 * Tema-setup.
 */
function plata_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	register_nav_menus(
		array(
			'primary'   => __( 'Primär meny', 'plata' ),
			'secondary' => __( 'Sekundär meny (sidfot)', 'plata' ),
		)
	);

	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/dist/css/style.min.css' );
}
add_action( 'after_setup_theme', 'plata_setup' );

/**
 * Ladda CSS och JS.
 */
function plata_enqueue_assets() {
	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();

	$css_file = $theme_dir . '/assets/dist/css/style.min.css';
	$js_file  = $theme_dir . '/assets/dist/js/main.min.js';

	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'plata-style',
			$theme_uri . '/assets/dist/css/style.min.css',
			array(),
			(string) filemtime( $css_file )
		);
	}

	if ( file_exists( $js_file ) ) {
		wp_enqueue_script(
			'plata-script',
			$theme_uri . '/assets/dist/js/main.min.js',
			array(),
			(string) filemtime( $js_file ),
			true
		);

		wp_localize_script(
			'plata-script',
			'plataStrings',
			array(
				/* translators: %s: namnet på menyposten. */
				'submenuToggle'   => __( 'Visa undermeny för %s', 'plata' ),
				'scrollableTable' => __( 'Tabell, går att scrolla i sidled', 'plata' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'plata_enqueue_assets' );

/**
 * Tillåt uppladdning av SVG-filer.
 *
 * @param array<string, string> $mimes Tillåtna mime-typer.
 * @return array<string, string>
 */
function plata_allow_svg_uploads( $mimes ) {
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';

	return $mimes;
}
add_filter( 'upload_mimes', 'plata_allow_svg_uploads' );

/**
 * Se till att WordPress accepterar SVG vid filkontroll.
 *
 * @param array|false $data     Filtypsdata.
 * @param string      $file     Temporär filsökväg.
 * @param string      $filename Filnamn.
 * @param string[]    $mimes    Tillåtna mime-typer.
 * @return array|false
 */
function plata_fix_svg_mime_type( $data, $file, $filename, $mimes ) {
	$ext = pathinfo( $filename, PATHINFO_EXTENSION );

	if ( ! in_array( strtolower( (string) $ext ), array( 'svg', 'svgz' ), true ) ) {
		return $data;
	}

	if ( is_array( $data ) ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	} else {
		$data = array(
			'ext'             => 'svg',
			'type'            => 'image/svg+xml',
			'proper_filename' => false,
		);
	}

	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'plata_fix_svg_mime_type', 10, 4 );

add_filter( 'show_admin_bar', '__return_false' );

/**
 * Redigeringslänk för det som visas just nu.
 *
 * @return string Tom sträng om inget kan redigeras.
 */
function plata_get_current_edit_url() {
	if ( is_singular() ) {
		return (string) get_edit_post_link( get_queried_object_id() );
	}

	if ( is_home() && ! is_front_page() ) {
		return (string) get_edit_post_link( (int) get_option( 'page_for_posts' ) );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		return $term instanceof WP_Term ? (string) get_edit_term_link( $term ) : '';
	}

	if ( is_author() ) {
		return (string) get_edit_user_link( get_queried_object_id() );
	}

	if ( is_post_type_archive() ) {
		$post_type = get_queried_object();

		if ( $post_type instanceof WP_Post_Type && current_user_can( $post_type->cap->edit_posts ) ) {
			return admin_url( 'edit.php?post_type=' . $post_type->name );
		}
	}

	return '';
}

/**
 * Verktygsfält för inloggade, som ersätter WordPress adminbar.
 */
function plata_render_admin_toolbar() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$edit_url = plata_get_current_edit_url();
	?>
	<div class="plata-toolbar">
		<?php if ( $edit_url ) : ?>
			<a
				class="plata-toolbar__button"
				href="<?php echo esc_url( $edit_url ); ?>"
				aria-label="<?php esc_attr_e( 'Redigera den här sidan', 'plata' ); ?>"
				title="<?php esc_attr_e( 'Redigera den här sidan', 'plata' ); ?>"
			>
				<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path d="M12 20h9" />
					<path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
					<path d="m15 5 3 3" />
				</svg>
			</a>
		<?php endif; ?>

		<a
			class="plata-toolbar__button"
			href="<?php echo esc_url( admin_url() ); ?>"
			aria-label="<?php esc_attr_e( 'Gå till adminpanelen', 'plata' ); ?>"
			title="<?php esc_attr_e( 'Gå till adminpanelen', 'plata' ); ?>"
		>
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				<path d="m3 10 9-7 9 7" />
				<path d="M5 9v11h14V9" />
				<path d="M9 20v-6h6v6" />
			</svg>
		</a>
	</div>
	<?php
}
add_action( 'wp_footer', 'plata_render_admin_toolbar' );
