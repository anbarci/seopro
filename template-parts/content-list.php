<?php
/**
 * Resimsiz liste görünümü (kategori/arşiv için).
 *
 * @package SeoPro
 */

defined( 'ABSPATH' ) || exit;

$seopro_cats   = get_the_category();
$seopro_color  = ! empty( $seopro_cats ) ? \SeoPro\Core\Helpers::cat_color( $seopro_cats[0]->term_id ) : 'var(--brand-primary)';
$seopro_ink    = ! empty( $seopro_cats ) ? \SeoPro\Core\Helpers::cat_ink( $seopro_cats[0]->term_id ) : 'var(--brand-primary)';
$seopro_author = get_the_author();
?>
<article <?php post_class( 'seopro-listpost' ); ?> style="--cat-color: <?php echo esc_attr( $seopro_color ); ?>; --cat-ink: <?php echo esc_attr( $seopro_ink ); ?>">
	<div class="seopro-listpost__date">
		<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
	</div>

	<div class="seopro-listpost__body">
		<h2 class="seopro-listpost__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

		<p class="seopro-listpost__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30, '…' ) ); ?></p>

		<?php if ( $seopro_author ) : ?>
			<div class="seopro-listpost__meta"><?php echo esc_html( $seopro_author ); ?></div>
		<?php endif; ?>
	</div>
</article>
