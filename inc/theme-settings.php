<?php
/**
 * Tema-inställningar (admin).
 *
 * @package Plata
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Standardfärger.
 *
 * @return array<string, array{label: string, default: string, group: string}>
 */
function plata_get_color_fields() {
	return array(
		'text'         => array(
			'label'   => __( 'Textfärg', 'plata' ),
			'default' => '#1a1a1a',
			'group'   => 'text',
		),
		'text_muted'   => array(
			'label'   => __( 'Sekundär text', 'plata' ),
			'default' => '#666666',
			'group'   => 'text',
		),
		'heading'      => array(
			'label'   => __( 'Rubrikfärg', 'plata' ),
			'default' => '#1a1a1a',
			'group'   => 'text',
		),
		'background'   => array(
			'label'   => __( 'Bakgrund', 'plata' ),
			'default' => '#ffffff',
			'group'   => 'surface',
		),
		'surface'      => array(
			'label'   => __( 'Ytfärg (t.ex. kort/sektioner)', 'plata' ),
			'default' => '#f5f5f5',
			'group'   => 'surface',
		),
		'border'       => array(
			'label'   => __( 'Kantlinjer', 'plata' ),
			'default' => '#e5e5e5',
			'group'   => 'surface',
		),
		'header_bg'    => array(
			'label'   => __( 'Bakgrund header', 'plata' ),
			'default' => '#ffffff',
			'group'   => 'surface',
		),
		'footer_bg'    => array(
			'label'   => __( 'Bakgrund footer', 'plata' ),
			'default' => '#f5f5f5',
			'group'   => 'surface',
		),
		'link'         => array(
			'label'   => __( 'Länkfärg', 'plata' ),
			'default' => '#0b57d0',
			'group'   => 'link',
		),
		'link_hover'   => array(
			'label'   => __( 'Länk hover', 'plata' ),
			'default' => '#0842a0',
			'group'   => 'link',
		),
		'link_focus'   => array(
			'label'   => __( 'Länk focus', 'plata' ),
			'default' => '#0842a0',
			'group'   => 'link',
		),
		'focus'        => array(
			'label'   => __( 'Fokusring', 'plata' ),
			'default' => '#0b57d0',
			'group'   => 'focus',
		),
		'button_bg'    => array(
			'label'   => __( 'Knapp bakgrund', 'plata' ),
			'default' => '#1a1a1a',
			'group'   => 'button',
		),
		'button_text'  => array(
			'label'   => __( 'Knapp text', 'plata' ),
			'default' => '#ffffff',
			'group'   => 'button',
		),
		'button_hover' => array(
			'label'   => __( 'Knapp hover', 'plata' ),
			'default' => '#333333',
			'group'   => 'button',
		),
		'button_focus' => array(
			'label'   => __( 'Knapp focus', 'plata' ),
			'default' => '#0b57d0',
			'group'   => 'button',
		),
	);
}

/**
 * Grupper för admin-UI.
 *
 * @return array<string, string>
 */
function plata_get_color_groups() {
	return array(
		'text'    => __( 'Text', 'plata' ),
		'surface' => __( 'Ytor', 'plata' ),
		'link'    => __( 'Länkar', 'plata' ),
		'focus'   => __( 'Fokus', 'plata' ),
		'button'  => __( 'Knappar', 'plata' ),
	);
}

/**
 * Hämta sparade färger med defaults som fallback.
 *
 * @return array<string, string>
 */
function plata_get_colors() {
	$saved   = get_option( 'plata_colors', array() );
	$colors  = array();
	$fields  = plata_get_color_fields();

	foreach ( $fields as $key => $field ) {
		$value = isset( $saved[ $key ] ) ? $saved[ $key ] : $field['default'];
		$colors[ $key ] = sanitize_hex_color( $value ) ? sanitize_hex_color( $value ) : $field['default'];
	}

	return $colors;
}

/**
 * Tillgängliga typsnitt: system + Font Library + theme.json.
 *
 * @return array<string, array{slug: string, name: string, fontFamily: string, source: string, id: int, fontFace?: array}>
 */
function plata_get_available_fonts() {
	$fonts = array(
		'system' => array(
			'slug'       => 'system',
			'name'       => __( 'Systemstandard', 'plata' ),
			'fontFamily' => 'system-ui, sans-serif',
			'source'     => 'system',
			'id'         => 0,
		),
	);

	$library_fonts = get_posts(
		array(
			'post_type'              => 'wp_font_family',
			'post_status'            => 'publish',
			'numberposts'            => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $library_fonts as $post ) {
		$settings = json_decode( $post->post_content, true );
		$slug     = $post->post_name ? $post->post_name : sanitize_title( $post->post_title );

		$fonts[ $slug ] = array(
			'slug'       => $slug,
			'name'       => $post->post_title,
			'fontFamily' => ! empty( $settings['fontFamily'] ) ? $settings['fontFamily'] : $post->post_title,
			'source'     => 'library',
			'id'         => (int) $post->ID,
		);
	}

	if ( function_exists( 'wp_get_global_settings' ) ) {
		$font_families = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );

		if ( is_array( $font_families ) ) {
			foreach ( $font_families as $list ) {
				if ( ! is_array( $list ) ) {
					continue;
				}

				foreach ( $list as $definition ) {
					if ( empty( $definition['slug'] ) ) {
						continue;
					}

					$slug = sanitize_title( $definition['slug'] );

					if ( isset( $fonts[ $slug ] ) ) {
						continue;
					}

					$fonts[ $slug ] = array(
						'slug'       => $slug,
						'name'       => ! empty( $definition['name'] ) ? $definition['name'] : $slug,
						'fontFamily' => ! empty( $definition['fontFamily'] ) ? $definition['fontFamily'] : $slug,
						'source'     => 'theme',
						'id'         => 0,
						'fontFace'   => ! empty( $definition['fontFace'] ) ? $definition['fontFace'] : array(),
					);
				}
			}
		}
	}

	return $fonts;
}

