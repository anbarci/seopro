<?php

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="seopro-container seopro-layout">
	<div class="seopro-content">
		<header class="seopro-archive-header">
			<?php the_archive_title( '<h1 class="seopro-archive-title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="seopro-archive-desc">', '</div>' ); ?>
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
