<?php

namespace SeoPro\Seo;

use SeoPro\Core\Options;

defined( 'ABSPATH' ) || exit;

class Head {

	public function register(): void {
		// ?replytocom=N her yorum için yazının kopya bir URL'sini üretir; Google bunları
		// ayrı sayfa olarak tarar. Yanıtla düğmesi zaten JS ile çalışır, href'e gerek yok.
		// (SEO eklentisi varken de zararsız; Yoast aynısını yapar.)
		add_filter( 'comment_reply_link', [ $this, 'reply_link' ] );

		if ( $this->seo_plugin_active() ) {
			return;
		}
		add_action( 'wp_head', [ $this, 'output' ], 5 );
		add_filter( 'wp_robots', [ $this, 'robots' ] );
	}

	public function robots( $robots ) {
		if ( $this->should_noindex() ) {
			unset( $robots['index'] );
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}

	/**
	 * Arama/404, yazıda işaretli noindex ve panelde seçilen arşiv türleri.
	 * Zayıf/tekrar eden arşivler dizine girip tarama bütçesini ve site kalitesini düşürmesin.
	 */
	private function should_noindex(): bool {
		if ( is_search() || is_404() || isset( $_GET['replytocom'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
		if ( is_singular() ) {
			return (bool) get_post_meta( get_queried_object_id(), PostMeta::NOINDEX, true );
		}
		if ( is_date() ) {
			return Options::bool( 'seopro_noindex_date' );
		}
		if ( is_author() ) {
			return Options::bool( 'seopro_noindex_author' );
		}
		if ( is_tag() ) {
			$mode = (string) Options::get( 'seopro_noindex_tags' );
			if ( 'noindex' === $mode ) {
				return true;
			}
			$term = get_queried_object();
			return 'thin' === $mode && $term instanceof \WP_Term && (int) $term->count < PostMeta::thin_min();
		}
		return false;
	}

	private function seo_plugin_active(): bool {
		return \SeoPro\Core\Helpers::seo_plugin_active();
	}

	private function description(): string {
		if ( is_singular() ) {
			$custom = PostMeta::description( get_queried_object_id() );
			if ( '' !== $custom ) {
				return $custom;
			}
			$excerpt = get_the_excerpt();
			return $excerpt ? wp_strip_all_tags( $excerpt ) : '';
		}
		// Liste sayfaları: her biri kendine özgü açıklama alsın. Eskiden anasayfa, yazar,
		// tarih, arama, 404 ve 2. sayfa aynı site sloganını kullanıyordu (kopya açıklama).
		if ( is_category() || is_tag() || is_tax() ) {
			$desc = wp_strip_all_tags( (string) term_description() );
			if ( '' === trim( $desc ) ) {
				/* translators: 1: term name, 2: site name. */
				$desc = sprintf( __( '%1$s ile ilgili yazılar – %2$s', 'seopro' ), single_term_title( '', false ), get_bloginfo( 'name' ) );
			}
			return $this->paged( $desc );
		}
		if ( is_author() ) {
			$author = get_queried_object();
			if ( ! $author instanceof \WP_User ) {
				return '';
			}
			$bio = trim( wp_strip_all_tags( (string) $author->description ) );
			/* translators: 1: author name, 2: site name. */
			return $this->paged( '' !== $bio ? $bio : sprintf( __( '%1$s tarafından yazılan yazılar – %2$s', 'seopro' ), $author->display_name, get_bloginfo( 'name' ) ) );
		}
		if ( is_front_page() || is_home() ) {
			return $this->paged( get_bloginfo( 'description' ) );
		}
		return '';
	}

	/**
	 * 2. ve sonraki liste sayfalarına sayfa numarası ekle (kopya açıklama olmasın).
	 */
	private function paged( string $desc ): string {
		$page = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		if ( '' === trim( $desc ) || $page < 2 ) {
			return $desc;
		}
		/* translators: 1: description, 2: page number. */
		return sprintf( __( '%1$s (Sayfa %2$d)', 'seopro' ), $desc, $page );
	}

	/**
	 * Yanıtla bağlantısındaki ?replytocom=N parametresini kaldır.
	 */
	public function reply_link( $link ) {
		return (string) preg_replace( '#href=([\'"])[^\'"]*\?replytocom=\d+\#respond\1#', 'href=$1#respond$1', (string) $link );
	}

	public function output(): void {
		$noindex = $this->should_noindex();
		// noindex sayfada açıklama işe yaramaz; kopya açıklama sayısını da artırır.
		$desc  = $noindex ? '' : mb_substr( trim( $this->description() ), 0, 160 );
		$title = wp_get_document_title();
		$url   = is_singular() ? ( wp_get_canonical_url() ?: get_permalink() ) : \SeoPro\Core\Helpers::current_url();
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
		// WordPress çekirdeği rel=canonical'ı yalnız tekil sayfalara basar. Anasayfa,
		// kategori, etiket ve sayfalama da kanonik adres alsın (?utm_… gibi parametreli
		// kopyalar tek adrese toplansın). Sayfalamada her sayfa kendisini gösterir.
		if ( ! is_singular() && ! $noindex ) {
			$out .= '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
		}
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
