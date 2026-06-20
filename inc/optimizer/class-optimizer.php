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

	/**
	 * AdSense açık ve en az bir reklam kodu girilmişse, ilk görünür reklamın
	 * bağlantı gecikmesini kısaltmak için preconnect/dns-prefetch ipuçları ekler.
	 * AMP isteğinde standart AdSense geçersiz olduğundan atlanır.
	 */
	public function resource_hints( array $hints, string $relation ): array {
		if ( ! $this->ads_present() ) {
			return $hints;
		}
		if ( 'preconnect' === $relation ) {
			$hints[] = [
				'href'        => 'https://pagead2.googlesyndication.com',
				'crossorigin' => 'anonymous',
			];
		} elseif ( 'dns-prefetch' === $relation ) {
			$hints[] = 'https://googleads.g.doubleclick.net';
			$hints[] = 'https://tpc.googlesyndication.com';
		}
		return $hints;
	}

	/**
	 * Reklam sistemi açık (AMP değil) ve en az bir bölgede kod var mı?
	 */
	private function ads_present(): bool {
		if ( \SeoPro\Amp\Amp::is_request() || ! \SeoPro\Core\Options::bool( 'seopro_ads_enable' ) ) {
			return false;
		}
		foreach ( [ 'header', 'before', 'in1', 'in2', 'after', 'sidebar', 'mobile' ] as $zone ) {
			if ( '' !== trim( (string) \SeoPro\Core\Options::get( 'seopro_ad_' . $zone ) ) ) {
				return true;
			}
		}
		return false;
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
		if ( \SeoPro\Amp\Amp::is_request() ) {
			return;
		}

		// Tekil yazı/sayfa: öne çıkan görsel hero boyutunda (eager) basılır → LCP odur.
		// Statik ana sayfa da is_singular() olduğundan buraya düşer ve hero'ya eşlenir
		// (card boyutu preload edilseydi render edilen hero ile çift indirme olurdu).
		if ( is_singular() ) {
			if ( ! has_post_thumbnail() ) {
				return;
			}
			$id   = get_post_thumbnail_id();
			$size = 'seopro-hero';
		} elseif ( ( is_home() || is_front_page() ) && ! is_paged() ) {
			global $wp_query;
			$first = $wp_query->posts[0] ?? null;
			if ( ! $first instanceof \WP_Post ) {
				return;
			}
			$id   = get_post_thumbnail_id( $first );
			$size = 'seopro-card';
		} else {
			return;
		}

		if ( ! $id ) {
			return;
		}
		$src = wp_get_attachment_image_url( $id, $size );
		if ( ! $src ) {
			return;
		}
		$srcset = wp_get_attachment_image_srcset( $id, $size );
		$sizes  = wp_get_attachment_image_sizes( $id, $size );
		printf(
			'<link rel="preload" as="image" href="%s"%s%s fetchpriority="high">' . "\n",
			esc_url( $src ),
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
			( $srcset && $sizes ) ? ' imagesizes="' . esc_attr( $sizes ) . '"' : ''
		);
	}
}
