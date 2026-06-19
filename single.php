<?php

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="seopro-container seopro-layout seopro-layout--single">
	<div class="seopro-content">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'single' );

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
