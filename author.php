<?php

defined( 'ABSPATH' ) || exit;

get_header();

$seopro_author = get_queried_object();
?>
<div class="seopro-container seopro-layout">
	<div class="seopro-content">
		<header class="seopro-author-header">
			<?php echo get_avatar( $seopro_author->ID ?? 0, 96, '', '', [ 'class' => 'seopro-author-header__avatar' ] ); ?>
			<div>
				<h1 class="seopro-author-header__name"><?php echo esc_html( get_the_author_meta( 'display_name', $seopro_author->ID ?? 0 ) ); ?></h1>
				<?php if ( $bio = get_the_author_meta( 'description', $seopro_author->ID ?? 0 ) ) : ?>
					<p class="seopro-author-header__bio"><?php echo esc_html( $bio ); ?></p>
				<?php endif; ?>
			</div>
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
