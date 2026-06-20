<?php
/**
 * Dinamik (panelden) renk + ölçü token'larını <head>'e inline basar.
 *
 * Önemli: erişilebilir aksan token'ları (buton dolgusu, bağlantı, koyu-zemin
 * aksanı) marka renginden OTOMATİK türetilir (WCAG AA). Böylece hangi renk
 * seçilirse seçilsin butonlar/bağlantılar o renge döner ve kontrast korunur.
 *
 * @package SeoPro\Core
 */

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class DynamicCss {

	public function register(): void {
		add_action( 'wp_head', [ $this, 'output' ], 20 );
		add_action( 'wp_enqueue_scripts', [ $this, 'inline' ], 20 );
	}

	public function inline(): void {
		wp_add_inline_style( 'seopro-main', $this->build() );
	}

	public function output(): void {}

	private function build(): string {
		$primary   = self::norm( sanitize_hex_color( (string) Options::get( 'seopro_brand_primary' ) ) ?: '#4285F4' );
		$hover     = self::norm( sanitize_hex_color( (string) Options::get( 'seopro_brand_hover' ) ) ?: '#3367D6' );
		$secondary = self::norm( sanitize_hex_color( (string) Options::get( 'seopro_brand_secondary' ) ) ?: '#344955' );
		$bg        = self::norm( sanitize_hex_color( (string) Options::get( 'seopro_bg_base' ) ) ?: '#F4F5FA' );
		$text      = self::norm( sanitize_hex_color( (string) Options::get( 'seopro_text_primary' ) ) ?: '#344955' );
		$max       = absint( Options::get( 'seopro_container_max' ) ) ?: 1240;
		$radius    = absint( Options::get( 'seopro_radius' ) );
		$fontBody  = sanitize_text_field( (string) Options::get( 'seopro_font_body' ) );
		$fontHead  = sanitize_text_field( (string) Options::get( 'seopro_font_head' ) );

		// WCAG AA türetimi (hedef 4.6 → 4.5 eşiğine güvenlik payı).
		$fill      = self::fill_for_white( $primary );          // beyaz metinli dolgu (buton/rozet)
		$link      = self::on_bg( $primary, $bg );              // açık zeminde bağlantı
		$linkHover = self::mix( $link, '#000000', 0.20 );
		$onDark    = self::on_bg( $primary, $secondary );       // koyu header/footer üstünde aksan
		$onDarkH   = self::mix( $onDark, '#ffffff', 0.22 );
		$soft      = self::rgba( $primary, 0.12 );
		$darkBg    = '#25353d';                                 // koyu mod zemini (main.css ile aynı)
		$linkDk    = self::on_bg( $primary, $darkBg );
		$linkDkH   = self::mix( $linkDk, '#ffffff', 0.22 );
		$softDk    = self::rgba( $primary, 0.24 );

		$css  = ':root{';
		$css .= '--brand-primary:' . $primary . ';';
		$css .= '--brand-primary-hover:' . $hover . ';';
		$css .= '--brand-secondary:' . $secondary . ';';
		$css .= '--brand-fill:' . $fill . ';';
		$css .= '--brand-soft:' . $soft . ';';
		$css .= '--link:' . $link . ';';
		$css .= '--link-hover:' . $linkHover . ';';
		$css .= '--brand-on-dark:' . $onDark . ';';
		$css .= '--brand-on-dark-hover:' . $onDarkH . ';';
		$css .= '--container-max:' . $max . 'px;';
		$css .= '--radius:' . $radius . 'px;';
		if ( $fontBody ) {
			$css .= '--font-body:"' . $fontBody . '",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;';
		}
		if ( $fontHead ) {
			$css .= '--font-head:"' . $fontHead . '",var(--font-body);';
		}
		$css .= '}';

		$css .= ':root:not([data-theme="dark"]){';
		$css .= '--bg-base:' . $bg . ';';
		$css .= '--text-primary:' . $text . ';';
		$css .= '}';

		// Koyu modda bağlantı/soft, marka renginin koyu-zemin türeviyle.
		$css .= '[data-theme="dark"]{';
		$css .= '--link:' . $linkDk . ';';
		$css .= '--link-hover:' . $linkDkH . ';';
		$css .= '--brand-soft:' . $softDk . ';';
		$css .= '}';

		foreach ( get_categories( [ 'hide_empty' => true, 'number' => 30 ] ) as $cat ) {
			$col  = Helpers::cat_color( (int) $cat->term_id );
			$css .= '.seopro-widget .wp-block-categories li.cat-item-' . (int) $cat->term_id . '{border-bottom:2px solid ' . $col . ';padding-bottom:var(--sp-3);}';
		}

		return wp_strip_all_tags( $css );
	}

	/* ===== Renk yardımcıları (WCAG) ===== */

	private static function norm( string $hex ): string {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return '#' . strtolower( $hex );
	}

	private static function rgb( string $hex ): array {
		$hex = ltrim( self::norm( $hex ), '#' );
		return [ hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) ];
	}

	private static function lin( float $c ): float {
		$c /= 255;
		return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
	}

	private static function lum( string $hex ): float {
		[ $r, $g, $b ] = self::rgb( $hex );
		return 0.2126 * self::lin( (float) $r ) + 0.7152 * self::lin( (float) $g ) + 0.0722 * self::lin( (float) $b );
	}

	private static function contrast( string $a, string $b ): float {
		$la = self::lum( $a );
		$lb = self::lum( $b );
		return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
	}

	private static function mix( string $hex, string $target, float $amt ): string {
		[ $r, $g, $b ]    = self::rgb( $hex );
		[ $tr, $tg, $tb ] = self::rgb( $target );
		return sprintf(
			'#%02x%02x%02x',
			(int) round( $r + ( $tr - $r ) * $amt ),
			(int) round( $g + ( $tg - $g ) * $amt ),
			(int) round( $b + ( $tb - $b ) * $amt )
		);
	}

	private static function rgba( string $hex, float $alpha ): string {
		[ $r, $g, $b ] = self::rgb( $hex );
		return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
	}

	/**
	 * Marka rengini siyaha doğru koyulaştırarak beyaz metnin AA geçtiği dolguyu döndürür.
	 */
	private static function fill_for_white( string $hex, float $target = 4.6 ): string {
		for ( $a = 0.0; $a <= 1.0001; $a += 0.02 ) {
			$c = self::mix( $hex, '#000000', $a );
			if ( self::contrast( '#ffffff', $c ) >= $target ) {
				return $c;
			}
		}
		return '#000000';
	}

	/**
	 * Marka rengini, verilen zeminde AA geçene kadar ayarlar (açık zemin→koyult, koyu zemin→aç).
	 */
	private static function on_bg( string $hex, string $bg, float $target = 4.6 ): string {
		$toward = self::lum( $bg ) > 0.4 ? '#000000' : '#ffffff';
		for ( $a = 0.0; $a <= 1.0001; $a += 0.02 ) {
			$c = self::mix( $hex, $toward, $a );
			if ( self::contrast( $c, $bg ) >= $target ) {
				return $c;
			}
		}
		return $toward;
	}
}
