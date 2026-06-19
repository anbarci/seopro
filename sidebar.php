<?php

defined( 'ABSPATH' ) || exit;

if ( 'none' === \SeoPro\Core\Options::get( 'seopro_sidebar_position' ) ) {
	return;
}

$seopro_has_ad = '' !== trim( (string) \SeoPro\Core\Options::get( 'seopro_ad_sidebar' ) ) && \SeoPro\Core\Options::bool( 'seopro_ads_enable' );

if ( ! is_active_sidebar( 'sidebar-1' ) && ! $seopro_has_ad ) {
	return;
}
?>
<aside class="seopro-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Yan sütun', 'seopro' ); ?>">
	<?php do_action( 'seopro_sidebar_top' ); ?>
	<?php
	if ( is_active_sidebar( 'sidebar-1' ) ) {
		dynamic_sidebar( 'sidebar-1' );
	}
	?>
</aside>
