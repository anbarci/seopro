<?php

namespace SeoPro\Customizer;

use SeoPro\Core\Options;

defined( 'ABSPATH' ) || exit;

class Customizer {

	public function register(): void {
		add_action( 'customize_register', [ $this, 'register_controls' ] );
		add_action( 'customize_preview_init', [ $this, 'preview_js' ] );
	}

	public function register_controls( \WP_Customize_Manager $wp ): void {
		$defaults = Options::defaults();

		$wp->add_panel( 'seopro_panel', [
			'title'    => __( 'SeoPro Tema', 'seopro' ),
			'priority' => 10,
		] );

		$wp->add_section( 'seopro_mode', [
			'title' => __( 'Site Modu', 'seopro' ),
			'panel' => 'seopro_panel',
		] );
		$wp->add_setting( 'seopro_site_mode', [
			'default'           => $defaults['seopro_site_mode'],
			'sanitize_callback' => [ $this, 'sanitize_mode' ],
			'transport'         => 'refresh',
		] );
		$wp->add_control( 'seopro_site_mode', [
			'type'        => 'radio',
			'section'     => 'seopro_mode',
			'label'       => __( 'Bu site nedir?', 'seopro' ),
			'description' => __( 'Blog veya Haber moduna göre tüm yerleşim uyarlanır.', 'seopro' ),
			'choices'     => [
				'blog' => __( 'Blog', 'seopro' ),
				'news' => __( 'Haber Sitesi', 'seopro' ),
			],
		] );
		$wp->add_setting( 'seopro_default_theme', [
			'default'           => $defaults['seopro_default_theme'],
			'sanitize_callback' => [ $this, 'sanitize_theme' ],
		] );
		$wp->add_control( 'seopro_default_theme', [
			'type'    => 'radio',
			'section' => 'seopro_mode',
			'label'   => __( 'Varsayılan görünüm', 'seopro' ),
			'choices' => [
				'light' => __( 'Açık', 'seopro' ),
				'dark'  => __( 'Koyu', 'seopro' ),
			],
		] );

		$wp->add_section( 'seopro_colors', [
			'title' => __( 'Renkler', 'seopro' ),
			'panel' => 'seopro_panel',
		] );
		foreach ( [
			'seopro_brand_primary'   => __( 'Birincil renk', 'seopro' ),
			'seopro_brand_hover'     => __( 'Birincil hover', 'seopro' ),
			'seopro_brand_secondary' => __( 'İkincil renk', 'seopro' ),
			'seopro_bg_base'         => __( 'Arka plan', 'seopro' ),
			'seopro_text_primary'    => __( 'Metin rengi', 'seopro' ),
		] as $id => $label ) {
			$wp->add_setting( $id, [
				'default'           => $defaults[ $id ],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			] );
			$wp->add_control( new \WP_Customize_Color_Control( $wp, $id, [
				'label'   => $label,
				'section' => 'seopro_colors',
			] ) );
		}

		$wp->add_section( 'seopro_typography', [
			'title' => __( 'Tipografi', 'seopro' ),
			'panel' => 'seopro_panel',
		] );
		$fonts = [ 'Work Sans', 'Inter', 'Space Grotesk', 'Sora', 'IBM Plex Sans', 'Roboto', 'system-ui' ];
		foreach ( [
			'seopro_font_head' => __( 'Başlık fontu', 'seopro' ),
			'seopro_font_body' => __( 'Metin fontu', 'seopro' ),
		] as $id => $label ) {
			$wp->add_setting( $id, [
				'default'           => $defaults[ $id ],
				'sanitize_callback' => 'sanitize_text_field',
			] );
			$wp->add_control( $id, [
				'type'    => 'select',
				'section' => 'seopro_typography',
				'label'   => $label,
				'choices' => array_combine( $fonts, $fonts ),
			] );
		}

		$wp->add_section( 'seopro_layout', [
			'title' => __( 'Yerleşim', 'seopro' ),
			'panel' => 'seopro_panel',
		] );
		$wp->add_setting( 'seopro_container_max', [
			'default'           => $defaults['seopro_container_max'],
			'sanitize_callback' => 'absint',
		] );
		$wp->add_control( 'seopro_container_max', [
			'type'    => 'number',
			'section' => 'seopro_layout',
			'label'   => __( 'Konteyner genişliği (px)', 'seopro' ),
		] );
		$wp->add_setting( 'seopro_radius', [
			'default'           => $defaults['seopro_radius'],
			'sanitize_callback' => 'absint',
		] );
		$wp->add_control( 'seopro_radius', [
			'type'    => 'number',
			'section' => 'seopro_layout',
			'label'   => __( 'Köşe yuvarlaklığı (px)', 'seopro' ),
		] );
		$wp->add_setting( 'seopro_sidebar_position', [
			'default'           => $defaults['seopro_sidebar_position'],
			'sanitize_callback' => [ $this, 'sanitize_sidebar' ],
		] );
		$wp->add_control( 'seopro_sidebar_position', [
			'type'    => 'radio',
			'section' => 'seopro_layout',
			'label'   => __( 'Yan sütun konumu', 'seopro' ),
			'choices' => [
				'right' => __( 'Sağ', 'seopro' ),
				'left'  => __( 'Sol', 'seopro' ),
				'none'  => __( 'Yok', 'seopro' ),
			],
		] );
		$wp->add_setting( 'seopro_sticky_header', [
			'default'           => $defaults['seopro_sticky_header'],
			'sanitize_callback' => 'wp_validate_boolean',
		] );
		$wp->add_control( 'seopro_sticky_header', [
			'type'    => 'checkbox',
			'section' => 'seopro_layout',
			'label'   => __( 'Sabit (sticky) header', 'seopro' ),
		] );

		$wp->add_section( 'seopro_performance', [
			'title' => __( 'Performans', 'seopro' ),
			'panel' => 'seopro_panel',
		] );
		foreach ( [
			'seopro_lazyload'      => __( 'Lazy loading', 'seopro' ),
			'seopro_amp_enable'    => __( 'Dahili AMP katmanı', 'seopro' ),
			'seopro_schema_enable' => __( 'Otomatik Schema.org', 'seopro' ),
			'seopro_toc_enable'    => __( 'İçindekiler (içerik tablosu)', 'seopro' ),
		] as $id => $label ) {
			$wp->add_setting( $id, [
				'default'           => $defaults[ $id ],
				'sanitize_callback' => 'wp_validate_boolean',
			] );
			$wp->add_control( $id, [
				'type'    => 'checkbox',
				'section' => 'seopro_performance',
				'label'   => $label,
			] );
		}
		$wp->add_setting( 'seopro_image_quality', [
			'default'           => $defaults['seopro_image_quality'],
			'sanitize_callback' => [ $this, 'sanitize_quality' ],
		] );
		$wp->add_control( 'seopro_image_quality', [
			'type'        => 'number',
			'section'     => 'seopro_performance',
			'label'       => __( 'Görsel kalitesi (JPEG/WebP)', 'seopro' ),
			'description' => __( '1-100 arası. Düşük değer = küçük dosya. Önerilen 78-82. Yalnızca bundan sonra yüklenen/yeniden üretilen görselleri etkiler.', 'seopro' ),
			'input_attrs' => [ 'min' => 1, 'max' => 100, 'step' => 1 ],
		] );

		$wp->add_section( 'seopro_watermark', [
			'title' => __( 'Görsel Damga (Watermark)', 'seopro' ),
			'panel' => 'seopro_panel',
		] );
		$wp->add_setting( 'seopro_watermark_enable', [
			'default'           => $defaults['seopro_watermark_enable'],
			'sanitize_callback' => 'wp_validate_boolean',
		] );
		$wp->add_control( 'seopro_watermark_enable', [
			'type'        => 'checkbox',
			'section'     => 'seopro_watermark',
			'label'       => __( 'Yüklenen görsellere damga ekle', 'seopro' ),
			'description' => __( 'Yalnızca bundan sonra yüklenen görselleri etkiler.', 'seopro' ),
		] );
		$wp->add_setting( 'seopro_watermark_type', [
			'default'           => $defaults['seopro_watermark_type'],
			'sanitize_callback' => [ $this, 'sanitize_wm_type' ],
		] );
		$wp->add_control( 'seopro_watermark_type', [
			'type'    => 'radio',
			'section' => 'seopro_watermark',
			'label'   => __( 'Damga türü', 'seopro' ),
			'choices' => [
				'text' => __( 'Yazı', 'seopro' ),
				'logo' => __( 'Logo (görsel)', 'seopro' ),
			],
		] );
		$wp->add_setting( 'seopro_watermark_text', [
			'default'           => $defaults['seopro_watermark_text'],
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp->add_control( 'seopro_watermark_text', [
			'type'        => 'text',
			'section'     => 'seopro_watermark',
			'label'       => __( 'Damga yazısı', 'seopro' ),
			'description' => __( 'Boş bırakılırsa site adı kullanılır.', 'seopro' ),
		] );
		$wp->add_setting( 'seopro_watermark_logo', [
			'default'           => $defaults['seopro_watermark_logo'],
			'sanitize_callback' => 'absint',
		] );
		$wp->add_control( new \WP_Customize_Media_Control( $wp, 'seopro_watermark_logo', [
			'section'     => 'seopro_watermark',
			'label'       => __( 'Damga logosu', 'seopro' ),
			'mime_type'   => 'image',
			'description' => __( 'Şeffaf PNG önerilir.', 'seopro' ),
		] ) );
		$wp->add_setting( 'seopro_watermark_position', [
			'default'           => $defaults['seopro_watermark_position'],
			'sanitize_callback' => [ $this, 'sanitize_wm_position' ],
		] );
		$wp->add_control( 'seopro_watermark_position', [
			'type'    => 'select',
			'section' => 'seopro_watermark',
			'label'   => __( 'Konum', 'seopro' ),
			'choices' => [
				'top-left'     => __( 'Sol üst', 'seopro' ),
				'top-right'    => __( 'Sağ üst', 'seopro' ),
				'bottom-left'  => __( 'Sol alt', 'seopro' ),
				'bottom-right' => __( 'Sağ alt', 'seopro' ),
				'center'       => __( 'Orta', 'seopro' ),
			],
		] );
		$wp->add_setting( 'seopro_watermark_opacity', [
			'default'           => $defaults['seopro_watermark_opacity'],
			'sanitize_callback' => 'absint',
		] );
		$wp->add_control( 'seopro_watermark_opacity', [
			'type'        => 'number',
			'section'     => 'seopro_watermark',
			'label'       => __( 'Opaklık (%)', 'seopro' ),
			'input_attrs' => [ 'min' => 0, 'max' => 100, 'step' => 5 ],
		] );
		$wp->add_setting( 'seopro_watermark_size', [
			'default'           => $defaults['seopro_watermark_size'],
			'sanitize_callback' => 'absint',
		] );
		$wp->add_control( 'seopro_watermark_size', [
			'type'        => 'number',
			'section'     => 'seopro_watermark',
			'label'       => __( 'Damga boyutu', 'seopro' ),
			'description' => __( 'Yazı ve logo boyutunu ölçekler (varsayılan 18; logo için görsel genişliğinin %\'si).', 'seopro' ),
			'input_attrs' => [ 'min' => 5, 'max' => 60, 'step' => 1 ],
		] );

		$wp->add_section( 'seopro_social', [
			'title' => __( 'Sosyal Medya', 'seopro' ),
			'panel' => 'seopro_panel',
		] );
		foreach ( [
			'seopro_social_x'         => 'X',
			'seopro_social_facebook'  => 'Facebook',
			'seopro_social_instagram' => 'Instagram',
			'seopro_social_youtube'   => 'YouTube',
			'seopro_social_linkedin'  => 'LinkedIn',
		] as $id => $label ) {
			$wp->add_setting( $id, [
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			] );
			$wp->add_control( $id, [
				'type'    => 'url',
				'section' => 'seopro_social',
				'label'   => $label,
			] );
		}

		$wp->add_section( 'seopro_footer', [
			'title' => __( 'Footer', 'seopro' ),
			'panel' => 'seopro_panel',
		] );
		$wp->add_setting( 'seopro_footer_text', [
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
		] );
		$wp->add_control( 'seopro_footer_text', [
			'type'    => 'textarea',
			'section' => 'seopro_footer',
			'label'   => __( 'Telif metni', 'seopro' ),
		] );
	}

	public function sanitize_mode( $value ): string {
		return in_array( $value, [ 'blog', 'news' ], true ) ? $value : 'blog';
	}

	public function sanitize_theme( $value ): string {
		return in_array( $value, [ 'light', 'dark' ], true ) ? $value : 'light';
	}

	public function sanitize_sidebar( $value ): string {
		return in_array( $value, [ 'right', 'left', 'none' ], true ) ? $value : 'right';
	}

	public function sanitize_quality( $value ): int {
		$value = absint( $value );
		if ( $value < 1 ) {
			return 82;
		}
		return min( $value, 100 );
	}

	public function sanitize_wm_type( $value ): string {
		return in_array( $value, [ 'text', 'logo' ], true ) ? $value : 'text';
	}

	public function sanitize_wm_position( $value ): string {
		return in_array( $value, [ 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'center' ], true ) ? $value : 'bottom-right';
	}

	public function preview_js(): void {
		wp_enqueue_script(
			'seopro-customize-preview',
			SEOPRO_URI . '/assets/js/customize-preview.js',
			[ 'customize-preview' ],
			SEOPRO_VERSION,
			true
		);
	}
}
