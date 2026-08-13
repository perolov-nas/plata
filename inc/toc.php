<?php
/**
 * Innehållsförteckning byggd från rubrikerna i innehållet.
 *
 * @package Plata
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Meta-nyckel för att visa/dölja innehållsförteckningen på enskilda sidor. */
define( 'PLATA_TOC_META_KEY', 'plata_show_toc' );

/**
 * Plocka ut h2 och h3 ur innehållet och se till att varje rubrik har ett id.
 *
 * Rubriker utan eget ankare får ett id härlett ur rubriktexten, så att
 * innehållsförteckningen kan länka till dem.
 *
 * @param string $content Renderat innehåll.
 * @return array{content: string, headings: array<int, array{id: string, text: string, level: int}>}
 */
function plata_build_toc( $content ) {
	$result = array(
		'content'  => (string) $content,
		'headings' => array(),
	);

	if ( '' === trim( (string) $content ) || ! class_exists( 'DOMDocument' ) ) {
		return $result;
	}

	$dom      = new DOMDocument();
	$internal = libxml_use_internal_errors( true );

	$loaded = $dom->loadHTML(
		'<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>'
		. '<body><div id="plata-toc-root">' . $content . '</div></body></html>'
	);

	libxml_clear_errors();
	libxml_use_internal_errors( $internal );

	if ( ! $loaded ) {
		return $result;
	}

	$xpath = new DOMXPath( $dom );
	$root  = $xpath->query( '//*[@id="plata-toc-root"]' )->item( 0 );

	if ( ! $root instanceof DOMElement ) {
		return $result;
	}

	$used = array();

	foreach ( $xpath->query( './/h2 | .//h3', $root ) as $heading ) {
		if ( ! $heading instanceof DOMElement ) {
			continue;
		}

		$text = trim( $heading->textContent );

		if ( '' === $text ) {
			continue;
		}

		$id = trim( $heading->getAttribute( 'id' ) );

		if ( '' === $id ) {
			$id = sanitize_title( $text );
		}

		if ( '' === $id ) {
			$id = 'avsnitt';
		}

		// Två rubriker med samma text får annars samma ankare.
		$base   = $id;
		$suffix = 2;

		while ( isset( $used[ $id ] ) ) {
			$id = $base . '-' . $suffix;
			++$suffix;
		}

		$used[ $id ] = true;

		$heading->setAttribute( 'id', $id );

		$result['headings'][] = array(
			'id'    => $id,
			'text'  => $text,
			'level' => 'h3' === strtolower( $heading->nodeName ) ? 3 : 2,
		);
	}

	if ( empty( $result['headings'] ) ) {
		return $result;
	}

	$html = '';

	foreach ( $root->childNodes as $child ) {
		$html .= $dom->saveHTML( $child );
	}

	$result['content'] = $html;

	return $result;
}

/**
 * Gruppera rubrikerna så att varje h3 hamnar under närmast föregående h2.
 *
 * @param array<int, array{id: string, text: string, level: int}> $headings Rubriker.
 * @return array<int, array{heading: array{id: string, text: string, level: int}, children: array<int, array{id: string, text: string, level: int}>}>
 */
function plata_group_toc_headings( $headings ) {
	$tree = array();

	foreach ( $headings as $heading ) {
		if ( 2 === $heading['level'] || empty( $tree ) ) {
			$tree[] = array(
				'heading'  => $heading,
				'children' => array(),
			);

			continue;
		}

		$tree[ count( $tree ) - 1 ]['children'][] = $heading;
	}

	return $tree;
}

/**
 * Skriv ut innehållsförteckningen som en nästlad lista.
 *
 * @param array<int, array{id: string, text: string, level: int}> $headings Rubriker.
 */
function plata_render_toc( $headings ) {
	$tree = plata_group_toc_headings( $headings );

	if ( empty( $tree ) ) {
		return;
	}
	?>
	<ul class="toc__list">
		<?php foreach ( $tree as $item ) : ?>
			<li>
				<a class="toc__link" href="#<?php echo esc_attr( $item['heading']['id'] ); ?>">
					<?php echo esc_html( $item['heading']['text'] ); ?>
				</a>

				<?php if ( ! empty( $item['children'] ) ) : ?>
					<ul class="toc__sublist">
						<?php foreach ( $item['children'] as $child ) : ?>
							<li>
								<a class="toc__link toc__link--sub" href="#<?php echo esc_attr( $child['id'] ); ?>">
									<?php echo esc_html( $child['text'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Om innehållsförteckningen ska visas på sidan.
 *
 * Saknad meta räknas som påslaget, så befintliga sidor behåller TOC:en
 * tills någon aktivt stänger av den.
 *
 * @param int $post_id Sidans ID.
 * @return bool
 */
function plata_page_shows_toc( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id ) {
		return true;
	}

	// Endast ett uttryckligt "0" stänger av. Sidor utan sparat värde får
	// standardvärdet från registreringen och behåller alltså sin TOC.
	return '0' !== (string) get_post_meta( $post_id, PLATA_TOC_META_KEY, true );
}

/**
 * Registrera sidmeta.
 *
 * Värdet lagras som strängen "1" eller "0", inte som boolean. Ett booleskt
 * false hamnar i databasen som tom sträng, vilket inte går att skilja från
 * ett osparat värde.
 *
 * Metan hålls utanför REST med flit: kryssrutan sitter i en klassisk
 * meta-box, och om blockredigeraren också skickade med fältet skulle den
 * kunna skriva tillbaka sitt inlästa värde över det som just sparats.
 */
function plata_register_toc_meta() {
	register_post_meta(
		'page',
		PLATA_TOC_META_KEY,
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '1',
			'show_in_rest'      => false,
			'auth_callback'     => static function () {
				return current_user_can( 'edit_pages' );
			},
			'sanitize_callback' => static function ( $value ) {
				return $value ? '1' : '0';
			},
		)
	);
}
add_action( 'init', 'plata_register_toc_meta' );

/**
 * Meta-box för att aktivera/avaktivera TOC på enskilda sidor.
 */
function plata_add_toc_meta_box() {
	add_meta_box(
		'plata_toc',
		__( 'Innehållsförteckning', 'plata' ),
		'plata_render_toc_meta_box',
		'page',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'plata_add_toc_meta_box' );

/**
 * Rita kryssrutan i meta-boxen.
 *
 * @param WP_Post $post Sidan som redigeras.
 */
function plata_render_toc_meta_box( $post ) {
	wp_nonce_field( 'plata_save_toc_meta', 'plata_toc_nonce' );

	$checked = plata_page_shows_toc( $post->ID );
	?>
	<p>
		<label for="plata_show_toc">
			<input
				type="checkbox"
				id="plata_show_toc"
				name="plata_show_toc"
				value="1"
				<?php checked( $checked ); ?>
			/>
			<?php esc_html_e( 'Visa innehållsförteckning', 'plata' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Gäller mallen Undersida normal. Byggs automatiskt av sidans h2- och h3-rubriker.', 'plata' ); ?>
	</p>
	<?php
}

/**
 * Spara TOC-inställningen.
 *
 * @param int $post_id Sidans ID.
 */
function plata_save_toc_meta( $post_id ) {
	if ( ! isset( $_POST['plata_toc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['plata_toc_nonce'] ) ), 'plata_save_toc_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	// En omarkerad checkbox skickas inte med i POST, så saknad nyckel betyder av.
	$show = isset( $_POST['plata_show_toc'] ) ? '1' : '0';
	update_post_meta( $post_id, PLATA_TOC_META_KEY, $show );
}
add_action( 'save_post_page', 'plata_save_toc_meta' );
