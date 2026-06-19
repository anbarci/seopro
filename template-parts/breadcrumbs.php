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
	<ol class="seopro-breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">
		<?php
		$seopro_pos = 1;
		foreach ( $seopro_items as $item ) :
			?>
			<li class="seopro-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<?php if ( ! empty( $item['url'] ) ) : ?>
					<a itemprop="item" href="<?php echo esc_url( $item['url'] ); ?>"><span itemprop="name"><?php echo esc_html( $item['label'] ); ?></span></a>
				<?php else : ?>
					<span itemprop="name"><?php echo esc_html( $item['label'] ); ?></span>
				<?php endif; ?>
				<meta itemprop="position" content="<?php echo esc_attr( (string) $seopro_pos ); ?>" />
			</li>
			<?php
			$seopro_pos++;
		endforeach;
		?>
	</ol>
</nav>
