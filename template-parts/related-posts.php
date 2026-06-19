<?php

defined( 'ABSPATH' ) || exit;

$seopro_cats = wp_get_post_categories( get_the_ID() );
if ( empty( $seopro_cats ) ) {
	return;
}
$seopro_limit = \SeoPro\Core\Mode::is_news() ? 6 : 3;
$seopro_related = new WP_Query( [
	'category__in'        => $seopro_cats,
	'post__not_in'        => [ get_the_ID() ],
	'posts_per_page'      => $seopro_limit,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
] );
if ( ! $seopro_related->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>
<section class="seopro-related" aria-label="<?php esc_attr_e( 'İlgili içerikler', 'seopro' ); ?>">
	<h2 class="seopro-related__title"><?php echo \SeoPro\Core\Mode::is_news() ? esc_html__( 'İlgili Haberler', 'seopro' ) : esc_html__( 'İlgili Yazılar', 'seopro' ); ?></h2>
	<div class="seopro-related__grid">
		<?php
		while ( $seopro_related->have_posts() ) :
			$seopro_related->the_post();
			?>
			<article class="seopro-related__card">
				<a class="seopro-related__link" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'seopro-thumb', [ 'class' => 'seopro-related__img', 'loading' => 'lazy' ] ); ?>
					<?php endif; ?>
					<h3 class="seopro-related__name"><?php the_title(); ?></h3>
				</a>
			</article>
			<?php
		endwhile;
		?>
	</div>
</section>
<?php
wp_reset_postdata();
