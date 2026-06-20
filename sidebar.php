<?php

defined( 'ABSPATH' ) || exit;

if ( 'none' === \SeoPro\Core\Options::get( 'seopro_sidebar_position' ) ) {
	return;
}

$seopro_has_ad  = '' !== trim( (string) \SeoPro\Core\Options::get( 'seopro_ad_sidebar' ) ) && \SeoPro\Core\Options::bool( 'seopro_ads_enable' );
$seopro_show_tp = \SeoPro\Core\Options::bool( 'seopro_tabposts_show' );

// Kullanıcı "Sekmeli Yazılar" widget'ını zaten sidebar'a eklediyse otomatik
// render'ı atla — yoksa (panel varsayılanı açık) yükseltmede çift kopya çıkar.
if ( $seopro_show_tp ) {
	$seopro_sb = wp_get_sidebars_widgets();
	foreach ( (array) ( $seopro_sb['sidebar-1'] ?? [] ) as $seopro_wid ) {
		if ( is_string( $seopro_wid ) && 0 === strpos( $seopro_wid, 'seopro_tabbed_posts' ) ) {
			$seopro_show_tp = false;
			break;
		}
	}
}

if ( ! is_active_sidebar( 'sidebar-1' ) && ! $seopro_has_ad && ! $seopro_show_tp ) {
	return;
}
?>
<aside class="seopro-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Yan sütun', 'seopro' ); ?>">
	<?php do_action( 'seopro_sidebar_top' ); ?>
	<?php
	if ( $seopro_show_tp ) :
		?>
		<section class="seopro-widget seopro-widget--tabposts">
			<?php echo \SeoPro\Widgets\TabbedPosts::render_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
		<?php
	endif;
	if ( is_active_sidebar( 'sidebar-1' ) ) {
		dynamic_sidebar( 'sidebar-1' );
	}
	?>
</aside>