/**
 * Typografi-fält (typsnitt).
 *
 * @return array<string, array{label: string, default: string}>
 */
function plata_get_typography_fields() {
	return array(
		'body'    => array(
			'label'   => __( 'Brödtext', 'plata' ),
			'default' => 'system',
		),
		'heading' => array(
			'label'   => __( 'Rubriker', 'plata' ),
			'default' => 'system',
		),
	);
}

/**
 * Standardstorlek för rotens font-size (px).
 *
 * @return int
 */
function plata_get_default_base_font_size() {
	return 16;
}

/**
 * Håll basstorleken inom ett rimligt spann.
 *
 * @param mixed $size     Storlek i pixlar.
 * @param int   $fallback Värde att använda när indata saknas.
 * @return int
 */
function plata_clamp_base_font_size( $size, $fallback ) {
	$size = (int) $size;

	if ( $size <= 0 ) {
		$size = $fallback;
	}

	return max( 12, min( 24, $size ) );
}

/**
 * Hämta sparad typografi.
 *
 * @return array{body: string, heading: string, base_size: int}
 */
function plata_get_typography() {
	$saved      = get_option( 'plata_typography', array() );
	$fields     = plata_get_typography_fields();
	$fonts      = plata_get_available_fonts();
	$out        = array();
	$default_px = plata_get_default_base_font_size();

	foreach ( $fields as $key => $field ) {
		$slug        = isset( $saved[ $key ] ) ? sanitize_title( $saved[ $key ] ) : $field['default'];
		$out[ $key ] = isset( $fonts[ $slug ] ) ? $slug : $field['default'];
	}

	$out['base_size'] = plata_clamp_base_font_size(
		isset( $saved['base_size'] ) ? $saved['base_size'] : $default_px,
		$default_px
	);

	return $out;
}

/**
 * Standardinställningar för layout.
 *
 * @return array<string, int>
 */
function plata_get_default_layout() {
	return array(
		'content_width' => 960,
		'wide_width'    => 1440,
	);
}

/**
 * Håll en bredd inom det spann webbläsaren och layouten klarar av.
 *
 * @param mixed $width    Bredd i pixlar.
 * @param int   $fallback Värde att använda när indata saknas.
 * @return int
 */
function plata_clamp_layout_width( $width, $fallback ) {
	$width = (int) $width;

	if ( $width <= 0 ) {
		$width = $fallback;
	}

	return max( 320, min( 3840, $width ) );
}

/**
 * Hämta sparade layoutinställningar.
 *
 * @return array<string, int>
 */
function plata_get_layout() {
	$defaults = plata_get_default_layout();
	$saved    = get_option( 'plata_layout', array() );
	$layout   = wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );

	$layout['wide_width']    = plata_clamp_layout_width( $layout['wide_width'], $defaults['wide_width'] );
	$layout['content_width'] = plata_clamp_layout_width( $layout['content_width'], $defaults['content_width'] );

	return $layout;
}

/**
 * Standardinställningar för varumärke.
 *
 * @return array<string, int>
 */
function plata_get_default_branding() {
	return array(
		'logo_id'    => 0,
		'favicon_id' => 0,
	);
}

/**
 * Kontrollera om en bilaga är en giltig bild (inkl. SVG).
 *
 * @param int $attachment_id Bilaga-ID.
 * @return bool
 */
function plata_is_image_attachment( $attachment_id ) {
	$attachment_id = absint( $attachment_id );

	if ( ! $attachment_id ) {
		return false;
	}

	if ( wp_attachment_is_image( $attachment_id ) ) {
		return true;
	}

	$mime = get_post_mime_type( $attachment_id );

	return is_string( $mime ) && str_starts_with( $mime, 'image/' );
}

/**
 * Hämta sparade varumärkesinställningar.
 *
 * @return array<string, int>
 */
function plata_get_branding() {
	$defaults = plata_get_default_branding();
	$saved    = get_option( 'plata_branding', array() );
	$branding = wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );

	foreach ( array( 'logo_id', 'favicon_id' ) as $key ) {
		$id = absint( $branding[ $key ] );
		$branding[ $key ] = plata_is_image_attachment( $id ) ? $id : 0;
	}

	return $branding;
}

/**
 * Hämta logotypens bilaga-ID.
 *
 * @return int
 */
function plata_get_logo_id() {
	$branding = plata_get_branding();
	return (int) $branding['logo_id'];
}

/**
 * Hämta faviconens bilaga-ID.
 *
 * @return int
 */
function plata_get_favicon_id() {
	$branding = plata_get_branding();
	return (int) $branding['favicon_id'];
}

/**
 * Registrera meny och settings.
 */
function plata_register_settings_page() {
	add_theme_page(
		__( 'Tema-inställningar', 'plata' ),
		__( 'Tema-inställningar', 'plata' ),
		'edit_theme_options',
		'plata-settings',
		'plata_render_settings_page'
	);

	register_setting(
		'plata_settings',
		'plata_colors',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'plata_sanitize_colors',
			'default'           => array(),
		)
	);

	register_setting(
		'plata_settings',
		'plata_typography',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'plata_sanitize_typography',
			'default'           => array(),
		)
	);

	register_setting(
		'plata_settings',
		'plata_layout',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'plata_sanitize_layout',
			'default'           => plata_get_default_layout(),
		)
	);

	register_setting(
		'plata_settings',
		'plata_branding',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'plata_sanitize_branding',
			'default'           => plata_get_default_branding(),
		)
	);

	register_setting(
		'plata_settings',
		'plata_social_heading',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'plata_sanitize_social_heading',
			'default'           => '',
		)
	);

	register_setting(
		'plata_settings',
		'plata_social_links',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'plata_sanitize_social_links',
			'default'           => array(),
		)
	);
}
add_action( 'admin_menu', 'plata_register_settings_page' );

