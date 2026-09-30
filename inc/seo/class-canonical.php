<?php

namespace SeoPro\Seo;

defined( 'ABSPATH' ) || exit;

class Canonical {

	public function register(): void {
		if ( $this->seo_plugin_active() ) {
			return;
		}
		// Alan, PostMeta'daki "SEO (SeoPro)" kutusundan girilir.
		add_filter( 'get_canonical_url', [ $this, 'filter' ], 10, 2 );
	}

	private function seo_plugin_active(): bool {
		return \SeoPro\Core\Helpers::seo_plugin_active();
	}

	public function filter( $canonical_url, $post ) {
		$custom = get_post_meta( $post->ID, '_seopro_canonical', true );
		return $custom ? esc_url_raw( $custom ) : $canonical_url;
	}
}
