<?php
/**
 * Ana fallback şablonu (post listesi).
 *
 * @package SeoPro
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="seopro-container seopro-layout">
	<div class="seopro-content">
		<?php if ( have_posts() ) : ?>

			<?php if ( is_home() && ! is_front_page() ) : ?>
				<h1 class="seopro-archive-title screen-reader-text"><?php single_post_title(); ?></h1>
			<?php endif; ?>

			<div class="seopro-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				[
					'mid_size'           => 1,
					'prev_text'          => __( 'Önceki', 'seopro' ),
					'next_text'          => __( 'Sonraki', 'seopro' ),
					'screen_reader_text' => __( 'Sayfalar arası gezinme', 'seopro' ),
					'aria_label'         => __( 'Sayfalar', 'seopro' ),
				]
			);
			?>

		<?php else : ?>

			<article class="seopro-card seopro-card--empty">
				<h2 class="seopro-card__title"><?php esc_html_e( 'İçerik bulunamadı.', 'seopro' ); ?></h2>
				<p><?php esc_html_e( 'Aradığınız içerik mevcut değil. Arama yapmayı deneyin.', 'seopro' ); ?></p>
				<?php get_search_form(); ?>
			</article>

		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
