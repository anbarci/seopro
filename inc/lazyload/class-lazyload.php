<?php

namespace SeoPro\Lazyload;

use SeoPro\Core\Options;

defined( 'ABSPATH' ) || exit;

class Lazyload {

	public function register(): void {
		if ( ! Options::bool( 'seopro_lazyload' ) ) {
			return;
		}
		add_filter( 'the_content', [ $this, 'images' ], 20 );
		add_filter( 'the_content', [ $this, 'iframes' ], 21 );
		add_filter( 'wp_lazy_loading_enabled', '__return_true' );
	}

	private function is_amp(): bool {
		return \SeoPro\Amp\Amp::is_request();
	}

	public function images( string $html ): string {
		if ( is_admin() || is_feed() || $this->is_amp() || '' === trim( $html ) ) {
			return $html;
		}
		return (string) preg_replace_callback(
			'/<img\b[^>]*>/i',
			static function ( $m ) {
				$tag = $m[0];
				if ( ! preg_match( '/\bloading=/i', $tag ) ) {
					$tag = str_replace( '<img', '<img loading="lazy"', $tag );
				}
				if ( ! preg_match( '/\bdecoding=/i', $tag ) ) {
					$tag = str_replace( '<img', '<img decoding="async"', $tag );
				}
				return $tag;
			},
			$html
		);
	}

	public function iframes( string $html ): string {
		if ( is_admin() || is_feed() || $this->is_amp() ) {
			return $html;
		}
		return (string) preg_replace_callback(
			'#<iframe\b[^>]*src=["\']([^"\']*(?:youtube\.com|youtube-nocookie\.com|youtu\.be)[^"\']*)["\'][^>]*></iframe>#i',
			static function ( $m ) {
				if ( ! preg_match( '#(?:youtu\.be/|v=|embed/)([A-Za-z0-9_-]{6,})#', $m[1], $id ) ) {
					return $m[0];
				}
				$vid = esc_attr( $id[1] );
				$thumb = esc_url( 'https://i.ytimg.com/vi/' . $vid . '/hqdefault.jpg' );
				return '<div class="seopro-embed" data-seopro-embed="youtube" data-id="' . $vid . '">'
					. '<button type="button" class="seopro-embed__play" aria-label="' . esc_attr__( 'Videoyu oynat', 'seopro' ) . '">'
					. '<img class="seopro-embed__poster" src="' . $thumb . '" alt="" loading="lazy" decoding="async" width="480" height="360">'
					. '<span class="seopro-embed__icon" aria-hidden="true">▶</span>'
					. '</button></div>';
			},
			$html
		);
	}
}
