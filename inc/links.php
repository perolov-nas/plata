<?php
/**
 * Märk upp länkar som lämnar webbplatsen.
 *
 * @package Plata
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalisera ett värdnamn för jämförelse.
 *
 * @param string $host Värdnamn.
 * @return string
 */
function plata_normalize_host( $host ) {
	return preg_replace( '/^www\./', '', strtolower( (string) $host ) );
}

/**
 * Om en länk pekar bort från den egna webbplatsen.
 *
 * @param string $url       Länkens href.
 * @param string $home_host Webbplatsens värdnamn.
 * @return bool
 */
function plata_is_external_url( $url, $home_host ) {
	$url = trim( (string) $url );

	if ( '' === $url || str_starts_with( $url, '#' ) ) {
		return false;
	}

	$host = wp_parse_url( $url, PHP_URL_HOST );

	/*
	 * Utan värdnamn är länken antingen relativ, alltså intern, eller ett
	 * protokoll som mailto: och tel: där man inte lämnar sidan på samma sätt.
	 */
	if ( ! $host ) {
		return false;
	}

	return plata_normalize_host( $host ) !== plata_normalize_host( $home_host );
}

/**
 * Ge utgående länkar klassen external-link.
 *
 * @param string $content Renderat innehåll.
 * @return string
 */
function plata_mark_external_links( $content ) {
	if ( ! is_string( $content ) || ! str_contains( $content, '<a ' ) ) {
		return $content;
	}

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $content;
	}

	$home_host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$processor = new WP_HTML_Tag_Processor( $content );

	while ( $processor->next_tag( 'a' ) ) {
		$href = $processor->get_attribute( 'href' );

		if ( ! is_string( $href ) || ! plata_is_external_url( $href, $home_host ) ) {
			continue;
		}

		$processor->add_class( 'external-link' );
	}

	return $processor->get_updated_html();
}
add_filter( 'the_content', 'plata_mark_external_links', 20 );
add_filter( 'widget_text_content', 'plata_mark_external_links', 20 );

/**
 * Ge utgående menylänkar samma klass.
 *
 * Menyer byggs inte av the_content, utan varje länk får sina attribut här.
 *
 * @param array<string, string> $atts Attribut för länken.
 * @return array<string, string>
 */
function plata_mark_external_menu_links( $atts ) {
	$href = isset( $atts['href'] ) ? $atts['href'] : '';

	if ( ! plata_is_external_url( $href, (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) ) {
		return $atts;
	}

	$classes = isset( $atts['class'] ) ? $atts['class'] . ' ' : '';

	$atts['class'] = $classes . 'external-link';

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'plata_mark_external_menu_links' );