/**
 * Sanera färger.
 *
 * @param mixed $input Indata från formulär.
 * @return array<string, string>
 */
function plata_sanitize_colors( $input ) {
	$clean  = array();
	$fields = plata_get_color_fields();

	if ( ! is_array( $input ) ) {
		return $clean;
	}

	foreach ( $fields as $key => $field ) {
		if ( empty( $input[ $key ] ) ) {
			$clean[ $key ] = $field['default'];
			continue;
		}

		$color = sanitize_hex_color( $input[ $key ] );
		$clean[ $key ] = $color ? $color : $field['default'];
	}

	return $clean;
}

/**
 * Sanera typografi.
 *
 * @param mixed $input Indata från formulär.
 * @return array{body?: string, heading?: string, base_size: int}
 */
function plata_sanitize_typography( $input ) {
	$clean      = array();
	$fields     = plata_get_typography_fields();
	$fonts      = plata_get_available_fonts();
	$default_px = plata_get_default_base_font_size();
	$input      = is_array( $input ) ? $input : array();

	foreach ( $fields as $key => $field ) {
		$slug          = isset( $input[ $key ] ) ? sanitize_title( $input[ $key ] ) : $field['default'];
		$clean[ $key ] = isset( $fonts[ $slug ] ) ? $slug : $field['default'];
	}

	$clean['base_size'] = plata_clamp_base_font_size(
		isset( $input['base_size'] ) ? $input['base_size'] : $default_px,
		$default_px
	);

	return $clean;
}

/**
 * Sanera layoutinställningar.
 *
 * @param mixed $input Indata från formulär.
 * @return array<string, int>
 */
function plata_sanitize_layout( $input ) {
	$defaults      = plata_get_default_layout();
	$input         = is_array( $input ) ? $input : array();
	$wide_width    = isset( $input['wide_width'] ) ? $input['wide_width'] : $defaults['wide_width'];
	$content_width = isset( $input['content_width'] ) ? $input['content_width'] : $defaults['content_width'];

	return array(
		'content_width' => plata_clamp_layout_width( $content_width, $defaults['content_width'] ),
		'wide_width'    => plata_clamp_layout_width( $wide_width, $defaults['wide_width'] ),
	);
}

/**
 * Sanera varumärkesinställningar.
 *
 * @param mixed $input Indata från formulär.
 * @return array<string, int>
 */
function plata_sanitize_branding( $input ) {
	$input = is_array( $input ) ? $input : array();
	$clean = plata_get_default_branding();

	foreach ( array( 'logo_id', 'favicon_id' ) as $key ) {
		$id = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : 0;
		$clean[ $key ] = plata_is_image_attachment( $id ) ? $id : 0;
	}

	return $clean;
}

/**
 * Standardfärger.
 *
 * @return array<string, string>
 */
function plata_get_default_colors() {
	$defaults = array();

	foreach ( plata_get_color_fields() as $key => $field ) {
		$defaults[ $key ] = $field['default'];
	}

	return $defaults;
}

/**
 * Standardtypografi.
 *
 * @return array{body: string, heading: string, base_size: int}
 */
function plata_get_default_typography() {
	$defaults = array();

	foreach ( plata_get_typography_fields() as $key => $field ) {
		$defaults[ $key ] = $field['default'];
	}

	$defaults['base_size'] = plata_get_default_base_font_size();

	return $defaults;
}

/**
 * Hantera återställning av tema-inställningar.
 */
