<?php

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class InContent {

	public function register(): void {
		add_filter( 'the_content', [ $this, 'inject' ], 12 );
	}

	public function inject( $content ) {
		if ( is_admin() || is_feed() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( \SeoPro\Amp\Amp::is_request() ) {
			return $content;
		}

		$related = $this->related_html( get_the_ID() );
		if ( '' === $related ) {
			return $content;
		}

		if ( substr_count( $content, '</p>' ) < 2 ) {
			return $content . $related;
		}

		$i = 0;
		return preg_replace_callback(
			'#</p>#',
			static function ( $m ) use ( &$i, $related ) {
				$i++;
				return ( 2 === $i ) ? '</p>' . $related : '</p>';
			},
			$content
		);
	}

	private function related_html( int $post_id ): string {
		// İki ORDER BY RAND() sorgusunun sonucunu yazı bazında cache'le.
		// Rastgele seçim TTL boyunca sabit kalır (kabul edilebilir) ama her
		// istekte filesort yapan RAND() sorguları DB'den kalkar.
		$ids = Cache::remember(
			'related_' . $post_id,
			HOUR_IN_SECONDS,
			static function () use ( $post_id ) {
				$cats = wp_get_post_categories( $post_id );

				$ids = [];
				if ( ! empty( $cats ) ) {
					$ids = get_posts( [
						'category__in'        => $cats,
						'post__not_in'        => [ $post_id ],
						'posts_per_page'      => 3,
						'orderby'             => 'rand',
						'fields'              => 'ids',
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
					] );
				}

				if ( count( $ids ) < 3 ) {
					$fill = get_posts( [
						'post__not_in'        => array_merge( [ $post_id ], $ids ),
						'posts_per_page'      => 3 - count( $ids ),
						'orderby'             => 'rand',
						'fields'              => 'ids',
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
					] );
					$ids = array_merge( $ids, $fill );
				}

				return array_values( array_map( 'intval', $ids ) );
			}
		);

		if ( empty( $ids ) ) {
			return '';
		}

		$q = new \WP_Query( [
			'post__in'            => $ids,
			'orderby'             => 'post__in',
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		] );

		ob_start();
		?>
		<aside class="seopro-inline-related" aria-label="<?php esc_attr_e( 'Benzer içerikler', 'seopro' ); ?>">
			<div class="seopro-inline-related__head">
				<span class="seopro-inline-related__icon" aria-hidden="true"><?php echo \SeoPro\Core\Helpers::icon( 'sparkle' ); // phpcs:ignore ?></span>
				<span class="seopro-inline-related__label"><?php esc_html_e( 'Benzer İçerikler', 'seopro' ); ?></span>
			</div>
			<ul class="seopro-inline-related__list">
				<?php
				while ( $q->have_posts() ) :
					$q->the_post();
					$c     = get_the_category();
					$color = ! empty( $c ) ? \SeoPro\Core\Helpers::cat_color( $c[0]->term_id ) : 'var(--brand-primary)';
					$ink   = ! empty( $c ) ? \SeoPro\Core\Helpers::cat_ink( $c[0]->term_id ) : 'var(--brand-primary)';
					?>
					<li class="seopro-inline-related__item" style="--cat-color: <?php echo esc_attr( $color ); ?>; --cat-ink: <?php echo esc_attr( $ink ); ?>">
						<a class="seopro-inline-related__link" href="<?php the_permalink(); ?>">
							<span class="seopro-inline-related__body">
								<?php if ( ! empty( $c ) ) : ?>
									<span class="seopro-inline-related__cat"><?php echo esc_html( $c[0]->name ); ?></span>
								<?php endif; ?>
								<span class="seopro-inline-related__title"><?php the_title(); ?></span>
							</span>
						</a>
					</li>
					<?php
				endwhile;
				?>
			</ul>
		</aside>
		<?php
		wp_reset_postdata();
		return (string) ob_get_clean();
	}
}
