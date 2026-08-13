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
 * Typografi-fält.
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
 * Hämta sparad typografi.
 *
 * @return array<string, string>
 */
function plata_get_typography() {
	$saved  = get_option( 'plata_typography', array() );
	$fields = plata_get_typography_fields();
	$fonts  = plata_get_available_fonts();
	$out    = array();

	foreach ( $fields as $key => $field ) {
		$slug = isset( $saved[ $key ] ) ? sanitize_title( $saved[ $key ] ) : $field['default'];
		$out[ $key ] = isset( $fonts[ $slug ] ) ? $slug : $field['default'];
	}

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
 * @return array<string, string>
 */
function plata_sanitize_typography( $input ) {
	$clean  = array();
	$fields = plata_get_typography_fields();
	$fonts  = plata_get_available_fonts();

	if ( ! is_array( $input ) ) {
		return $clean;
	}

	foreach ( $fields as $key => $field ) {
		$slug = isset( $input[ $key ] ) ? sanitize_title( $input[ $key ] ) : $field['default'];
		$clean[ $key ] = isset( $fonts[ $slug ] ) ? $slug : $field['default'];
	}

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
 * @return array<string, string>
 */
function plata_get_default_typography() {
	$defaults = array();

	foreach ( plata_get_typography_fields() as $key => $field ) {
		$defaults[ $key ] = $field['default'];
	}

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
 * Ladda WordPress färgväljare på settings-sidan.
 *
 * @param string $hook Aktuell admin-sida.
 */
function plata_admin_assets( $hook ) {
	if ( 'appearance_page_plata-settings' !== $hook ) {
		return;
	}

	$js_file = get_template_directory() . '/assets/dist/js/admin-settings.min.js';

	if ( ! file_exists( $js_file ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script(
		'plata-admin-settings',
		get_template_directory_uri() . '/assets/dist/js/admin-settings.min.js',
		array( 'jquery', 'wp-color-picker' ),
		(string) filemtime( $js_file ),
		true
	);
}
add_action( 'admin_enqueue_scripts', 'plata_admin_assets' );

/**
 * Rendera mediaväljare för en bilaga.
 *
 * @param string $key   Fältnyckel i plata_branding.
 * @param string $label Fältetikett.
 * @param int    $id    Bilaga-ID.
 * @param string $help  Hjälptext.
 */
function plata_render_media_field( $key, $label, $id, $help = '' ) {
	$id      = absint( $id );
	$preview = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	?>
	<tr>
		<th scope="row">
			<label for="plata_branding_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
		</th>
		<td>
			<div class="plata-media-field" data-title="<?php echo esc_attr( $label ); ?>">
				<input
					type="hidden"
					id="plata_branding_<?php echo esc_attr( $key ); ?>"
					name="plata_branding[<?php echo esc_attr( $key ); ?>]"
					value="<?php echo esc_attr( (string) $id ); ?>"
					class="plata-media-id"
				/>
				<div class="plata-media-preview"<?php echo $preview ? '' : ' hidden'; ?>>
					<?php if ( $preview ) : ?>
						<img src="<?php echo esc_url( $preview ); ?>" alt="" />
					<?php endif; ?>
				</div>
				<p>
					<button type="button" class="button plata-media-select">
						<?php echo $id ? esc_html__( 'Byt bild', 'plata' ) : esc_html__( 'Välj bild', 'plata' ); ?>
					</button>
					<button type="button" class="button-link-delete plata-media-remove"<?php echo $id ? '' : ' hidden'; ?>>
						<?php esc_html_e( 'Ta bort', 'plata' ); ?>
					</button>
				</p>
				<?php if ( $help ) : ?>
					<p class="description"><?php echo esc_html( $help ); ?></p>
				<?php endif; ?>
			</div>
		</td>
	</tr>
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
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p><?php esc_html_e( 'Justera temats logotyp, layout, typsnitt och färger. Ändringarna gäller globalt på webbplatsen.', 'plata' ); ?></p>

		<form id="plata-settings-form" method="post" action="options.php">
			<?php settings_fields( 'plata_settings' ); ?>

			<h2><?php esc_html_e( 'Webbplats', 'plata' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				plata_render_media_field(
					'logo_id',
					__( 'Logotyp', 'plata' ),
					$branding['logo_id'],
					__( 'Visas i sidhuvudet. PNG, JPG eller SVG rekommenderas.', 'plata' )
				);
				plata_render_media_field(
					'favicon_id',
					__( 'Favicon', 'plata' ),
					$branding['favicon_id'],
					__( 'Visas som webbplatsikon i webbläsarfliken. Kvadratisk bild rekommenderas.', 'plata' )
				);
				?>
			</table>

			<h2><?php esc_html_e( 'Typografi', 'plata' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: länk till Font Library */
					esc_html__( 'Välj bland installerade typsnitt från %s, eller systemstandard.', 'plata' ),
					'<a href="' . esc_url( $fonts_url ) . '">' . esc_html__( 'Utseende → Typsnitt', 'plata' ) . '</a>'
				);
				?>
			</p>
			<table class="form-table" role="presentation">
				<?php foreach ( $typography_fields as $key => $field ) : ?>
					<tr>
						<th scope="row">
							<label for="plata_font_<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $field['label'] ); ?>
							</label>
						</th>
						<td>
							<select
								id="plata_font_<?php echo esc_attr( $key ); ?>"
								name="plata_typography[<?php echo esc_attr( $key ); ?>]"
							>
								<?php foreach ( $fonts as $font ) : ?>
									<option
										value="<?php echo esc_attr( $font['slug'] ); ?>"
										<?php selected( $typography[ $key ], $font['slug'] ); ?>
										style="font-family: <?php echo esc_attr( $font['fontFamily'] ); ?>;"
									>
										<?php echo esc_html( $font['name'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<h2><?php esc_html_e( 'Layout', 'plata' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="plata_content_width"><?php esc_html_e( 'Innehållsbredd', 'plata' ); ?></label>
					</th>
					<td>
						<input
							type="number"
							id="plata_content_width"
							name="plata_layout[content_width]"
							value="<?php echo esc_attr( (string) $layout['content_width'] ); ?>"
							min="320"
							max="3840"
							step="1"
							class="small-text"
						/>
						<span>px</span>
						<p class="description">
							<?php esc_html_e( 'Maxbredd för block med vanlig innehållsjustering.', 'plata' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="plata_wide_width"><?php esc_html_e( 'Bredd för bred bredd', 'plata' ); ?></label>
					</th>
					<td>
						<input
							type="number"
							id="plata_wide_width"
							name="plata_layout[wide_width]"
							value="<?php echo esc_attr( (string) $layout['wide_width'] ); ?>"
							min="320"
							max="3840"
							step="1"
							class="small-text"
						/>
						<span>px</span>
						<p class="description">
							<?php esc_html_e( 'Maxbredd för block med bred justering. Fullbreddsblock fyller alltid hela viewporten.', 'plata' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php foreach ( $groups as $group_key => $group_label ) : ?>
				<h2><?php echo esc_html( $group_label ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( $fields as $key => $field ) : ?>
						<?php if ( $field['group'] !== $group_key ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<tr>
							<th scope="row">
								<label for="plata_color_<?php echo esc_attr( $key ); ?>">
									<?php echo esc_html( $field['label'] ); ?>
								</label>
							</th>
							<td>
								<input
									type="text"
									id="plata_color_<?php echo esc_attr( $key ); ?>"
									name="plata_colors[<?php echo esc_attr( $key ); ?>]"
									value="<?php echo esc_attr( $colors[ $key ] ); ?>"
									class="plata-color-field"
									data-default-color="<?php echo esc_attr( $field['default'] ); ?>"
								/>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>

		</form>

		<div class="plata-settings-actions">
			<?php
			submit_button(
				__( 'Spara inställningar', 'plata' ),
				'primary',
				'submit',
				false,
				array( 'form' => 'plata-settings-form' )
			);
			?>
			<form
				method="post"
				action=""
				class="plata-reset-form"
				onsubmit="return confirm('<?php echo esc_js( __( 'Återställ alla tema-inställningar till standard?', 'plata' ) ); ?>');"
			>
				<?php wp_nonce_field( 'plata_reset_settings' ); ?>
				<?php submit_button( __( 'Återställ till standard', 'plata' ), 'secondary', 'plata_reset_settings', false ); ?>
			</form>
		</div>
		<style>
			.plata-settings-actions {
				display: flex;
				flex-wrap: wrap;
				align-items: center;
				gap: 0.5em;
				margin: 1.5em 0;
			}
			.plata-reset-form {
				margin: 0;
			}
			.plata-media-preview {
				margin-bottom: 0.75rem;
			}
			.plata-media-preview img {
				display: block;
				max-width: 240px;
				max-height: 120px;
				width: auto;
				height: auto;
				background: #f0f0f1;
				border: 1px solid #c3c4c7;
				padding: 0.5rem;
			}
			.plata-media-field .button-link-delete {
				margin-left: 0.75rem;
				color: #b32d2e;
			}
		</style>
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

	foreach ( $typography as $slug ) {
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
	$colors  = plata_get_colors();
	$palette = array();

	foreach ( plata_get_color_fields() as $key => $field ) {
		if ( ! isset( $colors[ $key ] ) ) {
			continue;
		}

		$palette[] = array(
			'slug'  => 'plata-' . str_replace( '_', '-', $key ),
			'name'  => $field['label'],
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
