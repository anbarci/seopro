<?php

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="seopro-comments" aria-label="<?php esc_attr_e( 'Yorumlar', 'seopro' ); ?>">
	<?php if ( have_comments() ) : ?>
		<h2 class="seopro-comments__title">
			<?php
			$seopro_count = get_comments_number();
			printf(
				/* translators: %s: comment count. */
				esc_html( _n( '%s Yorum', '%s Yorum', $seopro_count, 'seopro' ) ),
				'<span>' . esc_html( number_format_i18n( $seopro_count ) ) . '</span>'
			);
			?>
		</h2>

		<ol class="seopro-comments__list">
			<?php
			wp_list_comments( [
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 48,
				'callback'    => [ '\SeoPro\Core\Comments', 'item' ],
			] );
			?>
		</ol>

		<?php
		the_comments_pagination( [
			'prev_text' => __( '‹ Önceki', 'seopro' ),
			'next_text' => __( 'Sonraki ›', 'seopro' ),
		] );
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="seopro-comments__closed"><?php esc_html_e( 'Yorumlar kapalı.', 'seopro' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	$seopro_cf_args = [
		'class_form'           => 'seopro-comment-form',
		'class_submit'         => 'seopro-button',
		'title_reply'          => __( 'Yorum yaz', 'seopro' ),
		'title_reply_before'   => '<h3 id="reply-title" class="seopro-comment-form__title screen-reader-text">',
		'title_reply_after'    => '</h3>',
		'comment_notes_before' => '',
	];
	if ( comments_open() ) :
		?>
		<details class="seopro-commentform">
			<summary class="seopro-commentform__head">
				<span class="seopro-commentform__icon"><?php echo \SeoPro\Core\Helpers::icon( 'comment' ); // phpcs:ignore ?></span>
				<span class="seopro-commentform__label"><?php esc_html_e( 'Yorum Yaz', 'seopro' ); ?></span>
				<span class="seopro-commentform__toggle" aria-hidden="true"><?php echo \SeoPro\Core\Helpers::icon( 'plus' ); // phpcs:ignore ?></span>
			</summary>
			<div class="seopro-commentform__body">
				<?php comment_form( $seopro_cf_args ); ?>
			</div>
		</details>
		<?php
	else :
		comment_form( $seopro_cf_args );
	endif;
	?>
</section>
