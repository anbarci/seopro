<?php
/**
 * Template Name: Yazarlar
 */

defined( 'ABSPATH' ) || exit;

get_header();

$seopro_authors = get_users( [
	'who'                 => 'authors',
	'has_published_posts' => [ 'post' ],
	'orderby'             => 'post_count',
	'order'               => 'DESC',
] );
?>
<div class="seopro-container seopro-authors-page">
	<?php while ( have_posts() ) : the_post(); ?>
		<h1 class="seopro-page__title"><?php the_title(); ?></h1>
		<div class="seopro-page__content"><?php the_content(); ?></div>
	<?php endwhile; ?>

	<div class="seopro-authors-grid">
		<?php foreach ( $seopro_authors as $seopro_author ) : ?>
			<article class="seopro-author-tile">
				<a href="<?php echo esc_url( get_author_posts_url( $seopro_author->ID ) ); ?>">
					<?php echo get_avatar( $seopro_author->ID, 80, '', '', [ 'class' => 'seopro-author-tile__avatar' ] ); ?>
					<span class="seopro-author-tile__name"><?php echo esc_html( $seopro_author->display_name ); ?></span>
					<span class="seopro-author-tile__count">
						<?php
						printf(
							/* translators: %d: post count. */
							esc_html__( '%d içerik', 'seopro' ),
							(int) count_user_posts( $seopro_author->ID, 'post', true )
						);
						?>
					</span>
				</a>
			</article>
		<?php endforeach; ?>
	</div>
</div>
<?php
get_footer();