function plata_handle_reset_settings() {
	if ( ! isset( $_POST['plata_reset_settings'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	check_admin_referer( 'plata_reset_settings' );

	update_option( 'plata_colors', plata_get_default_colors() );
	update_option( 'plata_typography', plata_get_default_typography() );
	update_option( 'plata_layout', plata_get_default_layout() );
	update_option( 'plata_branding', plata_get_default_branding() );
	delete_option( 'plata_spacing' );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'        => 'plata-settings',
				'plata-reset' => '1',
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'plata_handle_reset_settings' );

/**
 * Visa admin-meddelande efter återställning.
 */
function plata_reset_settings_notice() {
	if ( ! isset( $_GET['page'], $_GET['plata-reset'] ) || 'plata-settings' !== $_GET['page'] ) {
		return;
	}

	if ( '1' !== $_GET['plata-reset'] ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Tema-inställningarna har återställts till standard.', 'plata' ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'plata_reset_settings_notice' );

/**
 * Markera att användaren ska tas till tema-inställningar efter aktivering.
 */
function plata_on_switch_theme() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	set_transient( 'plata_activation_redirect', '1', 60 );
}
add_action( 'after_switch_theme', 'plata_on_switch_theme' );

/**
 * Redirecta till tema-inställningar första gången temat aktiveras.
 */
function plata_activation_redirect() {
	if ( ! get_transient( 'plata_activation_redirect' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	if ( wp_doing_ajax() || is_network_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	delete_transient( 'plata_activation_redirect' );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'           => 'plata-settings',
				'plata-welcome'  => '1',
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'plata_activation_redirect' );

/**
 * Ladda WordPress färgväljare på settings-sidan.
 *
 * @param string $hook Aktuell admin-sida.
 */
function plata_admin_assets( $hook ) {
	if ( 'appearance_page_plata-settings' !== $hook ) {
		return;
	}

	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();
	$css_file  = $theme_dir . '/assets/dist/css/admin-settings.min.css';
	$js_file   = $theme_dir . '/assets/dist/js/admin-settings.min.js';

	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'plata-admin-settings',
			$theme_uri . '/assets/dist/css/admin-settings.min.css',
			array(),
			(string) filemtime( $css_file )
		);
	}

	wp_enqueue_media();

	if ( file_exists( $js_file ) ) {
		wp_enqueue_script(
			'plata-admin-settings',
			$theme_uri . '/assets/dist/js/admin-settings.min.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			(string) filemtime( $js_file ),
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'plata_admin_assets' );

/**
 * Ge settings-sidan en egen body-klass så bakgrunden kan täcka hela ytan.
 *
 * @param string $classes Befintliga body-klasser.
 * @return string
 */
function plata_admin_body_class( $classes ) {
	$screen = get_current_screen();

	if ( $screen && 'appearance_page_plata-settings' === $screen->id ) {
		$classes .= ' plata-settings-page';
	}

	return $classes;
}
add_filter( 'admin_body_class', 'plata_admin_body_class' );

/**
 * Filnamn och storlek för en bilaga, om filen finns.
 *
 * @param int $attachment_id Bilaga-ID.
 * @return array{name: string, size: string, preview: string}
 */
function plata_get_attachment_file_meta( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	$meta          = array(
		'name'    => '',
		'size'    => '',
		'preview' => '',
	);

	if ( ! $attachment_id ) {
		return $meta;
	}

	$path = get_attached_file( $attachment_id );
	$url  = wp_get_attachment_image_url( $attachment_id, 'medium' );

	$meta['preview'] = $url ? $url : (string) wp_get_attachment_url( $attachment_id );
	$meta['name']    = $path ? basename( $path ) : get_the_title( $attachment_id );

	if ( $path && is_readable( $path ) ) {
		$formatted = size_format( (int) filesize( $path ), 0 );
		$meta['size'] = $formatted ? (string) $formatted : '';
	}

	return $meta;
}

/**
 * Rendera mediaväljare för en bilaga.
 *
 * @param string $key   Fältnyckel i plata_branding.
 * @param string $label Fältetikett.
 * @param int    $id    Bilaga-ID.
 * @param string $help  Hjälptext.
 */
function plata_render_media_field( $key, $label, $id, $help = '' ) {
	$id   = absint( $id );
	$file = plata_get_attachment_file_meta( $id );
	?>
	<div class="plata-field">
		<label class="plata-field__label" for="plata_branding_<?php echo esc_attr( $key ); ?>">
			<?php echo esc_html( $label ); ?>
		</label>
		<?php if ( $help ) : ?>
			<p class="plata-field__help"><?php echo esc_html( $help ); ?></p>
		<?php endif; ?>
		<div class="plata-upload plata-media-field" data-title="<?php echo esc_attr( $label ); ?>">
			<input
				type="hidden"
				id="plata_branding_<?php echo esc_attr( $key ); ?>"
				name="plata_branding[<?php echo esc_attr( $key ); ?>]"
				value="<?php echo esc_attr( (string) $id ); ?>"
				class="plata-media-id"
			/>
			<button type="button" class="plata-upload__dropzone plata-media-select"<?php echo $id ? ' hidden' : ''; ?>>
				<svg class="plata-upload__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path d="M12 16V6" />
					<path d="m8 10 4-4 4 4" />
					<path d="M5 18h14" />
				</svg>
				<strong><?php esc_html_e( 'Klicka eller släpp en bild här', 'plata' ); ?></strong>
				<span><?php esc_html_e( 'PNG, JPG eller SVG.', 'plata' ); ?></span>
			</button>
			<div class="plata-upload__file"<?php echo $id ? '' : ' hidden'; ?>>
				<?php if ( $file['preview'] ) : ?>
					<img class="plata-upload__thumb" src="<?php echo esc_url( $file['preview'] ); ?>" alt="" />
				<?php endif; ?>
				<div class="plata-upload__meta">
					<span class="plata-upload__name"><?php echo esc_html( $file['name'] ); ?></span>
					<span class="plata-upload__size"><?php echo esc_html( $file['size'] ); ?></span>
				</div>
				<button type="button" class="plata-upload__remove plata-media-remove">
					<?php esc_html_e( 'Ta bort', 'plata' ); ?>
				</button>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Rendera ett färg-token.
 *
 * @param string $key     Fältnyckel.
 * @param string $label   Etikett.
 * @param string $value   Hex-färg.
 * @param string $default Standardfärg.
 */
function plata_render_color_field( $key, $label, $value, $default ) {
	?>
	<div class="plata-token">
		<div class="plata-token__swatch" style="background-color: <?php echo esc_attr( $value ); ?>;">
			<input
				type="color"
				class="plata-token__picker"
				value="<?php echo esc_attr( $value ); ?>"
				aria-label="<?php echo esc_attr( $label ); ?>"
			/>
		</div>
		<div class="plata-token__body">
			<label class="plata-token__label" for="plata_color_<?php echo esc_attr( $key ); ?>">
				<?php echo esc_html( $label ); ?>
			</label>
			<input
				type="text"
				id="plata_color_<?php echo esc_attr( $key ); ?>"
				name="plata_colors[<?php echo esc_attr( $key ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				class="plata-color-field"
				data-default-color="<?php echo esc_attr( $default ); ?>"
				spellcheck="false"
				autocomplete="off"
			/>
		</div>
	</div>
	<?php
}

/**
 * Rendera en grupp färg-tokens.
 *
 * @param string                $title  Gruppens rubrik.
 * @param array<string, mixed>  $fields Fält att visa.
 * @param array<string, string> $colors Sparade färger.
 */
function plata_render_color_card( $title, $fields, $colors ) {
	if ( empty( $fields ) ) {
		return;
	}
	?>
	<div class="plata-tokens-group">
		<h3 class="plata-tokens-group__title"><?php echo esc_html( $title ); ?></h3>
		<div class="plata-tokens">
			<?php foreach ( $fields as $key => $field ) : ?>
				<?php plata_render_color_field( $key, $field['label'], $colors[ $key ], $field['default'] ); ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Rendera admin-sidan.
 */
function plata_render_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$colors            = plata_get_colors();
	$fields            = plata_get_color_fields();
	$groups            = plata_get_color_groups();
	$typography        = plata_get_typography();
	$typography_fields = plata_get_typography_fields();
	$fonts             = plata_get_available_fonts();
	$fonts_url         = admin_url( 'font-library.php' );
	$layout            = plata_get_layout();
	$branding          = plata_get_branding();
	$heading_family    = isset( $fonts[ $typography['heading'] ] ) ? $fonts[ $typography['heading'] ]['fontFamily'] : 'system-ui, sans-serif';
	$body_family       = isset( $fonts[ $typography['body'] ] ) ? $fonts[ $typography['body'] ]['fontFamily'] : 'system-ui, sans-serif';
	$theme             = wp_get_theme();
	$show_welcome      = isset( $_GET['plata-welcome'] ) && '1' === $_GET['plata-welcome'];
	$nav               = array(
		'identitet' => __( 'Identitet', 'plata' ),
		'farger'    => __( 'Färger', 'plata' ),
		'typografi' => __( 'Typografi', 'plata' ),
		'layout'    => __( 'Layout', 'plata' ),
		'socialt'   => __( 'Socialt', 'plata' ),
	);
	?>
	<div class="wrap plata-settings">
		<header class="plata-top">
			<div>
				<p class="plata-top__kicker"><?php echo esc_html( $theme->get( 'Name' ) ); ?></p>
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<p class="plata-top__lede">
					<?php esc_html_e( 'Ett litet designsystem för hela sajten. Ändra här, se det överallt.', 'plata' ); ?>
				</p>
			</div>
			<p class="plata-top__status"><?php esc_html_e( 'Aktivt', 'plata' ); ?></p>
		</header>

		<?php if ( $show_welcome ) : ?>
			<aside class="plata-welcome" role="status">
				<p class="plata-welcome__kicker"><?php esc_html_e( 'Välkommen', 'plata' ); ?></p>
				<h2><?php esc_html_e( 'Temat är aktivt. Gör det till ert.', 'plata' ); ?></h2>
				<p>
					<?php esc_html_e( 'Börja med logotyp, färger och typsnitt. Ändringarna gäller hela webbplatsen.', 'plata' ); ?>
				</p>
			</aside>
		<?php endif; ?>

		<div class="plata-shell">
			<nav class="plata-nav" aria-label="<?php esc_attr_e( 'Sektioner', 'plata' ); ?>">
				<?php foreach ( $nav as $id => $label ) : ?>
					<a class="plata-nav__link<?php echo 'identitet' === $id ? ' is-active' : ''; ?>" href="#plata-<?php echo esc_attr( $id ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form id="plata-settings-form" class="plata-main" method="post" action="options.php">
				<?php settings_fields( 'plata_settings' ); ?>

				<section class="plata-panel" id="plata-identitet">
					<header class="plata-panel__head">
						<p class="plata-panel__index">01</p>
						<div>
							<h2><?php esc_html_e( 'Identitet', 'plata' ); ?></h2>
							<p><?php esc_html_e( 'Logotyp och webbplatsikon. De två saker folk känner igen först.', 'plata' ); ?></p>
						</div>
					</header>
					<div class="plata-panel__grid">
						<?php
						plata_render_media_field(
							'logo_id',
							__( 'Logotyp', 'plata' ),
							$branding['logo_id'],
							__( 'Visas i sidhuvud och sidfot. PNG, JPG eller SVG.', 'plata' )
						);
						plata_render_media_field(
							'favicon_id',
							__( 'Favicon', 'plata' ),
							$branding['favicon_id'],
							__( 'Kvadratisk bild, gärna 512 × 512 px.', 'plata' )
						);
						?>
					</div>
				</section>

				<section class="plata-panel" id="plata-farger">
					<header class="plata-panel__head">
						<p class="plata-panel__index">02</p>
						<div>
							<h2><?php esc_html_e( 'Färger', 'plata' ); ?></h2>
							<p><?php esc_html_e( 'Klicka på en yta för att byta. Hex-värdet kan också skrivas in för hand.', 'plata' ); ?></p>
						</div>
					</header>
					<?php
					foreach ( $groups as $group_key => $group_label ) {
						$group_fields = array();

						foreach ( $fields as $key => $field ) {
							if ( $field['group'] === $group_key ) {
								$group_fields[ $key ] = $field;
							}
						}

						plata_render_color_card( $group_label, $group_fields, $colors );
					}
					?>
				</section>

				<section class="plata-panel" id="plata-typografi">
					<header class="plata-panel__head">
						<p class="plata-panel__index">03</p>
						<div>
							<h2><?php esc_html_e( 'Typografi', 'plata' ); ?></h2>
							<p>
								<?php
								printf(
									/* translators: %s: länk till Font Library */
									esc_html__( 'Typsnitt från %s, eller systemstandard.', 'plata' ),
									'<a href="' . esc_url( $fonts_url ) . '">' . esc_html__( 'Utseende → Typsnitt', 'plata' ) . '</a>'
								);
								?>
							</p>
						</div>
					</header>
					<div class="plata-type">
						<div class="plata-type__controls">
							<?php foreach ( $typography_fields as $key => $field ) : ?>
								<div class="plata-field">
									<label class="plata-field__label" for="plata_font_<?php echo esc_attr( $key ); ?>">
										<?php echo esc_html( $field['label'] ); ?>
									</label>
									<select
										id="plata_font_<?php echo esc_attr( $key ); ?>"
										name="plata_typography[<?php echo esc_attr( $key ); ?>]"
										class="plata-font-select"
										data-role="<?php echo esc_attr( $key ); ?>"
									>
										<?php foreach ( $fonts as $font ) : ?>
											<option
												value="<?php echo esc_attr( $font['slug'] ); ?>"
												<?php selected( $typography[ $key ], $font['slug'] ); ?>
												data-font-family="<?php echo esc_attr( $font['fontFamily'] ); ?>"
												style="font-family: <?php echo esc_attr( $font['fontFamily'] ); ?>;"
											>
												<?php echo esc_html( $font['name'] ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							<?php endforeach; ?>
							<div class="plata-field">
								<label class="plata-field__label" for="plata_base_font_size">
									<?php esc_html_e( 'Basstorlek', 'plata' ); ?>
								</label>
								<div class="plata-inline">
									<input
										type="number"
										id="plata_base_font_size"
										name="plata_typography[base_size]"
										value="<?php echo esc_attr( (string) $typography['base_size'] ); ?>"
										min="12"
										max="24"
										step="1"
									/>
									<span class="plata-field__suffix">px</span>
								</div>
							</div>
						</div>
						<div class="plata-specimen">
							<p class="plata-specimen__label"><?php esc_html_e( 'Prov', 'plata' ); ?></p>
							<p class="plata-specimen__heading" style="font-family: <?php echo esc_attr( $heading_family ); ?>;">
								<?php esc_html_e( 'Rubriker sätter tonen', 'plata' ); ?>
							</p>
							<p class="plata-specimen__body" style="font-family: <?php echo esc_attr( $body_family ); ?>;">
								<?php esc_html_e( 'Snabba bruna räven hoppar över den lata hunden. Brödtexten ska bära längre stycken utan att trötta ögat.', 'plata' ); ?>
							</p>
							<p class="plata-specimen__meta">
								<span class="plata-specimen__heading-name"><?php echo esc_html( isset( $fonts[ $typography['heading'] ] ) ? $fonts[ $typography['heading'] ]['name'] : __( 'Systemstandard', 'plata' ) ); ?></span>
								<span aria-hidden="true">·</span>
								<span class="plata-specimen__body-name"><?php echo esc_html( isset( $fonts[ $typography['body'] ] ) ? $fonts[ $typography['body'] ]['name'] : __( 'Systemstandard', 'plata' ) ); ?></span>
							</p>
						</div>
					</div>
				</section>

				<section class="plata-panel" id="plata-layout">
					<header class="plata-panel__head">
						<p class="plata-panel__index">04</p>
						<div>
							<h2><?php esc_html_e( 'Layout', 'plata' ); ?></h2>
							<p><?php esc_html_e( 'Hur brett innehållet får bli. Fullbreddsblock tar alltid hela fönstret.', 'plata' ); ?></p>
						</div>
					</header>
					<div class="plata-panel__grid">
						<div class="plata-field">
							<label class="plata-field__label" for="plata_content_width">
								<?php esc_html_e( 'Innehållsbredd', 'plata' ); ?>
							</label>
							<div class="plata-inline">
								<input
									type="number"
									id="plata_content_width"
									name="plata_layout[content_width]"
									value="<?php echo esc_attr( (string) $layout['content_width'] ); ?>"
									min="320"
									max="3840"
									step="1"
								/>
								<span class="plata-field__suffix">px</span>
							</div>
						</div>
						<div class="plata-field">
							<label class="plata-field__label" for="plata_wide_width">
								<?php esc_html_e( 'Bredd för bred bredd', 'plata' ); ?>
							</label>
							<div class="plata-inline">
								<input
									type="number"
									id="plata_wide_width"
									name="plata_layout[wide_width]"
									value="<?php echo esc_attr( (string) $layout['wide_width'] ); ?>"
									min="320"
									max="3840"
									step="1"
								/>
								<span class="plata-field__suffix">px</span>
							</div>
						</div>
					</div>
				</section>

				<section class="plata-panel" id="plata-socialt">
					<header class="plata-panel__head">
						<p class="plata-panel__index">05</p>
						<div>
							<h2><?php esc_html_e( 'Socialt', 'plata' ); ?></h2>
							<p><?php esc_html_e( 'Ikoner i sidfoten. Lägg till, ta bort och dra för att ändra ordning.', 'plata' ); ?></p>
						</div>
					</header>
					<div class="plata-field">
						<label class="plata-field__label" for="plata_social_heading">
							<?php esc_html_e( 'Rubrik', 'plata' ); ?>
						</label>
						<input
							type="text"
							id="plata_social_heading"
							name="plata_social_heading"
							value="<?php echo esc_attr( plata_get_social_heading() ); ?>"
						/>
					</div>
					<?php plata_render_social_links_field(); ?>
				</section>
			</form>
		</div>

		<div class="plata-dock">
			<form
				method="post"
				action=""
				class="plata-reset-form"
				onsubmit="return confirm('<?php echo esc_js( __( 'Återställ alla tema-inställningar till standard?', 'plata' ) ); ?>');"
			>
				<?php wp_nonce_field( 'plata_reset_settings' ); ?>
				<?php submit_button( __( 'Återställ', 'plata' ), 'secondary', 'plata_reset_settings', false ); ?>
			</form>
			<?php
			submit_button(
				__( 'Spara', 'plata' ),
				'primary',
				'submit',
				false,
				array( 'form' => 'plata-settings-form' )
			);
			?>
		</div>
	</div>
	<?php
}

/**
 * Konvertera font-face-settings (camelCase) till CSS-props (kebab-case).
 *
 * @param array<string, mixed> $settings Font face-inställningar.
 * @return array<string, mixed>
 */
function plata_normalize_font_face( array $settings ) {
	$map = array(
		'ascentOverride'         => 'ascent-override',
		'descentOverride'        => 'descent-override',
		'fontDisplay'            => 'font-display',
		'fontFamily'             => 'font-family',
		'fontStretch'            => 'font-stretch',
		'fontStyle'              => 'font-style',
		'fontWeight'             => 'font-weight',
		'fontVariant'            => 'font-variant',
		'fontFeatureSettings'    => 'font-feature-settings',
		'fontVariationSettings'  => 'font-variation-settings',
		'lineGapOverride'        => 'line-gap-override',
		'sizeAdjust'             => 'size-adjust',
		'src'                    => 'src',
		'unicodeRange'           => 'unicode-range',
	);

	$normalized = array();

	foreach ( $settings as $key => $value ) {
		$prop = isset( $map[ $key ] ) ? $map[ $key ] : $key;
		$normalized[ $prop ] = $value;
	}

	return $normalized;
}

/**
 * Hämta @font-face-data för valda typsnitt.
 *
 * @return array<int, array<int, array<string, mixed>>>
 */
function plata_get_selected_font_faces() {
	$typography = plata_get_typography();
	$fonts      = plata_get_available_fonts();
	$faces      = array();
	$seen       = array();

	foreach ( array( 'body', 'heading' ) as $key ) {
		$slug = isset( $typography[ $key ] ) ? $typography[ $key ] : '';

		if ( empty( $fonts[ $slug ] ) || isset( $seen[ $slug ] ) ) {
			continue;
		}

		$seen[ $slug ] = true;
		$font          = $fonts[ $slug ];

		if ( 'library' === $font['source'] && ! empty( $font['id'] ) ) {
			$face_posts = get_children(
				array(
					'post_parent' => $font['id'],
					'post_type'   => 'wp_font_face',
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);

			$family_faces = array();

			foreach ( $face_posts as $face_post ) {
				$settings = json_decode( $face_post->post_content, true );

				if ( ! is_array( $settings ) || empty( $settings['src'] ) ) {
					continue;
				}

				$normalized = plata_normalize_font_face( $settings );
				$normalized['font-family'] = WP_Font_Utils::sanitize_font_family(
					! empty( $font['fontFamily'] ) ? $font['fontFamily'] : $font['name']
				);

				$family_faces[] = $normalized;
			}

			if ( ! empty( $family_faces ) ) {
				$faces[] = $family_faces;
			}

			continue;
		}

		if ( 'theme' === $font['source'] && ! empty( $font['fontFace'] ) && is_array( $font['fontFace'] ) ) {
			$family_name  = WP_Font_Utils::sanitize_font_family( $font['fontFamily'] );
			$family_faces = array();

			foreach ( $font['fontFace'] as $face ) {
				if ( ! is_array( $face ) ) {
					continue;
				}

				$normalized = plata_normalize_font_face( $face );
				$normalized['font-family'] = $family_name;

				if ( ! empty( $normalized['src'] ) && is_array( $normalized['src'] ) ) {
					$normalized['src'] = array_map(
						static function ( $src ) {
							if ( is_string( $src ) && str_starts_with( $src, 'file:./' ) ) {
								return get_theme_file_uri( str_replace( 'file:./', '', $src ) );
							}
							return $src;
						},
						$normalized['src']
					);
				} elseif ( ! empty( $normalized['src'] ) && is_string( $normalized['src'] ) && str_starts_with( $normalized['src'], 'file:./' ) ) {
					$normalized['src'] = get_theme_file_uri( str_replace( 'file:./', '', $normalized['src'] ) );
				}

				$family_faces[] = $normalized;
			}

			if ( ! empty( $family_faces ) ) {
				$faces[] = $family_faces;
			}
		}
	}

	return $faces;
}

/**
 * Skriv ut @font-face för valda typsnitt.
 */
function plata_print_selected_font_faces() {
	$faces = plata_get_selected_font_faces();

	if ( empty( $faces ) ) {
		return;
	}

	wp_print_font_faces( $faces );
}
add_action( 'wp_head', 'plata_print_selected_font_faces', 5 );

/**
 * Bygg CSS-variabler från sparade inställningar.
 *
 * @return string
 */
function plata_get_css_variables() {
	$colors = plata_get_colors();
	$layout = plata_get_layout();
	$map    = array(
		'text'         => '--color-text',
		'text_muted'   => '--color-text-muted',
		'heading'      => '--color-heading',
		'background'   => '--color-background',
		'surface'      => '--color-surface',
		'border'       => '--color-border',
		'header_bg'    => '--color-header-bg',
		'footer_bg'    => '--color-footer-bg',
		'link'         => '--color-link',
		'link_hover'   => '--color-link-hover',
		'link_focus'   => '--color-link-focus',
		'focus'        => '--color-focus',
		'button_bg'    => '--color-button-bg',
		'button_text'  => '--color-button-text',
		'button_hover' => '--color-button-hover',
		'button_focus' => '--color-button-focus',
	);

	$rules = array();
	foreach ( $map as $key => $var ) {
		if ( isset( $colors[ $key ] ) ) {
			$rules[] = $var . ': ' . $colors[ $key ] . ';';
		}
	}

	$typography = plata_get_typography();
	$fonts      = plata_get_available_fonts();

	$body_family    = isset( $fonts[ $typography['body'] ] ) ? $fonts[ $typography['body'] ]['fontFamily'] : 'system-ui, sans-serif';
	$heading_family = isset( $fonts[ $typography['heading'] ] ) ? $fonts[ $typography['heading'] ]['fontFamily'] : $body_family;

	$rules[] = '--font-body: ' . $body_family . ';';
	$rules[] = '--font-heading: ' . $heading_family . ';';
	$rules[] = '--font-size-root: ' . (int) $typography['base_size'] . 'px;';
	$rules[] = '--layout-content: ' . $layout['content_width'] . 'px;';
	$rules[] = '--layout-wide: ' . $layout['wide_width'] . 'px;';

	return ":root {\n\t" . implode( "\n\t", $rules ) . "\n}";
}

/**
 * Injicera CSS-variabler på frontenden.
 */
function plata_output_theme_variables() {
	if ( ! wp_style_is( 'plata-style', 'enqueued' ) ) {
		return;
	}

	wp_add_inline_style( 'plata-style', plata_get_css_variables() );
}
add_action( 'wp_enqueue_scripts', 'plata_output_theme_variables', 20 );

/**
 * Hämta @font-face-reglerna som ren CSS.
 *
 * @return string
 */
function plata_get_font_faces_css() {
	$faces = plata_get_selected_font_faces();

	if ( empty( $faces ) ) {
		return '';
	}

	ob_start();
	wp_print_font_faces( $faces );
	$markup = (string) ob_get_clean();

	return trim( preg_replace( '~</?style[^>]*>~i', '', $markup ) );
}

/**
 * Lägg till temats dynamiska stilar i blockredigeraren.
 *
 * Editorn läser in `style.min.css` via add_editor_style(), men de sparade
 * inställningarna injiceras separat eftersom de genereras vid körning.
 *
 * @param array<string, mixed> $settings Redigerarinställningar.
 * @return array<string, mixed>
 */
function plata_add_editor_settings_styles( $settings ) {
	$css        = plata_get_css_variables();
	$font_faces = plata_get_font_faces_css();
	$layout     = plata_get_layout();

	if ( '' !== $font_faces ) {
		$css .= "\n" . $font_faces;
	}

	if ( ! isset( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
		$settings['styles'] = array();
	}

	$settings['__experimentalFeatures']['layout']['contentSize'] = $layout['content_width'] . 'px';
	$settings['__experimentalFeatures']['layout']['wideSize']    = $layout['wide_width'] . 'px';

	$settings['styles'][] = array(
		'css'            => $css,
		'__unstableType' => 'theme',
		'isGlobalStyles' => false,
	);

	return $settings;
}
add_filter( 'block_editor_settings_all', 'plata_add_editor_settings_styles' );

/**
 * Temats färger formaterade som en theme.json-palett.
 *
 * @return array<int, array{slug: string, name: string, color: string}>
 */
function plata_get_editor_color_palette() {
	$colors      = plata_get_colors();
	$palette     = array();
	$seen_colors = array();

	foreach ( array_keys( plata_get_color_fields() ) as $key ) {
		if ( ! isset( $colors[ $key ] ) ) {
			continue;
		}

		$normalized_color = strtolower( $colors[ $key ] );

		if ( isset( $seen_colors[ $normalized_color ] ) ) {
			continue;
		}

		$seen_colors[ $normalized_color ] = true;

		$palette[] = array(
			'slug'  => 'plata-' . str_replace( '_', '-', $key ),
			/* translators: %d: Färgens ordningsnummer i paletten. */
			'name'  => sprintf( __( 'Temafärg %d', 'plata' ), count( $palette ) + 1 ),
			'color' => $colors[ $key ],
		);
	}

	return $palette;
}

/**
 * Gör temats färger valbara i blockredigerarens färgpalett.
 *
 * Färgerna skrivs som hex-värden eftersom palettens förhandsvisningar i
 * sidopanelen ligger utanför redigerarens iframe och därför inte når
 * temats CSS-variabler.
 *
 * @param WP_Theme_JSON_Data $theme_json Temats theme.json-data.
 * @return WP_Theme_JSON_Data
 */
function plata_add_colors_to_theme_json( $theme_json ) {
	return $theme_json->update_with(
		array(
			'version'  => 3,
			'settings' => array(
				'color' => array(
					'custom'         => true,
					'customGradient' => true,
					'palette'        => plata_get_editor_color_palette(),
				),
			),
		)
	);
}
add_filter( 'wp_theme_json_data_theme', 'plata_add_colors_to_theme_json' );

/**
 * Töm cachad theme.json-data när färgerna sparas.
 *
 * WordPress cachar de globala stilarna, så paletten skulle annars kunna
 * ligga kvar med gamla värden vid persistent objektcache.
 */
function plata_flush_theme_json_cache() {
	if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
		wp_clean_theme_json_cache();
	}
}
add_action( 'add_option_plata_colors', 'plata_flush_theme_json_cache' );
add_action( 'update_option_plata_colors', 'plata_flush_theme_json_cache' );

/**
 * Använd vald favicon som webbplatsikon.
 *
 * @param string     $url  Nuvarande URL.
 * @param int        $size Storlek i pixlar.
 * @param int|string $blog Blogg-ID.
 * @return string
 */
function plata_filter_site_icon_url( $url, $size, $blog ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$favicon_id = plata_get_favicon_id();

	if ( ! $favicon_id ) {
		return $url;
	}

	$icon_url = wp_get_attachment_image_url( $favicon_id, array( $size, $size ) );

	return $icon_url ? $icon_url : (string) wp_get_attachment_url( $favicon_id );
}
add_filter( 'get_site_icon_url', 'plata_filter_site_icon_url', 10, 3 );
