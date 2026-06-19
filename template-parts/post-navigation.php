<?php

defined( 'ABSPATH' ) || exit;

$seopro_prev = get_previous_post();
$seopro_next = get_next_post();
if ( ! $seopro_prev && ! $seopro_next ) {
	return;
}
?>
<nav class="seopro-postnav" aria-label="<?php esc_attr_e( 'Yazılar arası gezinme', 'seopro' ); ?>">
	<?php if ( $seopro_prev ) : ?>
		<a class="seopro-postnav__link seopro-postnav__link--prev" href="<?php echo esc_url( get_permalink( $seopro_prev ) ); ?>" rel="prev">
			<?php if ( has_post_thumbnail( $seopro_prev ) ) : ?>
				<span class="seopro-postnav__thumb"><?php echo get_the_post_thumbnail( $seopro_prev, 'seopro-thumb', [ 'loading' => 'lazy', 'alt' => '' ] ); ?></span>
			<?php endif; ?>
			<span class="seopro-postnav__text">
				<span class="seopro-postnav__label"><?php esc_html_e( '‹ Önceki yazı', 'seopro' ); ?></span>
				<span class="seopro-postnav__title"><?php echo esc_html( get_the_title( $seopro_prev ) ); ?></span>
			</span>
		</a>
	<?php else : ?>
		<span></span>
	<?php endif; ?>

	<?php if ( $seopro_next ) : ?>
		<a class="seopro-postnav__link seopro-postnav__link--next" href="<?php echo esc_url( get_permalink( $seopro_next ) ); ?>" rel="next">
			<span class="seopro-postnav__text">
				<span class="seopro-postnav__label"><?php esc_html_e( 'Sonraki yazı ›', 'seopro' ); ?></span>
				<span class="seopro-postnav__title"><?php echo esc_html( get_the_title( $seopro_next ) ); ?></span>
			</span>
			<?php if ( has_post_thumbnail( $seopro_next ) ) : ?>
				<span class="seopro-postnav__thumb"><?php echo get_the_post_thumbnail( $seopro_next, 'seopro-thumb', [ 'loading' => 'lazy', 'alt' => '' ] ); ?></span>
			<?php endif; ?>
		</a>
	<?php endif; ?>
</nav>
