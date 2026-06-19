<?php
/**
 * Front-end CSS/JS yükleme hattı.
 *
 * @package SeoPro\Core
 */

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class Assets {

	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'wp_head', [ $this, 'preload_fonts' ], 2 );
	}

	public function preload_fonts(): void {
		foreach ( [ 'worksans-latin' ] as $font ) {
			$file = SEOPRO_DIR . '/assets/fonts/' . $font . '.woff2';
			if ( is_file( $file ) ) {
				printf(
					'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
					esc_url( SEOPRO_URI . '/assets/fonts/' . $font . '.woff2' )
				);
			}
		}
	}

	/**
	 * Tema asset'lerini kuyruğa al.
	 *
	 * jQuery bağımlılığı yoktur; tüm JS vanilla ve defer ile yüklenir.
	 */
	public function enqueue(): void {
		$css = SEOPRO_DIR . '/assets/css/main.css';
		$js  = SEOPRO_DIR . '/assets/js/main.js';

		wp_enqueue_style(
			'seopro-main',
			SEOPRO_URI . '/assets/css/main.css',
			[],
			is_file( $css ) ? (string) filemtime( $css ) : SEOPRO_VERSION
		);

		wp_enqueue_script(
			'seopro-main',
			SEOPRO_URI . '/assets/js/main.js',
			[],
			is_file( $js ) ? (string) filemtime( $js ) : SEOPRO_VERSION,
			[
				'strategy'  => 'defer',
				'in_footer' => true,
			]
		);

		if ( is_singular() && comments_open() && (bool) get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}
