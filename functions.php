<?php
/**
 * SeoPro teması bootstrap.
 *
 * @package SeoPro
 */

defined( 'ABSPATH' ) || exit;

define( 'SEOPRO_VERSION', '1.1.0' );
define( 'SEOPRO_DIR', get_template_directory() );
define( 'SEOPRO_URI', get_template_directory_uri() );

/**
 * PSR-4 benzeri otomatik yükleyici.
 *
 * SeoPro\Core\Setup        -> inc/core/class-setup.php
 * SeoPro\Schema\NewsArticle -> inc/schema/class-news-article.php
 */
spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'SeoPro\\';

		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}

		$parts = explode( '\\', substr( $class, strlen( $prefix ) ) );
		$name  = array_pop( $parts );
		$name  = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $name ) );
		$dir   = strtolower( implode( '/', $parts ) );
		$file  = SEOPRO_DIR . '/inc/' . ( '' !== $dir ? $dir . '/' : '' ) . 'class-' . $name . '.php';

		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

/**
 * Temayı başlat.
 */
\SeoPro\Core\Theme::instance();
