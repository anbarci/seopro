<?php

defined( 'ABSPATH' ) || exit;

$seopro_bio = get_the_author_meta( 'description' );
if ( ! $seopro_bio ) {
	return;
}
?>
<aside class="seopro-author-bio" aria-label="<?php esc_attr_e( 'Yazar hakkında', 'seopro' ); ?>">
	<?php echo get_avatar( get_the_author_meta( 'ID' ), 64, '', '', [ 'class' => 'seopro-author-bio__avatar' ] ); ?>
	<div class="seopro-author-bio__body">
		<a class="seopro-author-bio__name" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a>
		<p class="seopro-author-bio__text"><?php echo esc_html( $seopro_bio ); ?></p>
	</div>
</aside>
