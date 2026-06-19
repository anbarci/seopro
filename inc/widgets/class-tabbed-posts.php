<?php
/**
 * Sekmeli yazı widget'ı: Popüler / Son / Rastgele (ikon sekmeli).
 *
 * @package SeoPro\Widgets
 */

namespace SeoPro\Widgets;

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

	private function query( string $source, int $count ): \WP_Query {
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

	private function panel( string $source, int $count, string $panel_id, string $tab_id, bool $active, bool $tabbed, string $style ): string {
		$q = $this->query( $source, $count );
		$show_thumb = ( 'cards' === $style );
		ob_start();
		printf(
			'<ul id="%s" class="seopro-tabposts__panel%s"%s%s>',
			esc_attr( $panel_id ),
			$active ? ' is-active' : '',
			$tabbed ? ' role="tabpanel" aria-labelledby="' . esc_attr( $tab_id ) . '"' : '',
			( $tabbed && ! $active ) ? ' hidden' : ''
		);
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$thumb = '';
				if ( $show_thumb ) {
					$thumb = has_post_thumbnail()
						? get_the_post_thumbnail( get_the_ID(), 'seopro-thumb', [ 'class' => 'seopro-tabposts__thumb', 'loading' => 'lazy', 'alt' => '' ] )
						: '<span class="seopro-tabposts__thumb seopro-tabposts__thumb--ph" aria-hidden="true"></span>';
				}
				printf(
					'<li class="seopro-tabposts__item"><a class="seopro-tabposts__link" href="%s">%s<span class="seopro-tabposts__body"><span class="seopro-tabposts__title">%s</span><time class="seopro-tabposts__date" datetime="%s">%s</time></span></a></li>',
					esc_url( get_permalink() ),
					$thumb, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_html( get_the_title() ),
					esc_attr( get_the_date( 'c' ) ),
					esc_html( get_the_date() )
				);
			}
		} else {
			printf( '<li class="seopro-tabposts__empty">%s</li>', esc_html__( 'Henüz yazı yok.', 'seopro' ) );
		}
		echo '</ul>';
		wp_reset_postdata();
		return (string) ob_get_clean();
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] ?? '' );
		$count = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		$uid   = sanitize_html_class( $this->id ?: uniqid( 'tp' ) );

		// Görünüm + hangi sekmeler? Tema panelinden (theme_mod) kontrol edilir.
		$style = ( 'list' === Options::get( 'seopro_tabposts_style' ) ) ? 'list' : 'cards';
		$all   = [
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
			$tabs = [ 'latest' => $all['latest'] ]; // hepsi kapalıysa en azından son yazılar
		}
		$tabbed = count( $tabs ) > 1;

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

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
			echo $this->panel( $key, $count, $uid . '-panel-' . $key, $uid . '-tab-' . $key, $first, $tabbed, $style ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$first = false;
		}
		echo '</div>';

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
