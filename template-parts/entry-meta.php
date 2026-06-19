<?php

defined( 'ABSPATH' ) || exit;

$seopro_reading = \SeoPro\Core\Helpers::reading_time( get_the_content() );
?>
<div class="seopro-entry-meta">
	<span class="seopro-entry-meta__author" itemprop="author" itemscope itemtype="https://schema.org/Person">
		<?php echo get_avatar( get_the_author_meta( 'ID' ), 28, '', '', [ 'class' => 'seopro-entry-meta__avatar' ] ); ?>
		<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" itemprop="url">
			<span itemprop="name"><?php the_author(); ?></span>
		</a>
	</span>
	<time class="seopro-entry-meta__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" itemprop="datePublished">
		<?php echo esc_html( get_the_date() ); ?>
	</time>
	<span class="seopro-entry-meta__reading"><?php echo esc_html( $seopro_reading ); ?></span>
	<?php if ( comments_open() ) : ?>
		<a class="seopro-entry-meta__comments" href="#comments"><?php comments_number( __( 'Yorum yok', 'seopro' ), __( '1 yorum', 'seopro' ), __( '% yorum', 'seopro' ) ); ?></a>
	<?php endif; ?>
</div>
