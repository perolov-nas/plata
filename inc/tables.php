<?php
/**
 * Responsiva tabeller.
 *
 * Varje cell i tabellkroppen får rubrikens text i ett data-label-attribut.
 * När tabellen är för smal staplas raderna till kort där CSS skriver ut
 * etiketten framför värdet, så att kolumnen går att förstå utan rubrikrad.
 *
 * @package Plata
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Leta upp tabellens rubriker.
 *
 * Rubrikerna hämtas i första hand från thead. Saknas thead duger första
 * raden i tbody om den enbart består av th-celler.
 *
 * @param DOMXPath   $xpath XPath för dokumentet.
 * @param DOMElement $table Tabellen.
 * @return array{labels: array<int, string>, header_row: DOMElement|null}
 */
function plata_get_table_headers( $xpath, $table ) {
	$empty = array(
		'labels'     => array(),
		'header_row' => null,
	);

	$head_cells = $xpath->query( './/thead/tr[1]/*[self::th or self::td]', $table );

	if ( $head_cells->length > 0 ) {
		$labels = array();

		foreach ( $head_cells as $cell ) {
			$labels[] = trim( $cell->textContent );
		}

		return array(
			'labels'     => $labels,
			'header_row' => null,
		);
	}

	$first_row = $xpath->query( './/tbody/tr[1]', $table )->item( 0 );

	if ( ! $first_row instanceof DOMElement ) {
		return $empty;
	}

	$cells  = $xpath->query( './*[self::th or self::td]', $first_row );
	$labels = array();

	foreach ( $cells as $cell ) {
		if ( 'th' !== strtolower( $cell->nodeName ) ) {
			return $empty;
		}

		$labels[] = trim( $cell->textContent );
	}

	if ( empty( $labels ) ) {
		return $empty;
	}

	return array(
		'labels'     => $labels,
		'header_row' => $first_row,
	);
}

/**
 * Märk upp tabeller från blockredigeraren så att de kan staplas på smal skärm.
 *
 * @param string               $block_content Renderat block.
 * @param array<string, mixed> $block         Blockdata.
 * @return string
 */
function plata_responsive_tables( $block_content, $block ) {
	if ( ! isset( $block['blockName'] ) || 'core/table' !== $block['blockName'] ) {
		return $block_content;
	}

	if ( ! class_exists( 'DOMDocument' ) || ! str_contains( $block_content, '<table' ) ) {
		return $block_content;
	}

	$dom      = new DOMDocument();
	$internal = libxml_use_internal_errors( true );

	$loaded = $dom->loadHTML(
		'<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>'
		. '<body><div id="plata-table-root">' . $block_content . '</div></body></html>'
	);

	libxml_clear_errors();
	libxml_use_internal_errors( $internal );

	if ( ! $loaded ) {
		return $block_content;
	}

	$xpath = new DOMXPath( $dom );
	$root  = $xpath->query( '//*[@id="plata-table-root"]' )->item( 0 );

	if ( ! $root instanceof DOMElement ) {
		return $block_content;
	}

	$table = $xpath->query( './/table', $root )->item( 0 );

	if ( ! $table instanceof DOMElement ) {
		return $block_content;
	}

	$headers = plata_get_table_headers( $xpath, $table );
	$labels  = $headers['labels'];

	// Utan rubriker vore etiketterna gissningar, då får tabellen scrolla i sidled.
	if ( empty( $labels ) ) {
		return $block_content;
	}

	if ( $headers['header_row'] instanceof DOMElement ) {
		$headers['header_row']->setAttribute( 'class', trim( $headers['header_row']->getAttribute( 'class' ) . ' plata-table__header-row' ) );
	}

	foreach ( $xpath->query( './/tbody/tr', $table ) as $row ) {
		if ( $row === $headers['header_row'] ) {
			continue;
		}

		$column = 0;

		foreach ( $xpath->query( './*[self::th or self::td]', $row ) as $cell ) {
			$colspan = max( 1, (int) $cell->getAttribute( 'colspan' ) );

			// En cell som spänner över flera kolumner hör inte till en enda
			// rubrik, så den lämnas utan etikett.
			if ( 1 === $colspan && isset( $labels[ $column ] ) && '' !== $labels[ $column ] && '' === $cell->getAttribute( 'data-label' ) ) {
				$cell->setAttribute( 'data-label', $labels[ $column ] );
			}

			$column += $colspan;
		}
	}

	$table->setAttribute( 'class', trim( $table->getAttribute( 'class' ) . ' plata-table--stacked' ) );

	$html = '';

	foreach ( $root->childNodes as $child ) {
		$html .= $dom->saveHTML( $child );
	}

	return $html;
}
add_filter( 'render_block', 'plata_responsive_tables', 10, 2 );
