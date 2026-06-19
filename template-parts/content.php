<?php
/**
 * Liste görünümü için yazı kartı.
 *
 * @package SeoPro
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'seopro-card' ); ?> aria-labelledby="post-<?php the_ID(); ?>-title">

	<a class="seopro-card__media<?php echo has_post_thumbnail() ? '' : ' seopro-card__media--ph'; ?>" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'seopro-card', [ 'class' => 'seopro-card__img', 'loading' => 'lazy' ] );
		}
		?>
	</a>

	<div class="seopro-card__body">
		<?php
		$seopro_cats = get_the_category_list( ', ' );
		if ( $seopro_cats ) :
			?>
			<div class="seopro-card__cats"><?php echo wp_kses_post( $seopro_cats ); ?></div>
		<?php endif; ?>

		<h2 class="seopro-card__title" id="post-<?php the_ID(); ?>-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<div class="seopro-card__meta">
			<span class="seopro-card__author"><?php echo esc_html( get_the_author() ); ?></span>
			<time class="seopro-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>
		</div>

		<p class="seopro-card__excerpt">
			<?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?>
		</p>
	</div>
</article>
