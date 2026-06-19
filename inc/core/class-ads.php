<?php
/**
 * Reklam / AdSense yerleşim sistemi.
 *
 * Veri-temelli yüksek-gelir bölgeleri (AdSense en iyi uygulamaları):
 *  - in1  : girişten sonra (1.-3. paragraf) — en yüksek CTR
 *  - in2  : içerik ortası
 *  - header: header altı, fold üstü (footer'dan ~%25-40 daha iyi)
 *  - before: başlık altı / içerik öncesi
 *  - after : içerik sonu
 *  - sidebar: sticky yan sütun (masaüstü gelirinin %15-25'i)
 *  - mobile: mobil alt sticky anchor
 *
 * Kodlar tema panelinden (Reklamlar sekmesi) raw HTML/JS olarak girilir.
 * AMP isteğinde standart AdSense JS geçersiz olduğundan tüm reklamlar atlanır.
 *
 * @package SeoPro\Core
 */

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class Ads {

	public function register(): void {
		add_action( 'seopro_after_header', [ $this, 'header_zone' ] );
		add_action( 'seopro_sidebar_top', [ $this, 'sidebar_zone' ] );
		add_action( 'wp_footer', [ $this, 'mobile_anchor' ] );
		add_filter( 'the_content', [ $this, 'inject_content' ], 13 );
		add_shortcode( 'seopro_ad', [ $this, 'shortcode' ] );

		// AMP reklamları (amp-auto-ads / amp-ad — standart AdSense JS AMP'te geçersiz).
		add_action( 'seopro_amp_head', [ $this, 'amp_head' ] );
		add_action( 'seopro_amp_body_open', [ $this, 'amp_auto_ads' ] );
		add_action( 'seopro_amp_content_top', [ $this, 'amp_top' ] );
		add_action( 'seopro_amp_content_bottom', [ $this, 'amp_bottom' ] );
	}

	/**
	 * Reklam sistemi genel olarak açık mı + AMP değil mi?
	 */
	private function active(): bool {
		if ( \SeoPro\Amp\Amp::is_request() ) {
			return false;
		}
		return Options::bool( 'seopro_ads_enable' );
	}

	/**
	 * Bir bölgenin kodunu döndürür.
	 */
	private function code( string $zone ): string {
		return trim( (string) Options::get( 'seopro_ad_' . $zone ) );
	}

	/**
	 * Reklam kodunu etiketli kutuya sarar. Kod admin tarafından girilir (güvenilir, raw).
	 *
	 * Tembel yükleme açıkken kod inert <template> içine konur: sayfa yüklenirken
	 * HİÇBİR ağ isteği/script çalışmaz. main.js ilk etkileşim/idle sonrası,
	 * IntersectionObserver ile görünüme yaklaşınca şablonu canlandırır.
	 */
	private function box( string $zone, string $code ): string {
		if ( '' === $code ) {
			return '';
		}
		$cls   = 'seopro-ad seopro-ad--' . $zone;
		$label = '<span class="seopro-ad__label">' . esc_html__( 'Reklam', 'seopro' ) . '</span>';

		if ( Options::bool( 'seopro_ads_lazy' ) ) {
			return '<div class="' . esc_attr( $cls . ' seopro-ad--lazy' ) . '" data-seopro-ad-lazy="1">'
				. $label
				. '<template data-seopro-ad-code>' . $code . '</template>'
				. '</div>';
		}

		return '<div class="' . esc_attr( $cls ) . '">' . $label . $code . '</div>';
	}

	/**
	 * Bir bölgeyi doğrudan basar.
	 */
	private function render( string $zone ): void {
		if ( ! $this->active() ) {
			return;
		}
		echo $this->box( $zone, $this->code( $zone ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function header_zone(): void {
		$this->render( 'header' );
	}

	public function sidebar_zone(): void {
		$this->render( 'sidebar' );
	}

	/**
	 * Mobil alt sticky anchor (sadece reklam kodu varsa).
	 */
	public function mobile_anchor(): void {
		if ( ! $this->active() ) {
			return;
		}
		$code = $this->code( 'mobile' );
		if ( '' === $code ) {
			return;
		}
		?>
		<div class="seopro-ad-anchor" data-seopro-anchor hidden>
			<button type="button" class="seopro-ad-anchor__close" data-seopro-anchor-close aria-label="<?php esc_attr_e( 'Kapat', 'seopro' ); ?>">&times;</button>
			<?php echo $this->box( 'mobile', $code ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
	}

	/**
	 * İçerik içi reklamlar: başlık-öncesi, paragraf-arası ve içerik-sonu.
	 */
	public function inject_content( $content ) {
		if ( ! $this->active() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// Paragraf-arası: yüksek N'i önce ekle ki düşük N'in indeksi kaymasın.
		$n2 = max( 1, (int) Options::get( 'seopro_ad_in2_after' ) );
		$n1 = max( 1, (int) Options::get( 'seopro_ad_in1_after' ) );
		$content = $this->after_paragraph( $content, $this->code( 'in2' ), $n2, 'in2' );
		$content = $this->after_paragraph( $content, $this->code( 'in1' ), $n1, 'in1' );

		$before = $this->box( 'before', $this->code( 'before' ) );
		$after  = $this->box( 'after', $this->code( 'after' ) );

		return $before . $content . $after;
	}

	/**
	 * Kodu N. paragraftan (</p>) sonra ekler; o kadar paragraf yoksa atlar.
	 */
	private function after_paragraph( string $content, string $code, int $n, string $zone ): string {
		if ( '' === $code ) {
			return $content;
		}
		$offset = 0;
		for ( $i = 0; $i < $n; $i++ ) {
			$pos = strpos( $content, '</p>', $offset );
			if ( false === $pos ) {
				return $content;
			}
			$offset = $pos + 4;
		}
		return substr_replace( $content, $this->box( $zone, $code ), $offset, 0 );
	}

	/**
	 * [seopro_ad zone="in1"] kısa kodu — yazı içinde elle yerleştirme.
	 */
	public function shortcode( $atts ): string {
		if ( ! $this->active() ) {
			return '';
		}
		$atts = shortcode_atts( [ 'zone' => 'in1' ], $atts, 'seopro_ad' );
		$zone = preg_replace( '/[^a-z0-9_]/', '', (string) $atts['zone'] );
		return $this->box( $zone, $this->code( $zone ) );
	}

	/* ===== AMP reklamları ===== */

	private function amp_active(): bool {
		return Options::bool( 'seopro_amp_ads_enable' ) && '' !== $this->amp_client();
	}

	private function amp_client(): string {
		return preg_replace( '/[^a-zA-Z0-9-]/', '', (string) Options::get( 'seopro_amp_ad_client' ) );
	}

	private function amp_mode(): string {
		return 'manual' === Options::get( 'seopro_amp_ad_mode' ) ? 'manual' : 'auto';
	}

	/**
	 * AMP <head>: gerekli custom-element script'i.
	 */
	public function amp_head(): void {
		if ( ! $this->amp_active() ) {
			return;
		}
		if ( 'auto' === $this->amp_mode() ) {
			echo '<script async custom-element="amp-auto-ads" src="https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js"></script>' . "\n";
		} else {
			echo '<script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>' . "\n";
		}
	}

	/**
	 * <body> hemen altı: amp-auto-ads (yalnızca otomatik mod).
	 */
	public function amp_auto_ads(): void {
		if ( ! $this->amp_active() || 'auto' !== $this->amp_mode() ) {
			return;
		}
		printf(
			'<amp-auto-ads type="adsense" data-ad-client="%s"></amp-auto-ads>' . "\n",
			esc_attr( $this->amp_client() )
		);
	}

	/**
	 * Manuel modda bir amp-ad birimi (responsive full-width).
	 */
	private function amp_unit( string $slot_key ): string {
		$slot = preg_replace( '/\D/', '', (string) Options::get( $slot_key ) );
		if ( '' === $slot ) {
			return '';
		}
		return sprintf(
			'<div class="amp-ad-wrap"><amp-ad width="100vw" height="320" type="adsense" data-ad-client="%s" data-ad-slot="%s" data-auto-format="rspv" data-full-width=""><div overflow=""></div></amp-ad></div>',
			esc_attr( $this->amp_client() ),
			esc_attr( $slot )
		);
	}

	public function amp_top(): void {
		if ( $this->amp_active() && 'manual' === $this->amp_mode() ) {
			echo $this->amp_unit( 'seopro_amp_slot_top' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public function amp_bottom(): void {
		if ( $this->amp_active() && 'manual' === $this->amp_mode() ) {
			echo $this->amp_unit( 'seopro_amp_slot_bottom' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
