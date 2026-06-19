<?php
/**
 * Blok desenleri (patterns) ve blok stilleri.
 *
 * @package SeoPro\Core
 */

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class Patterns {

	public function register(): void {
		add_action( 'init', [ $this, 'block_styles' ] );
		add_action( 'init', [ $this, 'block_patterns' ] );
	}

	/**
	 * Çekirdek bloklara SeoPro stil varyasyonları.
	 */
	public function block_styles(): void {
		if ( ! function_exists( 'register_block_style' ) ) {
			return;
		}
		register_block_style( 'core/button', [ 'name' => 'seopro-outline', 'label' => __( 'Çerçeveli (SeoPro)', 'seopro' ) ] );
		register_block_style( 'core/quote', [ 'name' => 'seopro-bordered', 'label' => __( 'Kenarlıklı (SeoPro)', 'seopro' ) ] );
		register_block_style( 'core/pullquote', [ 'name' => 'seopro-accent', 'label' => __( 'Vurgu (SeoPro)', 'seopro' ) ] );
		register_block_style( 'core/separator', [ 'name' => 'seopro-dots', 'label' => __( 'Noktalı (SeoPro)', 'seopro' ) ] );
		register_block_style( 'core/group', [ 'name' => 'seopro-card', 'label' => __( 'Kart (SeoPro)', 'seopro' ) ] );
	}

	/**
	 * SeoPro blok desenleri.
	 */
	public function block_patterns(): void {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}
		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category( 'seopro', [ 'label' => __( 'SeoPro', 'seopro' ) ] );
		}

		register_block_pattern( 'seopro/cta', [
			'title'      => __( 'SeoPro: Harekete geçirici (CTA)', 'seopro' ),
			'categories' => [ 'seopro', 'call-to-action' ],
			'content'    => '<!-- wp:group {"className":"seopro-cta","layout":{"type":"constrained"}} -->'
				. '<div class="wp-block-group seopro-cta"><!-- wp:heading {"textAlign":"center"} -->'
				. '<h2 class="wp-block-heading has-text-align-center">' . esc_html__( 'Yazıları kaçırma', 'seopro' ) . '</h2>'
				. '<!-- /wp:heading --><!-- wp:paragraph {"align":"center"} -->'
				. '<p class="has-text-align-center">' . esc_html__( 'En yeni içerikleri ilk sen oku. Birkaç saniyede katıl.', 'seopro' ) . '</p>'
				. '<!-- /wp:paragraph --><!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->'
				. '<div class="wp-block-buttons"><!-- wp:button -->'
				. '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Hemen başla', 'seopro' ) . '</a></div>'
				. '<!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->',
		] );

		register_block_pattern( 'seopro/pullquote', [
			'title'      => __( 'SeoPro: Öne çıkan alıntı', 'seopro' ),
			'categories' => [ 'seopro', 'text' ],
			'content'    => '<!-- wp:pullquote {"className":"is-style-seopro-accent"} -->'
				. '<figure class="wp-block-pullquote is-style-seopro-accent"><blockquote><p>' . esc_html__( 'Buraya vurgulamak istediğiniz çarpıcı cümleyi yazın.', 'seopro' ) . '</p><cite>' . esc_html__( 'Kaynak / Yazar', 'seopro' ) . '</cite></blockquote></figure>'
				. '<!-- /wp:pullquote -->',
		] );

		register_block_pattern( 'seopro/ad-slot', [
			'title'      => __( 'SeoPro: Reklam yuvası', 'seopro' ),
			'categories' => [ 'seopro' ],
			'description'=> __( 'Tema panelindeki içerik-içi #1 reklam bölgesini yazının istediğin yerine yerleştirir.', 'seopro' ),
			'content'    => '<!-- wp:shortcode -->[seopro_ad zone="in1"]<!-- /wp:shortcode -->',
		] );

		register_block_pattern( 'seopro/info', [
			'title'      => __( 'SeoPro: Bilgi kutusu', 'seopro' ),
			'categories' => [ 'seopro', 'text' ],
			'content'    => '<!-- wp:group {"className":"seopro-infobox","layout":{"type":"constrained"}} -->'
				. '<div class="wp-block-group seopro-infobox"><!-- wp:paragraph -->'
				. '<p><strong>' . esc_html__( 'İpucu:', 'seopro' ) . '</strong> ' . esc_html__( 'Okura hatırlatmak istediğin kısa ve faydalı bir not.', 'seopro' ) . '</p>'
				. '<!-- /wp:paragraph --></div><!-- /wp:group -->',
		] );
	}
}
