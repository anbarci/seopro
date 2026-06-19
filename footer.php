<?php

defined( 'ABSPATH' ) || exit;

$seopro_footer_text = \SeoPro\Core\Options::get( 'seopro_footer_text' );
$seopro_socials     = \SeoPro\Core\Options::social_links();
?>
</main><!-- #seopro-main -->

<footer class="seopro-site-footer" role="contentinfo">
	<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
		<div class="seopro-container seopro-footer-widgets">
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
					<div class="seopro-footer-col"><?php dynamic_sidebar( 'footer-' . $i ); ?></div>
				<?php endif; ?>
			<?php endfor; ?>
		</div>
	<?php endif; ?>

	<div class="seopro-container seopro-site-footer__inner">
		<?php if ( $seopro_socials ) : ?>
			<ul class="seopro-social" aria-label="<?php esc_attr_e( 'Sosyal medya', 'seopro' ); ?>">
				<?php foreach ( $seopro_socials as $social ) : ?>
					<li><a href="<?php echo esc_url( $social['url'] ); ?>" rel="noopener noreferrer me" target="_blank"><?php echo esc_html( $social['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php
		if ( has_nav_menu( 'footer' ) ) {
			wp_nav_menu( [
				'theme_location'       => 'footer',
				'container'            => 'nav',
				'container_class'      => 'seopro-footer-nav',
				'container_aria_label' => __( 'Footer menü', 'seopro' ),
				'menu_class'           => 'seopro-footer-nav__list',
				'fallback_cb'          => false,
				'depth'                => 1,
			] );
		}
		?>
	</div>

	<div class="seopro-footerbar">
		<div class="seopro-container seopro-footerbar__inner">
			<div class="seopro-copyright">
				<?php
				if ( $seopro_footer_text ) {
					echo wp_kses_post( $seopro_footer_text );
				} else {
					printf( '&copy; %s %s &middot; %s', esc_html( wp_date( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ), esc_html__( 'Tüm hakları saklıdır.', 'seopro' ) );
				}
				?>
			</div>
		</div>
	</div>
</footer>

<button type="button" class="seopro-totop" data-seopro-totop aria-label="<?php esc_attr_e( 'Yukarı çık', 'seopro' ); ?>"><?php echo \SeoPro\Core\Helpers::icon( 'arrow-up' ); // phpcs:ignore ?></button>

<?php wp_footer(); ?>
<!-- SeoPro teması - Telif (c) 2026 hazermedya.com (Hikmet Anbarcı). Haklar & iletişim: https://hazermedya.com -->
</body>
</html>
