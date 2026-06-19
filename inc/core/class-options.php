<?php

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class Options {

	public static function defaults(): array {
		return [
			'seopro_site_mode'       => 'blog',
			'seopro_brand_primary'   => '#4285F4',
			'seopro_brand_hover'     => '#3367D6',
			'seopro_brand_secondary' => '#344955',
			'seopro_bg_base'         => '#F4F5FA',
			'seopro_text_primary'    => '#344955',
			'seopro_container_max'   => 1240,
			'seopro_radius'          => 6,
			'seopro_font_body'       => 'Work Sans',
			'seopro_font_head'       => 'Work Sans',
			'seopro_default_theme'   => 'light',
			'seopro_sidebar_position'=> 'right',
			'seopro_sticky_header'   => true,
			'seopro_lazyload'        => true,
			'seopro_toc_enable'      => true,
			'seopro_amp_enable'      => true,
			'seopro_schema_enable'   => true,
			'seopro_schema_type'     => 'auto',
			'seopro_schema_org_name' => '',
			'seopro_schema_org_logo' => 0,
			'seopro_og_default'      => 0,
			'seopro_excerpt_length'  => 24,
			'seopro_image_quality'   => 82,
			'seopro_watermark_enable'   => false,
			'seopro_watermark_type'     => 'text',
			'seopro_watermark_text'     => '',
			'seopro_watermark_logo'     => 0,
			'seopro_watermark_position' => 'bottom-right',
			'seopro_watermark_opacity'  => 70,
			'seopro_watermark_size'     => 18,
			'seopro_footer_text'     => '',
			'seopro_flipbar_enable'  => true,
			'seopro_flipbar_source'  => 'latest',
			'seopro_flipbar_count'   => 6,
			'seopro_tabposts_style'  => 'cards',
			'seopro_tabposts_popular'=> true,
			'seopro_tabposts_latest' => true,
			'seopro_tabposts_random' => true,
			'seopro_ads_enable'      => true,
			'seopro_ads_lazy'        => true,
			'seopro_ad_header'       => '',
			'seopro_ad_before'       => '',
			'seopro_ad_in1'          => '',
			'seopro_ad_in1_after'    => 2,
			'seopro_ad_in2'          => '',
			'seopro_ad_in2_after'    => 6,
			'seopro_ad_after'        => '',
			'seopro_ad_sidebar'      => '',
			'seopro_ad_mobile'       => '',
			'seopro_amp_ads_enable'  => false,
			'seopro_amp_ad_client'   => '',
			'seopro_amp_ad_mode'     => 'auto',
			'seopro_amp_slot_top'    => '',
			'seopro_amp_slot_bottom' => '',
			'seopro_social_x'        => '',
			'seopro_social_facebook' => '',
			'seopro_social_instagram'=> '',
			'seopro_social_youtube'  => '',
			'seopro_social_linkedin' => '',
		];
	}

	public static function get( string $key ) {
		$defaults = self::defaults();
		$default  = $defaults[ $key ] ?? '';
		return get_theme_mod( $key, $default );
	}

	public static function bool( string $key ): bool {
		return (bool) self::get( $key );
	}

	public static function social_links(): array {
		$map = [
			'x'         => 'X',
			'facebook'  => 'Facebook',
			'instagram' => 'Instagram',
			'youtube'   => 'YouTube',
			'linkedin'  => 'LinkedIn',
		];
		$out = [];
		foreach ( $map as $key => $label ) {
			$url = self::get( 'seopro_social_' . $key );
			if ( $url ) {
				$out[ $key ] = [
					'label' => $label,
					'url'   => $url,
				];
			}
		}
		return $out;
	}
}
