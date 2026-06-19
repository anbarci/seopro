<?php

namespace SeoPro\Widgets;

defined( 'ABSPATH' ) || exit;

class Registrar {

	public function register(): void {
		add_action( 'widgets_init', [ $this, 'register_widgets' ] );
	}

	public function register_widgets(): void {
		register_widget( TabbedPosts::class );
		register_widget( PopularPosts::class );
		register_widget( AuthorsList::class );
	}
}
