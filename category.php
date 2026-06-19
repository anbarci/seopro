<?php

defined( 'ABSPATH' ) || exit;

get_header();

$seopro_term   = get_queried_object();
$seopro_accent = ( $seopro_term && isset( $seopro_term->term_id ) )
	? \SeoPro\Core\Helpers::cat_color( $seopro_term->term_id )
	: 'var(--brand-primary)';
?>
<div class="seopro-container seopro-layout">
	<div class="seopro-content">
		<header class="seopro-archive-header" style="--cat-accent: <?php echo esc_attr( $seopro_accent ); ?>">
			<h1 class="seopro-archive-title"><?php single_cat_title(); ?></h1>
			<?php the_archive_description( '<div class="seopro-archive-desc">', '</div>' ); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="seopro-archive-list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'list' );
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
