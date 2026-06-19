<?php

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'seopro-page' ); ?>>
	<header class="seopro-page__header">
		<h1 class="seopro-page__title"><?php the_title(); ?></h1>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="seopro-page__media">
			<?php the_post_thumbnail( 'seopro-hero', [ 'class' => 'seopro-page__img', 'loading' => 'eager' ] ); ?>
		</figure>
	<?php endif; ?>

	<div class="seopro-page__content">
		<?php
		the_content();
		wp_link_pages( [
			'before' => '<nav class="seopro-page-links" aria-label="' . esc_attr__( 'Sayfa bağlantıları', 'seopro' ) . '">',
			'after'  => '</nav>',
		] );
		?>
	</div>
</article>
