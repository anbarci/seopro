<?php
/**
 * Tema kurulumu: destekler, menüler, dil, görsel boyutları.
 *
 * @package SeoPro\Core
 */

namespace SeoPro\Core;

defined( 'ABSPATH' ) || exit;

class Setup {

	public function register(): void {
		add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ] );
		add_action( 'after_setup_theme', [ $this, 'content_width' ], 0 );
		add_action( 'widgets_init', [ $this, 'widgets_init' ] );
	}

	/**
	 * Gömülü medya (oEmbed) ve geniş hizalama için içerik genişliği.
	 */
	public function content_width(): void {
		if ( ! isset( $GLOBALS['content_width'] ) ) {
			$GLOBALS['content_width'] = 820;
		}
	}

	/**
	 * Tema desteklerini ve menüleri tanımla.
	 */
	public function after_setup_theme(): void {
		load_theme_textdomain( 'seopro', SEOPRO_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor.css' );
		add_theme_support( 'post-formats', [ 'aside', 'gallery', 'link', 'image', 'quote', 'video', 'audio' ] );
		add_theme_support( 'custom-logo', [
			'height'      => 60,
			'width'       => 220,
			'flex-width'  => true,
			'flex-height' => true,
		] );
		add_theme_support( 'html5', [
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		] );

		register_nav_menus( [
			'primary' => __( 'Ana Menü', 'seopro' ),
			'footer'  => __( 'Footer Menü', 'seopro' ),
		] );

		// Tema genelinde kullanılan içerik görsel boyutları.
		add_image_size( 'seopro-card', 800, 450, true );
		add_image_size( 'seopro-thumb', 400, 225, true );
		add_image_size( 'seopro-hero', 1280, 720, true );
	}

	/**
	 * Widget bölgelerini kaydet.
	 */
	public function widgets_init(): void {
		register_sidebar( [
			'name'          => __( 'Yan Sütun', 'seopro' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Ana yan sütun bölgesi.', 'seopro' ),
			'before_widget' => '<section id="%1$s" class="seopro-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="seopro-widget__title">',
			'after_title'   => '</h2>',
		] );

		for ( $i = 1; $i <= 3; $i++ ) {
			register_sidebar( [
				/* translators: %d: footer column number. */
				'name'          => sprintf( __( 'Footer %d', 'seopro' ), $i ),
				'id'            => 'footer-' . $i,
				'before_widget' => '<section id="%1$s" class="seopro-widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="seopro-widget__title">',
				'after_title'   => '</h2>',
			] );
		}
	}
}
