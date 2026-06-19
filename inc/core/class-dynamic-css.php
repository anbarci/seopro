<?php

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class DynamicCss {

	public function register(): void {
		add_action( 'wp_head', [ $this, 'output' ], 20 );
		add_action( 'wp_enqueue_scripts', [ $this, 'inline' ], 20 );
	}

	public function inline(): void {
		wp_add_inline_style( 'seopro-main', $this->build() );
	}

	public function output(): void {}

	private function build(): string {
		$primary   = sanitize_hex_color( (string) Options::get( 'seopro_brand_primary' ) ) ?: '#4285F4';
		$hover     = sanitize_hex_color( (string) Options::get( 'seopro_brand_hover' ) ) ?: '#3367D6';
		$secondary = sanitize_hex_color( (string) Options::get( 'seopro_brand_secondary' ) ) ?: '#344955';
		$bg        = sanitize_hex_color( (string) Options::get( 'seopro_bg_base' ) ) ?: '#F4F5FA';
		$text      = sanitize_hex_color( (string) Options::get( 'seopro_text_primary' ) ) ?: '#344955';
		$max       = absint( Options::get( 'seopro_container_max' ) ) ?: 1240;
		$radius    = absint( Options::get( 'seopro_radius' ) );
		$fontBody  = sanitize_text_field( (string) Options::get( 'seopro_font_body' ) );
		$fontHead  = sanitize_text_field( (string) Options::get( 'seopro_font_head' ) );

		$css  = ':root{';
		$css .= '--brand-primary:' . $primary . ';';
		$css .= '--brand-primary-hover:' . $hover . ';';
		$css .= '--brand-secondary:' . $secondary . ';';
		$css .= '--container-max:' . $max . 'px;';
		$css .= '--radius:' . $radius . 'px;';
		if ( $fontBody ) {
			$css .= '--font-body:"' . $fontBody . '",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;';
		}
		if ( $fontHead ) {
			$css .= '--font-head:"' . $fontHead . '",var(--font-body);';
		}
		$css .= '}';

		$css .= ':root:not([data-theme="dark"]){';
		$css .= '--bg-base:' . $bg . ';';
		$css .= '--text-primary:' . $text . ';';
		$css .= '}';

		foreach ( get_categories( [ 'hide_empty' => true, 'number' => 30 ] ) as $cat ) {
			$col  = Helpers::cat_color( (int) $cat->term_id );
			$css .= '.seopro-widget .wp-block-categories li.cat-item-' . (int) $cat->term_id . '{border-bottom:2px solid ' . $col . ';padding-bottom:var(--sp-3);}';
		}

		return wp_strip_all_tags( $css );
	}
}
