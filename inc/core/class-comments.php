<?php

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class Comments {

	public static function item( $comment, $args, $depth ): void {
		$tag = ( 'div' === ( $args['style'] ?? 'ol' ) ) ? 'div' : 'li';
		?>
		<<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'seopro-comment' ); ?>>
			<article class="seopro-comment__body">
				<div class="seopro-comment__avatar"><?php echo get_avatar( $comment, 48 ); ?></div>
				<div class="seopro-comment__main">
					<header class="seopro-comment__head">
						<span class="seopro-comment__author"><?php comment_author_link(); ?></span>
						<time class="seopro-comment__date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
							<?php echo esc_html( get_comment_date() ); ?>
						</time>
					</header>

					<?php if ( '0' === $comment->comment_approved ) : ?>
						<p class="seopro-comment__moderation"><?php esc_html_e( 'Yorumunuz onay bekliyor.', 'seopro' ); ?></p>
					<?php endif; ?>

					<div class="seopro-comment__content"><?php comment_text(); ?></div>

					<div class="seopro-comment__actions">
						<?php
						comment_reply_link( array_merge( $args, [
							'depth'     => $depth,
							'max_depth' => $args['max_depth'] ?? 5,
							'reply_text'=> __( 'Yanıtla', 'seopro' ),
						] ) );
						?>
					</div>
				</div>
			</article>
		<?php
	}
}
