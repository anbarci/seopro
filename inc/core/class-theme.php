<?php
/**
 * Ana tema konteyneri.
 *
 * @package SeoPro\Core
 */

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Tüm modülleri bağlayan singleton.
 */
final class Theme {

	private static ?Theme $instance = null;

	/**
	 * Tekil örnek.
	 */
	public static function instance(): Theme {
		return self::$instance ??= new self();
	}

	private function __construct() {
		$this->boot();
	}

	/**
	 * Modülleri kaydet.
	 */
	private function boot(): void {
		( new Setup() )->register();
		( new Assets() )->register();
		( new Mode() )->register();
		( new DynamicCss() )->register();
		( new InContent() )->register();
		( new Toc() )->register();
		( new Ads() )->register();
		( new Patterns() )->register();
		( new Newsletter() )->register();

		( new \SeoPro\Customizer\Customizer() )->register();
		( new \SeoPro\Lazyload\Lazyload() )->register();
		( new \SeoPro\Optimizer\Optimizer() )->register();
		( new \SeoPro\Optimizer\Watermark() )->register();
		( new \SeoPro\Schema\Manager() )->register();
		( new \SeoPro\Seo\Head() )->register();
		( new \SeoPro\Seo\Canonical() )->register();
		( new \SeoPro\Amp\Amp() )->register();
		( new \SeoPro\Widgets\Registrar() )->register();
		( new \SeoPro\Members\Members() )->register();
		( new \SeoPro\Security\Security() )->register();

		if ( is_admin() ) {
			( new \SeoPro\Admin\Panel() )->register();
		}
	}

	private function __clone() {}

	public function __wakeup(): void {
		throw new \RuntimeException( 'SeoPro\Core\Theme tek örnektir.' );
	}
}
