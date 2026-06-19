<?php
/**
 * Template Name: Tam Genişlik
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="seopro-container seopro-fullwidth">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'seopro-page' ); ?>>
			<h1 class="seopro-page__title"><?php the_title(); ?></h1>
			<div class="seopro-page__content"><?php the_content(); ?></div>
		</article>
		<?php
	endwhile;
	?>
</div>
<?php
get_footer();
