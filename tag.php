<?php
/**
 * Etiket arşivi şablonu.
 *
 * @package SeoPro
 */

defined( 'ABSPATH' ) || exit;

get_header();

$seopro_tag   = get_queried_object();
$seopro_count = ( $seopro_tag && isset( $seopro_tag->count ) ) ? (int) $seopro_tag->count : 0;
?>
<div class="seopro-container seopro-layout">
	<div class="seopro-content">
		<header class="seopro-archive-header seopro-archive-header--tag">
			<span class="seopro-archive-kicker"><?php esc_html_e( 'Etiket', 'seopro' ); ?></span>
			<h1 class="seopro-archive-title">#<?php single_tag_title(); ?></h1>
			<?php the_archive_description( '<div class="seopro-archive-desc">', '</div>' ); ?>
			<?php if ( $seopro_count ) : ?>
				<p class="seopro-archive-count">
					<?php
					/* translators: %s: number of posts. */
					printf( esc_html( _n( '%s yazı', '%s yazı', $seopro_count, 'seopro' ) ), esc_html( number_format_i18n( $seopro_count ) ) );
					?>
				</p>
			<?php endif; ?>
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
