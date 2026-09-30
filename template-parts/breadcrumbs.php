<?php

defined( 'ABSPATH' ) || exit;

if ( is_front_page() ) {
	return;
}
$seopro_items = \SeoPro\Core\Helpers::breadcrumb_items();
if ( empty( $seopro_items ) ) {
	return;
}
?>
<nav class="seopro-breadcrumbs" aria-label="<?php esc_attr_e( 'İçerik haritası', 'seopro' ); ?>">
	<ol class="seopro-breadcrumbs__list">
		<?php
		foreach ( $seopro_items as $item ) :
			?>
			<li class="seopro-breadcrumbs__item">
				<?php if ( ! empty( $item['url'] ) ) : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>"><span><?php echo esc_html( $item['label'] ); ?></span></a>
				<?php else : ?>
					<span><?php echo esc_html( $item['label'] ); ?></span>
				<?php endif; ?>
			</li>
			<?php
		endforeach;
		?>
	</ol>
</nav>
