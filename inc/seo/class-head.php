<?php

namespace SeoPro\Seo;

defined( 'ABSPATH' ) || exit;

class Head {

	public function register(): void {
		if ( $this->seo_plugin_active() ) {
			return;
		}
		add_action( 'wp_head', [ $this, 'output' ], 5 );
		add_filter( 'wp_robots', [ $this, 'robots' ] );
	}

	public function robots( $robots ) {
		if ( is_search() || is_404() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}

	private function seo_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'SEOPress' );
	}

	private function description(): string {
		if ( is_singular() ) {
			$excerpt = get_the_excerpt();
			return $excerpt ? wp_strip_all_tags( $excerpt ) : '';
		}
		if ( is_category() || is_tag() || is_tax() ) {
			return wp_strip_all_tags( (string) term_description() );
		}
		return get_bloginfo( 'description' );
	}

	public function output(): void {
		$desc  = mb_substr( trim( $this->description() ), 0, 160 );
		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink() : home_url( add_query_arg( [] ) );
		$image     = '';
		$image_w   = 0;
		$image_h   = 0;
		$image_alt = '';

		if ( is_singular() && has_post_thumbnail() ) {
			$thumb_id = get_post_thumbnail_id();
			$src      = wp_get_attachment_image_src( $thumb_id, 'seopro-hero' );
			if ( $src ) {
				$image     = $src[0];
				$image_w   = (int) $src[1];
				$image_h   = (int) $src[2];
				$image_alt = trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
				if ( '' === $image_alt ) {
					$image_alt = $title;
				}
			}
		}

		// Fallback paylaşım görseli (anasayfa/arşiv/görselsiz yazı): panel ayarı, yoksa özel logo.
		if ( '' === $image ) {
			$fallback_id = (int) \SeoPro\Core\Options::get( 'seopro_og_default' );
			if ( ! $fallback_id ) {
				$fallback_id = (int) get_theme_mod( 'custom_logo' );
			}
			if ( $fallback_id ) {
				$src = wp_get_attachment_image_src( $fallback_id, 'full' );
				if ( $src ) {
					$image     = $src[0];
					$image_w   = (int) $src[1];
					$image_h   = (int) $src[2];
					$image_alt = get_bloginfo( 'name' );
				}
			}
		}

		$tags = [];
		if ( $desc ) {
			$tags[] = [ 'name' => 'description', 'content' => $desc ];
		}
		$tags[] = [ 'property' => 'og:site_name', 'content' => get_bloginfo( 'name' ) ];
		$tags[] = [ 'property' => 'og:locale', 'content' => str_replace( '-', '_', (string) get_bloginfo( 'language' ) ) ];
		$tags[] = [ 'property' => 'og:type', 'content' => is_singular( 'post' ) ? 'article' : 'website' ];
		if ( is_singular( 'post' ) ) {
			$tags[] = [ 'property' => 'article:published_time', 'content' => get_the_date( 'c' ) ];
			$tags[] = [ 'property' => 'article:modified_time', 'content' => get_the_modified_date( 'c' ) ];
		}
		$tags[] = [ 'property' => 'og:title', 'content' => $title ];
		$tags[] = [ 'property' => 'og:url', 'content' => $url ];
		if ( $desc ) {
			$tags[] = [ 'property' => 'og:description', 'content' => $desc ];
		}
		if ( $image ) {
			$tags[] = [ 'property' => 'og:image', 'content' => $image ];
			if ( $image_w && $image_h ) {
				$tags[] = [ 'property' => 'og:image:width', 'content' => (string) $image_w ];
				$tags[] = [ 'property' => 'og:image:height', 'content' => (string) $image_h ];
			}
			if ( $image_alt ) {
				$tags[] = [ 'property' => 'og:image:alt', 'content' => $image_alt ];
			}
		}
		$tags[] = [ 'name' => 'twitter:card', 'content' => $image ? 'summary_large_image' : 'summary' ];
		$tags[] = [ 'name' => 'twitter:title', 'content' => $title ];
		if ( $desc ) {
			$tags[] = [ 'name' => 'twitter:description', 'content' => $desc ];
		}
		if ( $image ) {
			$tags[] = [ 'name' => 'twitter:image', 'content' => $image ];
			if ( $image_alt ) {
				$tags[] = [ 'name' => 'twitter:image:alt', 'content' => $image_alt ];
			}
		}

		$out = "\n";
		foreach ( $tags as $tag ) {
			$attr = isset( $tag['property'] ) ? 'property' : 'name';
			$key  = $tag['property'] ?? $tag['name'];
			$out .= sprintf(
				'<meta %1$s="%2$s" content="%3$s" />' . "\n",
				$attr,
				esc_attr( $key ),
				esc_attr( $tag['content'] )
			);
		}
		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
