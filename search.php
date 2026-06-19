<?php

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="seopro-container seopro-layout">
	<div class="seopro-content">
		<header class="seopro-archive-header">
			<h1 class="seopro-archive-title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( '“%s” için sonuçlar', 'seopro' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
			<?php get_search_form(); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="seopro-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination( [
				'prev_text'  => __( 'Önceki', 'seopro' ),
				'next_text'  => __( 'Sonraki', 'seopro' ),
				'aria_label' => __( 'Sayfalar', 'seopro' ),
			] );
			?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
