<?php

namespace SeoPro\Widgets;

defined( 'ABSPATH' ) || exit;

class PopularPosts extends \WP_Widget {

	public function __construct() {
		parent::__construct(
			'seopro_popular_posts',
			__( 'SeoPro: Popüler Yazılar', 'seopro' ),
			[ 'description' => __( 'En çok yorum alan yazılar.', 'seopro' ) ]
		);
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] ?? __( 'Popüler', 'seopro' ) );
		$count = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;

		$query = new \WP_Query( [
			'posts_per_page'      => $count,
			'orderby'             => 'comment_count',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		] );

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '<ul class="seopro-widget-posts">';
		while ( $query->have_posts() ) {
			$query->the_post();
			printf(
				'<li class="seopro-widget-posts__item"><a href="%s">%s</a></li>',
				esc_url( get_permalink() ),
				esc_html( get_the_title() )
			);
		}
		echo '</ul>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_reset_postdata();
	}

	public function form( $instance ) {
		$title = $instance['title'] ?? __( 'Popüler', 'seopro' );
		$count = $instance['count'] ?? 5;
		printf(
			'<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s"></p>',
			esc_attr( $this->get_field_id( 'title' ) ),
			esc_html__( 'Başlık', 'seopro' ),
			esc_attr( $this->get_field_name( 'title' ) ),
			esc_attr( $title )
		);
		printf(
			'<p><label for="%1$s">%2$s</label><input class="tiny-text" id="%1$s" name="%3$s" type="number" min="1" max="20" value="%4$s"></p>',
			esc_attr( $this->get_field_id( 'count' ) ),
			esc_html__( 'Adet', 'seopro' ),
			esc_attr( $this->get_field_name( 'count' ) ),
			esc_attr( (string) $count )
		);
		return '';
	}

	public function update( $new_instance, $old_instance ) {
		return [
			'title' => sanitize_text_field( $new_instance['title'] ?? '' ),
			'count' => absint( $new_instance['count'] ?? 5 ),
		];
	}
}
