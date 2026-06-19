<?php

namespace SeoPro\Schema;

use SeoPro\Core\Options;
use SeoPro\Core\Mode;
use SeoPro\Core\Helpers;

defined( 'ABSPATH' ) || exit;

class Manager {

	public function register(): void {
		if ( ! Options::bool( 'seopro_schema_enable' ) || $this->seo_plugin_active() ) {
			return;
		}
		add_action( 'wp_head', [ $this, 'output' ], 30 );
	}

	private function seo_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'SEOPress' );
	}

	public function output(): void {
		$graph = array_filter( [
			$this->website(),
			$this->breadcrumbs(),
			$this->primary_entity(),
		] );

		if ( empty( $graph ) ) {
			return;
		}

		$data = [
			'@context' => 'https://schema.org',
			'@graph'   => array_values( $graph ),
		];

		echo "\n" . '<script type="application/ld+json">'
			. wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			. '</script>' . "\n";
	}

	private function website(): array {
		if ( ! is_front_page() ) {
			return [];
		}
		return [
			'@type'           => 'WebSite',
			'name'            => get_bloginfo( 'name' ),
			'url'             => home_url( '/' ),
			'description'     => get_bloginfo( 'description' ),
			'potentialAction' => [
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			],
		];
	}

	private function breadcrumbs(): array {
		$items = Helpers::breadcrumb_items();
		if ( count( $items ) < 2 ) {
			return [];
		}
		$elements = [];
		$pos      = 1;
		foreach ( $items as $item ) {
			$el = [
				'@type'    => 'ListItem',
				'position' => $pos,
				'name'     => $item['label'],
			];
			if ( ! empty( $item['url'] ) ) {
				$el['item'] = $item['url'];
			}
			$elements[] = $el;
			$pos++;
		}
		return [
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $elements,
		];
	}

	private function primary_entity(): array {
		if ( is_singular( 'post' ) ) {
			return $this->article();
		}
		if ( is_singular( 'page' ) ) {
			return $this->web_page();
		}
		if ( is_author() ) {
			return $this->person();
		}
		if ( is_category() || is_tag() || is_tax() || is_home() ) {
			return $this->collection();
		}
		return [];
	}

	private function article_type(): string {
		$forced = (string) Options::get( 'seopro_schema_type' );
		if ( $forced && 'auto' !== $forced ) {
			return $forced;
		}
		return Mode::is_news() ? 'NewsArticle' : 'BlogPosting';
	}

	private function article(): array {
		$id   = get_the_ID();
		$img  = has_post_thumbnail( $id ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'seopro-hero' ) : false;

		$data = [
			'@type'            => $this->article_type(),
			'headline'         => get_the_title(),
			'datePublished'    => get_the_date( 'c', $id ),
			'dateModified'     => get_the_modified_date( 'c', $id ),
			'mainEntityOfPage' => get_permalink( $id ),
			'wordCount'        => str_word_count( wp_strip_all_tags( get_the_content() ) ),
			'inLanguage'       => get_bloginfo( 'language' ),
			'author'           => [
				'@type' => 'Person',
				'name'  => get_the_author(),
				'url'   => get_author_posts_url( (int) get_post_field( 'post_author', $id ) ),
			],
			'publisher'        => $this->publisher(),
		];

		$desc = get_the_excerpt();
		if ( $desc ) {
			$data['description'] = wp_strip_all_tags( $desc );
		}
		if ( $img ) {
			$data['image'] = [
				'@type'  => 'ImageObject',
				'url'    => $img[0],
				'width'  => (int) $img[1],
				'height' => (int) $img[2],
			];
		}
		$cats = get_the_category( $id );
		if ( ! empty( $cats ) ) {
			$data['articleSection'] = $cats[0]->name;
		}
		$tags = get_the_tags( $id );
		if ( ! empty( $tags ) ) {
			$data['keywords'] = implode( ', ', wp_list_pluck( $tags, 'name' ) );
		}
		return $data;
	}

	private function web_page(): array {
		return [
			'@type' => 'WebPage',
			'name'  => get_the_title(),
			'url'   => get_permalink(),
		];
	}

	private function person(): array {
		$author = get_queried_object();
		if ( ! $author instanceof \WP_User ) {
			return [];
		}
		return [
			'@type' => 'Person',
			'name'  => $author->display_name,
			'url'   => get_author_posts_url( $author->ID ),
		];
	}

	private function collection(): array {
		return [
			'@type' => 'CollectionPage',
			'name'  => wp_get_document_title(),
			'url'   => home_url( add_query_arg( [] ) ),
		];
	}

	private function publisher(): array {
		$logo = (int) Options::get( 'seopro_schema_org_logo' );
		if ( ! $logo ) {
			$logo = (int) get_theme_mod( 'custom_logo' );
		}
		$name = (string) Options::get( 'seopro_schema_org_name' );
		$data = [
			'@type' => 'Organization',
			'name'  => $name ?: get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		];
		if ( $logo ) {
			$src = wp_get_attachment_image_src( (int) $logo, 'full' );
			if ( $src ) {
				$data['logo'] = [
					'@type'  => 'ImageObject',
					'url'    => $src[0],
					'width'  => $src[1],
					'height' => $src[2],
				];
			}
		}
		return $data;
	}
}
