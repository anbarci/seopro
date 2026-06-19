<?php

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class Toc {

	public function register(): void {
		if ( ! Options::bool( 'seopro_toc_enable' ) ) {
			return;
		}
		add_filter( 'the_content', [ $this, 'inject' ], 11 );
	}

	public function inject( $content ) {
		if ( ! is_singular( [ 'post', 'page' ] ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( ! preg_match_all( '/<h([23])\b([^>]*)>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER ) || count( $matches ) < 2 ) {
			return $content;
		}

		$items = [];
		$used  = [];
		foreach ( $matches as $m ) {
			$text = trim( wp_strip_all_tags( $m[3] ) );
			if ( '' === $text ) {
				continue;
			}
			$level = (int) $m[1];
			if ( preg_match( '/\bid=["\']([^"\']+)["\']/', $m[2], $idm ) ) {
				$id = $idm[1];
			} else {
				$id     = $this->unique_slug( $text, $used );
				$withid = '<h' . $level . $m[2] . ' id="' . esc_attr( $id ) . '">' . $m[3] . '</h' . $level . '>';
				$pos    = strpos( $content, $m[0] );
				if ( false !== $pos ) {
					$content = substr_replace( $content, $withid, $pos, strlen( $m[0] ) );
				}
			}
			$used[ $id ] = true;
			$items[]     = [
				'level' => $level,
				'id'    => $id,
				'text'  => $text,
			];
		}

		if ( count( $items ) < 2 ) {
			return $content;
		}

		$toc = $this->render( $items );
		if ( preg_match( '/<h[23]\b[^>]*>/i', $content, $fm, PREG_OFFSET_CAPTURE ) ) {
			return substr_replace( $content, $toc, $fm[0][1], 0 );
		}
		return $toc . $content;
	}

	private function unique_slug( string $text, array &$used ): string {
		$base = sanitize_title( $text );
		if ( '' === $base ) {
			$base = 'bolum';
		}
		$slug = $base;
		$i    = 2;
		while ( isset( $used[ $slug ] ) ) {
			$slug = $base . '-' . $i;
			$i++;
		}
		return $slug;
	}

	private function render( array $items ): string {
		$amp = \SeoPro\Amp\Amp::is_request();
		ob_start();
		if ( $amp ) :
			$permalink = get_permalink();
			?>
			<nav class="seopro-toc seopro-toc--amp" aria-label="<?php esc_attr_e( 'İçindekiler', 'seopro' ); ?>">
				<span class="seopro-toc__amplabel"><?php esc_html_e( 'İçindekiler', 'seopro' ); ?></span>
				<ol class="seopro-toc__list">
					<?php foreach ( $items as $item ) : ?>
						<li class="seopro-toc__item seopro-toc__item--h<?php echo (int) $item['level']; ?>"><a href="<?php echo esc_url( $permalink . '#' . $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a></li>
					<?php endforeach; ?>
				</ol>
			</nav>
			<?php
		else :
			?>
			<details class="seopro-toc">
				<summary class="seopro-toc__head">
					<span class="seopro-toc__icon"><?php echo Helpers::icon( 'list' ); // phpcs:ignore ?></span>
					<span class="seopro-toc__label"><?php esc_html_e( 'İçindekiler', 'seopro' ); ?></span>
					<span class="seopro-toc__toggle" aria-hidden="true"><?php echo Helpers::icon( 'plus' ); // phpcs:ignore ?></span>
				</summary>
				<nav class="seopro-toc__nav" aria-label="<?php esc_attr_e( 'İçindekiler', 'seopro' ); ?>">
					<ol class="seopro-toc__list">
						<?php foreach ( $items as $item ) : ?>
							<li class="seopro-toc__item seopro-toc__item--h<?php echo (int) $item['level']; ?>"><a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</nav>
			</details>
			<?php
		endif;
		return (string) ob_get_clean();
	}
}
