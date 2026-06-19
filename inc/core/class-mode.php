<?php
/**
 * Site modu: blog vs haber. Layout ve body class'ı yönetir.
 *
 * Faz 1'de Customizer'dan ayarlanabilir hale gelecek; şimdilik
 * varsayılan "blog" ve `seopro_site_mode` filtresiyle override edilebilir.
 *
 * @package SeoPro\Core
 */

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class Mode {

	public const BLOG = 'blog';
	public const NEWS = 'news';

	public function register(): void {
		add_filter( 'body_class', [ $this, 'body_class' ] );
	}

	/**
	 * Geçerli site modunu döndür.
	 */
	public static function current(): string {
		$mode = (string) get_theme_mod( 'seopro_site_mode', self::BLOG );

		/**
		 * Site modunu filtrele.
		 *
		 * @param string $mode 'blog' veya 'news'.
		 */
		$mode = apply_filters( 'seopro_site_mode', $mode );

		return in_array( $mode, [ self::BLOG, self::NEWS ], true ) ? $mode : self::BLOG;
	}

	public static function is_news(): bool {
		return self::NEWS === self::current();
	}

	public static function is_blog(): bool {
		return self::BLOG === self::current();
	}

	/**
	 * Mode'a göre body class ekle.
	 *
	 * @param string[] $classes Mevcut class'lar.
	 * @return string[]
	 */
	public function body_class( array $classes ): array {
		$classes[] = 'seopro-mode-' . self::current();
		return $classes;
	}
}
