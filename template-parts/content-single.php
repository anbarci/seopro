<?php

defined( 'ABSPATH' ) || exit;
?>
<div class="seopro-reading-progress" data-seopro-progress aria-hidden="true"></div>
<article <?php post_class( 'seopro-single' ); ?> itemscope itemtype="https://schema.org/Article">
	<?php get_template_part( 'template-parts/breadcrumbs' ); ?>

	<header class="seopro-single__header">
		<?php
		$seopro_cats = get_the_category_list( ', ' );
		if ( $seopro_cats ) :
			?>
			<div class="seopro-single__cats"><?php echo wp_kses_post( $seopro_cats ); ?></div>
		<?php endif; ?>

		<h1 class="seopro-single__title" itemprop="headline"><?php the_title(); ?></h1>

		<?php get_template_part( 'template-parts/entry-meta' ); ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="seopro-single__media">
			<?php
			the_post_thumbnail( 'seopro-hero', [
				'class'         => 'seopro-single__img',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'itemprop'      => 'image',
			] );
			?>
		</figure>
	<?php endif; ?>

	<?php
	$seopro_url   = rawurlencode( get_permalink() );
	$seopro_title = rawurlencode( get_the_title() );
	$seopro_img   = has_post_thumbnail() ? rawurlencode( (string) get_the_post_thumbnail_url( get_the_ID(), 'full' ) ) : '';
	$seopro_share = [
		'x'        => [ 'X', '#0F1419', 'https://twitter.com/intent/tweet?url=' . $seopro_url . '&text=' . $seopro_title ],
		'facebook' => [ 'Facebook', '#1877F2', 'https://www.facebook.com/sharer/sharer.php?u=' . $seopro_url ],
		'whatsapp' => [ 'WhatsApp', '#25D366', 'https://api.whatsapp.com/send?text=' . $seopro_title . '%20' . $seopro_url ],
		'telegram' => [ 'Telegram', '#229ED9', 'https://t.me/share/url?url=' . $seopro_url . '&text=' . $seopro_title ],
		'linkedin' => [ 'LinkedIn', '#0A66C2', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $seopro_url ],
		'reddit'    => [ 'Reddit', '#FF4500', 'https://www.reddit.com/submit?url=' . $seopro_url . '&title=' . $seopro_title ],
		'pinterest' => [ 'Pinterest', '#E60023', 'https://pinterest.com/pin/create/button/?url=' . $seopro_url . '&media=' . $seopro_img . '&description=' . $seopro_title ],
		'email'     => [ __( 'E-posta', 'seopro' ), '#64748B', 'mailto:?subject=' . $seopro_title . '&body=' . $seopro_url ],
	];
	$seopro_ask = __( 'Bu yazıyı özetle ve en önemli noktalarını madde madde çıkar:', 'seopro' ) . ' ' . get_permalink();
	$seopro_q   = rawurlencode( $seopro_ask );
	// Her servis URL'den prompt almıyor. Claude (2025-10) ve Copilot (2026-01)
	// güvenlik nedeniyle URL-prefill'i kaldırdı; Gemini hiç desteklemedi; Grok'ta
	// güvenilir parametre yok. İki mod:
	//   'url'  => prompt bağlantıyla gider (ChatGPT, Perplexity) — tek tık.
	//   'copy' => prompt panoya kopyalanır + sohbet açılır, kullanıcı yapıştırır.
	$seopro_ai = [
		'ChatGPT'    => [ '#10A37F', 'url',  'https://chatgpt.com/?q=' . $seopro_q ],
		'Perplexity' => [ '#20808D', 'url',  'https://www.perplexity.ai/search?q=' . $seopro_q ],
		'Claude'     => [ '#D97757', 'copy', 'https://claude.ai/new' ],
		'Gemini'     => [ '#4285F4', 'copy', 'https://gemini.google.com/app' ],
		'Grok'       => [ '#111111', 'copy', 'https://grok.com/' ],
		'Copilot'    => [ '#0A6CFF', 'copy', 'https://copilot.microsoft.com/' ],
	];
	?>
	<div class="seopro-actions">
		<button type="button" class="seopro-action-btn" data-seopro-modal-open="share" aria-haspopup="dialog"><?php echo \SeoPro\Core\Helpers::icon( 'share' ); // phpcs:ignore ?><span class="seopro-action-btn__lg"><?php esc_html_e( 'Bu yazıyı paylaş', 'seopro' ); ?></span><span class="seopro-action-btn__sm"><?php esc_html_e( 'Paylaş', 'seopro' ); ?></span></button>
		<div class="seopro-fontsize" role="group" aria-label="<?php esc_attr_e( 'Yazı boyutu', 'seopro' ); ?>">
			<button type="button" class="seopro-fontsize__btn seopro-fontsize__btn--down" data-seopro-font="down" aria-label="<?php esc_attr_e( 'Yazıyı küçült', 'seopro' ); ?>">A−</button>
			<button type="button" class="seopro-fontsize__btn seopro-fontsize__btn--up" data-seopro-font="up" aria-label="<?php esc_attr_e( 'Yazıyı büyült', 'seopro' ); ?>">A+</button>
		</div>
		<button type="button" class="seopro-action-btn seopro-action-btn--ai" data-seopro-modal-open="askai" aria-haspopup="dialog"><?php echo \SeoPro\Core\Helpers::icon( 'sparkle' ); // phpcs:ignore ?><span class="seopro-action-btn__lg"><?php esc_html_e( 'Yapay zekaya sor', 'seopro' ); ?></span><span class="seopro-action-btn__sm"><?php esc_html_e( 'AI Sor', 'seopro' ); ?></span></button>
	</div>

	<div class="seopro-modal" data-seopro-modal="share" hidden>
		<div class="seopro-modal__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Bu yazıyı paylaş', 'seopro' ); ?>">
			<button type="button" class="seopro-modal__close" data-seopro-modal-close aria-label="<?php esc_attr_e( 'Kapat', 'seopro' ); ?>"><?php echo \SeoPro\Core\Helpers::icon( 'close' ); // phpcs:ignore ?></button>
			<span class="seopro-modal__title"><?php esc_html_e( 'Bu yazıyı paylaş', 'seopro' ); ?></span>
			<div class="seopro-share__buttons">
				<?php foreach ( $seopro_share as $seopro_key => $seopro_p ) : ?>
					<a class="seopro-share__btn seopro-share__btn--<?php echo esc_attr( $seopro_key ); ?>" style="--share: <?php echo esc_attr( $seopro_p[1] ); ?>" href="<?php echo esc_url( $seopro_p[2] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $seopro_p[0] ); ?>"><?php echo \SeoPro\Core\Helpers::social_icon( $seopro_key ); // phpcs:ignore ?></a>
				<?php endforeach; ?>
				<button type="button" class="seopro-share__btn seopro-share__btn--copy" data-seopro-copy="<?php echo esc_url( get_permalink() ); ?>" aria-label="<?php esc_attr_e( 'Bağlantıyı kopyala', 'seopro' ); ?>"><?php echo \SeoPro\Core\Helpers::social_icon( 'copy' ); // phpcs:ignore ?></button>
			</div>
		</div>
	</div>

	<div class="seopro-modal" data-seopro-modal="askai" hidden>
		<div class="seopro-modal__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Yapay zekaya sor', 'seopro' ); ?>">
			<button type="button" class="seopro-modal__close" data-seopro-modal-close aria-label="<?php esc_attr_e( 'Kapat', 'seopro' ); ?>"><?php echo \SeoPro\Core\Helpers::icon( 'close' ); // phpcs:ignore ?></button>
			<span class="seopro-modal__title"><?php esc_html_e( 'Yapay zekaya sor', 'seopro' ); ?></span>
			<p class="seopro-modal__desc"><?php esc_html_e( 'Bu yazının özetini veya analizini seçtiğin yapay zekadan al.', 'seopro' ); ?></p>
			<div class="seopro-ai-list">
				<?php foreach ( $seopro_ai as $seopro_ai_name => $seopro_ai_p ) : ?>
					<?php $seopro_ai_copy = ( 'copy' === $seopro_ai_p[1] ); ?>
					<a class="seopro-ai<?php echo $seopro_ai_copy ? ' seopro-ai--copy' : ''; ?>" style="--ai: <?php echo esc_attr( $seopro_ai_p[0] ); ?>" href="<?php echo esc_url( $seopro_ai_p[2] ); ?>" target="_blank" rel="noopener nofollow"<?php echo $seopro_ai_copy ? ' data-seopro-ai-copy="' . esc_attr( $seopro_ask ) . '"' : ''; ?>>
						<span class="seopro-ai__icon"><?php echo \SeoPro\Core\Helpers::icon( $seopro_ai_copy ? 'copy' : 'sparkle' ); // phpcs:ignore ?></span>
						<span class="seopro-ai__name"><?php echo esc_html( $seopro_ai_name ); ?></span>
						<?php if ( $seopro_ai_copy ) : ?><span class="seopro-ai__tag"><?php esc_html_e( 'kopyala & aç', 'seopro' ); ?></span><?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
			<p class="seopro-ai-note"><?php esc_html_e( '"Kopyala & aç" işaretli yapay zekalar bağlantıdan soru almıyor; tıklayınca soru panoya kopyalanır, açılan sohbete yapıştırın (Ctrl/⌘+V).', 'seopro' ); ?></p>
		</div>
	</div>

	<div class="seopro-single__content" itemprop="articleBody">
		<?php
		the_content();
		wp_link_pages( [
			'before' => '<nav class="seopro-page-links" aria-label="' . esc_attr__( 'Sayfa bağlantıları', 'seopro' ) . '">',
			'after'  => '</nav>',
		] );
		?>
	</div>

	<footer class="seopro-single__footer">
		<?php the_tags( '<div class="seopro-single__tags">', '', '</div>' ); ?>
	</footer>

	<?php get_template_part( 'template-parts/author-bio' ); ?>
	<?php get_template_part( 'template-parts/post-navigation' ); ?>
	<?php get_template_part( 'template-parts/related-posts' ); ?>
</article>
