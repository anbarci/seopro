<?php

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class NavWalker extends \Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="seopro-nav__sub">';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? [] : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $classes, true );

		$classes[] = 'seopro-nav__item';
		if ( $has_children ) {
			$classes[] = 'seopro-nav__item--parent';
		}

		$class_attr = ' class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '"';

		$output .= '<li' . $class_attr . '>';

		$atts          = [];
		$atts['href']  = ! empty( $item->url ) ? $item->url : '';
		$atts['title'] = ! empty( $item->attr_title ) ? $item->attr_title : '';

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( '' !== $value ) {
				$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}
		}
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$output .= '<a class="seopro-nav__link"' . $attributes . '>' . esc_html( $title ) . '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}
}
