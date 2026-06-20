<?php
/**
 * Sekmeli yazı widget'ı: Popüler / Son / Rastgele (ikon sekmeli).
 *
 * @package SeoPro\Widgets
 */

namespace SeoPro\Widgets;

use SeoPro\Core\Cache;
use SeoPro\Core\Helpers;
use SeoPro\Core\Options;

defined( 'ABSPATH' ) || exit;

class TabbedPosts extends \WP_Widget {

	public function __construct() {
		parent::__construct(
			'seopro_tabbed_posts',
			__( 'SeoPro: Sekmeli Yazılar (Popüler / Son / Rastgele)', 'seopro' ),
			[ 'description' => __( 'İkon sekmeli yazı listesi: Popüler, son ve rastgele yazılar tek widget\'ta.', 'seopro' ) ]
		);
	}

	private static function query_for( string $source, int $count ): \WP_Query {
		$args = [
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		];
		if ( 'popular' === $source ) {
			$args['orderby'] = 'comment_count';
			$args['order']   = 'DESC';
		} elseif ( 'random' === $source ) {
			$args['orderby'] = 'rand';
		} else {
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
		}
		return new \WP_Query( $args );
	}

	/**
	 * Bir kaynağın hafif yazı verisini üretir (sorgu + öne çıkan görsel markup'ı).
	 * Sonuç cache'lenebilir olsun diye yalnız string/skaler değer döndürür.
	 */
	private static function tab_items( string $source, int $count, bool $show_thumb ): array {
		$q     = self::query_for( $source, $count );
		$items = [];
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$thumb = '';
				if ( $show_thumb ) {
					$thumb = has_post_thumbnail()
						? get_the_post_thumbnail( get_the_ID(), 'seopro-thumb', [ 'class' => 'seopro-tabposts__thumb', 'loading' => 'lazy', 'alt' => '' ] )
						: '<span class="seopro-tabposts__thumb seopro-tabposts__thumb--ph" aria-hidden="true"></span>';
				}
				$items[] = [
					'url'   => get_permalink(),
					'title' => get_the_title(),
					'c'     => get_the_date( 'c' ),
					'date'  => get_the_date(),
					'thumb' => $thumb,
				];
			}
		}
		wp_reset_postdata();
		return $items;
	}

	/**
	 * Önceden üretilmiş item dizisinden panel markup'ı (sorgu yok).
	 */
	private static function panel_html( array $items, string $panel_id, string $tab_id, bool $active, bool $tabbed, bool $show_thumb ): string {
		// tabpanel rolü SARMALAYICI div'de; liste (<ul>) içeride düz kalır ki
		// <li> öğeleri liste semantiğini koruyup üst <ul>'da yer alsın (a11y).
		ob_start();
		printf(
			'<div id="%s" class="seopro-tabposts__panel%s"%s%s><ul class="seopro-tabposts__list">',
			esc_attr( $panel_id ),
			$active ? ' is-active' : '',
			$tabbed ? ' role="tabpanel" aria-labelledby="' . esc_attr( $tab_id ) . '"' : '',
			( $tabbed && ! $active ) ? ' hidden' : ''
		);
		if ( ! empty( $items ) ) {
			foreach ( $items as $it ) {
				$thumb = $show_thumb ? (string) ( $it['thumb'] ?? '' ) : '';
				printf(
					'<li class="seopro-tabposts__item"><a class="seopro-tabposts__link" href="%s">%s<span class="seopro-tabposts__body"><span class="seopro-tabposts__title">%s</span><time class="seopro-tabposts__date" datetime="%s">%s</time></span></a></li>',
					esc_url( $it['url'] ?? '' ),
					$thumb, // WP üretimi güvenli markup. phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_html( $it['title'] ?? '' ),
					esc_attr( $it['c'] ?? '' ),
					esc_html( $it['date'] ?? '' )
				);
			}
		} else {
			printf( '<li class="seopro-tabposts__empty">%s</li>', esc_html__( 'Henüz yazı yok.', 'seopro' ) );
		}
		echo '</ul></div>';
		return (string) ob_get_clean();
	}

	/**
	 * Sekmeli yazı bloğunu üretir. Hem widget hem de panel-kontrollü
	 * sidebar otomatik gösterimi bunu kullanır. Görünüm/sekmeler tema panelinden.
	 */
	public static function render_block( int $count = 5 ): string {
		$count      = max( 1, min( 15, $count ) );
		$uid        = sanitize_html_class( wp_unique_id( 'seopro-tp-' ) );
		$style      = ( 'list' === Options::get( 'seopro_tabposts_style' ) ) ? 'list' : 'cards';
		$show_thumb = ( 'cards' === $style );

		$all = [
			'popular' => [ 'icon' => 'fire', 'label' => __( 'Popüler', 'seopro' ) ],
			'latest'  => [ 'icon' => 'clock', 'label' => __( 'Son yazılar', 'seopro' ) ],
			'random'  => [ 'icon' => 'shuffle', 'label' => __( 'Rastgele', 'seopro' ) ],
		];
		$tabs = [];
		foreach ( $all as $key => $t ) {
			if ( Options::bool( 'seopro_tabposts_' . $key ) ) {
				$tabs[ $key ] = $t;
			}
		}
		if ( empty( $tabs ) ) {
			$tabs = [ 'latest' => $all['latest'] ];
		}
		$tabbed = count( $tabs ) > 1;

		// Görünür sekmelerin verisini tek transient'te cache'le: cache-hit'te
		// 3 WP_Query + meta/terim/görsel priming (ölçülen ~31 sorgu) yerine ~1.
		// Önek 'tabposts2': a11y markup değişimi sonrası eski cache'lenmiş HTML servis edilmesin.
		$ckey = 'tabposts2_' . implode( '-', array_keys( $tabs ) ) . '_' . ( $show_thumb ? 't' : 'n' ) . '_' . $count;
		$data = Cache::remember(
			$ckey,
			15 * MINUTE_IN_SECONDS,
			static function () use ( $tabs, $count, $show_thumb ) {
				$out = [];
				foreach ( $tabs as $key => $t ) {
					$out[ $key ] = self::tab_items( $key, $count, $show_thumb );
				}
				return $out;
			}
		);

		ob_start();
		printf( '<div class="seopro-tabposts seopro-tabposts--%s"%s>', esc_attr( $style ), $tabbed ? ' data-seopro-tabs' : '' );

		if ( $tabbed ) {
			echo '<div class="seopro-tabposts__tabs" role="tablist" aria-label="' . esc_attr__( 'Yazı listeleri', 'seopro' ) . '">';
			$first = true;
			foreach ( $tabs as $key => $t ) {
				printf(
					'<button type="button" class="seopro-tabposts__tab%1$s" id="%2$s-tab-%3$s" role="tab" aria-controls="%2$s-panel-%3$s" aria-selected="%4$s"%5$s aria-label="%6$s" title="%6$s">%7$s</button>',
					$first ? ' is-active' : '',
					esc_attr( $uid ),
					esc_attr( $key ),
					$first ? 'true' : 'false',
					$first ? '' : ' tabindex="-1"',
					esc_attr( $t['label'] ),
					Helpers::icon( $t['icon'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				$first = false;
			}
			echo '</div>';
		}

		$first = true;
		foreach ( $tabs as $key => $t ) {
			$items = $data[ $key ] ?? [];
			echo self::panel_html( $items, $uid . '-panel-' . $key, $uid . '-tab-' . $key, $first, $tabbed, $show_thumb ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$first = false;
		}
		echo '</div>';
		return (string) ob_get_clean();
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] ?? '' );
		$count = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo self::render_block( $count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$title = $instance['title'] ?? '';
		$count = $instance['count'] ?? 5;
		printf(
			'<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s"></p>',
			esc_attr( $this->get_field_id( 'title' ) ),
			esc_html__( 'Başlık', 'seopro' ),
			esc_attr( $this->get_field_name( 'title' ) ),
			esc_attr( $title )
		);
		printf(
			'<p><label for="%1$s">%2$s</label> <input class="tiny-text" id="%1$s" name="%3$s" type="number" min="1" max="15" value="%4$s"></p>',
			esc_attr( $this->get_field_id( 'count' ) ),
			esc_html__( 'Her sekmede yazı adedi', 'seopro' ),
			esc_attr( $this->get_field_name( 'count' ) ),
			esc_attr( (string) $count )
		);
		return '';
	}

	public function update( $new_instance, $old_instance ) {
		return [
			'title' => sanitize_text_field( $new_instance['title'] ?? '' ),
			'count' => min( 15, max( 1, absint( $new_instance['count'] ?? 5 ) ) ),
		];
	}
}
