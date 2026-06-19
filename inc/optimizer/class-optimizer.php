<?php

namespace SeoPro\Optimizer;

defined( 'ABSPATH' ) || exit;

class Optimizer {

	public function register(): void {
		add_action( 'init', [ $this, 'cleanup' ] );
		add_filter( 'wp_resource_hints', [ $this, 'resource_hints' ], 10, 2 );
		add_action( 'wp_enqueue_scripts', [ $this, 'dequeue' ], 100 );
		add_filter( 'wp_editor_set_quality', [ $this, 'image_quality' ], 10, 2 );
		add_action( 'wp_head', [ $this, 'preload_lcp' ], 2 );
	}

	public function cleanup(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'rsd_link' );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	public function resource_hints( array $hints, string $relation ): array {
		return $hints;
	}

	public function dequeue(): void {
		if ( ! is_admin() ) {
			wp_deregister_script( 'wp-embed' );
		}
	}

	public function image_quality( $quality, $mime = '' ): int {
		$q = (int) \SeoPro\Core\Options::get( 'seopro_image_quality' );
		if ( $q < 1 || $q > 100 ) {
			return (int) $quality;
		}
		return $q;
	}

	public function preload_lcp(): void {
		if ( ( ! is_front_page() && ! is_home() ) || is_paged() || \SeoPro\Amp\Amp::is_request() ) {
			return;
		}
		global $wp_query;
		$first = $wp_query->posts[0] ?? null;
		if ( ! $first instanceof \WP_Post ) {
			return;
		}
		$id = get_post_thumbnail_id( $first );
		if ( ! $id ) {
			return;
		}
		$src = wp_get_attachment_image_url( $id, 'seopro-card' );
		if ( ! $src ) {
			return;
		}
		$srcset = wp_get_attachment_image_srcset( $id, 'seopro-card' );
		$sizes  = wp_get_attachment_image_sizes( $id, 'seopro-card' );
		printf(
			'<link rel="preload" as="image" href="%s"%s%s fetchpriority="high">' . "\n",
			esc_url( $src ),
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
			( $srcset && $sizes ) ? ' imagesizes="' . esc_attr( $sizes ) . '"' : ''
		);
	}
}
