<?php

namespace SeoPro\Amp;

defined( 'ABSPATH' ) || exit;

class Sanitizer {

	public static function content( string $html ): string {
		$html = preg_replace( '#<script\b[^>]*>.*?</script>#is', '', $html );
		$html = preg_replace( '#\son\w+="[^"]*"#i', '', $html );
		$html = preg_replace( '#\sstyle="[^"]*"#i', '', $html );

		$html = preg_replace_callback(
			'#<iframe\b[^>]*src=["\']([^"\']*(?:youtube\.com|youtu\.be)[^"\']*)["\'][^>]*></iframe>#i',
			static function ( $m ) {
				if ( ! preg_match( '#(?:youtu\.be/|v=|embed/)([A-Za-z0-9_-]{6,})#', $m[1], $id ) ) {
					return '';
				}
				return '<amp-youtube data-videoid="' . esc_attr( $id[1] ) . '" layout="responsive" width="480" height="270"></amp-youtube>';
			},
			$html
		);

		$html = preg_replace_callback(
			'#<img\b([^>]*)>#i',
			static function ( $m ) {
				$attrs = $m[1];
				preg_match( '/\bsrc=["\']([^"\']+)["\']/i', $attrs, $src );
				if ( empty( $src[1] ) ) {
					return '';
				}
				preg_match( '/\bwidth=["\']?(\d+)/i', $attrs, $w );
				preg_match( '/\bheight=["\']?(\d+)/i', $attrs, $h );
				preg_match( '/\balt=["\']([^"\']*)["\']/i', $attrs, $alt );
				$width  = ! empty( $w[1] ) ? (int) $w[1] : 800;
				$height = ! empty( $h[1] ) ? (int) $h[1] : 450;
				return sprintf(
					'<amp-img src="%s" width="%d" height="%d" layout="responsive" alt="%s"></amp-img>',
					esc_url( $src[1] ),
					$width,
					$height,
					esc_attr( $alt[1] ?? '' )
				);
			},
			$html
		);

		return $html;
	}
}
