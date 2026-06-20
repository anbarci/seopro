<?php
/**
 * Hafif tema cache katmanı (versiyonlu transient).
 *
 * Pahalı, her-sayfada tekrarlayan tema sorgularını (sekmeli yazılar,
 * flipbar, benzer içerik, popüler) sonuç bazında cache'ler. Tek tek anahtar
 * silmek yerine global bir sürüm sayacı kullanılır: içerik değişince sayaç
 * artırılır, eski anahtarlar erişilemez hâle gelir ve TTL ile kendiliğinden
 * düşer. Böylece kalıcı object cache olmadan da (anahtar tarama gerektirmeden)
 * güvenli invalidasyon sağlanır.
 *
 * @package SeoPro\Core
 */

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class Cache {

	private const VER_OPTION = 'seopro_cache_ver';

	private static ?int $ver = null;

	public function register(): void {
		// İçerik değişiminde tüm tema cache'ini geçersiz kıl.
		add_action( 'save_post', [ $this, 'on_post_change' ], 10, 1 );
		add_action( 'deleted_post', [ $this, 'on_post_change' ], 10, 1 );
		add_action( 'trashed_post', [ $this, 'on_post_change' ], 10, 1 );
		add_action( 'transition_post_status', [ $this, 'bump' ] );
		add_action( 'wp_insert_comment', [ $this, 'bump' ] );
		add_action( 'transition_comment_status', [ $this, 'bump' ] );
	}

	/**
	 * Aktif cache sürümü (bir kez okunur).
	 */
	public static function version(): int {
		if ( null === self::$ver ) {
			self::$ver = max( 1, (int) get_option( self::VER_OPTION, 1 ) );
		}
		return self::$ver;
	}

	/**
	 * Revizyon/otomatik kayıtlarda boşuna invalidasyon yapma.
	 */
	public function on_post_change( $post_id ): void {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		$this->bump();
	}

	/**
	 * Sürümü artır → tüm tema cache anahtarları geçersiz olur.
	 */
	public function bump(): void {
		$next = self::version() + 1;
		update_option( self::VER_OPTION, $next, false ); // autoload kapalı
		self::$ver = $next;
	}

	/**
	 * Anahtar varsa döndür, yoksa $cb ile üret + cache'le.
	 *
	 * Customizer ön izlemesinde (canlı ayar denemesi) cache atlanır ki
	 * görünüm/sekme değişiklikleri anında görünsün.
	 */
	public static function remember( string $key, int $ttl, callable $cb ) {
		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
			return $cb();
		}
		$full  = 'seopro_' . self::version() . '_' . $key;
		$value = get_transient( $full );
		if ( false === $value ) {
			$value = $cb();
			set_transient( $full, $value, max( 60, $ttl ) );
		}
		return $value;
	}
}
