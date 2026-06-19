<?php

namespace SeoPro\Amp;

use SeoPro\Core\Options;

defined( 'ABSPATH' ) || exit;

class Amp {

	private const QV = 'amp';

	public function register(): void {
		if ( ! Options::bool( 'seopro_amp_enable' ) ) {
			return;
		}
		add_action( 'init', [ $this, 'add_endpoint' ] );
		add_filter( 'query_vars', [ $this, 'query_var' ] );
		add_action( 'template_redirect', [ $this, 'maybe_render' ] );
		add_action( 'wp_head', [ $this, 'amphtml_link' ], 1 );
		add_action( 'after_switch_theme', [ $this, 'flush' ] );
	}

	public function add_endpoint(): void {
		add_rewrite_endpoint( self::QV, EP_PERMALINK );
	}

	public function flush(): void {
		$this->add_endpoint();
		flush_rewrite_rules();
	}

	public function query_var( array $vars ): array {
		$vars[] = self::QV;
		return $vars;
	}

	public static function is_request(): bool {
		global $wp_query;
		if ( isset( $_GET[ self::QV ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
		return isset( $wp_query ) && is_array( $wp_query->query_vars ) && array_key_exists( self::QV, $wp_query->query_vars );
	}

	public function amphtml_link(): void {
		if ( self::is_request() || ! is_singular( 'post' ) ) {
			return;
		}
		printf( '<link rel="amphtml" href="%s" />' . "\n", esc_url( trailingslashit( get_permalink() ) . 'amp/' ) );
	}

	public function maybe_render(): void {
		if ( ! self::is_request() || ! is_singular( 'post' ) ) {
			return;
		}
		$this->render();
		exit;
	}

	private function render(): void {
		the_post();
		$full      = Sanitizer::content( apply_filters( 'the_content', get_the_content() ) );
		$content   = $this->truncate_html( $full );
		$truncated = ( $content !== $full );
		$css     = file_exists( SEOPRO_DIR . '/assets/css/amp.css' )
			? file_get_contents( SEOPRO_DIR . '/assets/css/amp.css' )
			: '';
		$thumb   = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'seopro-hero' ) : '';

		status_header( 200 );
		header( 'Content-Type: text/html; charset=utf-8' );
		?>
<!DOCTYPE html>
<html amp lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
	<title><?php echo esc_html( wp_get_document_title() ); ?></title>
	<link rel="canonical" href="<?php echo esc_url( get_permalink() ); ?>">
	<script async src="https://cdn.ampproject.org/v0.js"></script>
	<?php if ( false !== strpos( $content, 'amp-youtube' ) ) : ?>
		<script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>
	<?php endif; ?>
	<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
	<?php do_action( 'seopro_amp_head' ); ?>
	<style amp-custom><?php echo wp_strip_all_tags( (string) $css ); // phpcs:ignore ?></style>
</head>
<body>
	<?php do_action( 'seopro_amp_body_open' ); ?>
	<header class="amp-header"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></header>
	<main>
		<article>
			<h1><?php the_title(); ?></h1>
			<p class="amp-meta"><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></p>
			<?php if ( $thumb ) : ?>
				<amp-img src="<?php echo esc_url( $thumb ); ?>" width="1280" height="720" layout="responsive" alt="<?php echo esc_attr( get_the_title() ); ?>"></amp-img>
			<?php endif; ?>
			<?php do_action( 'seopro_amp_content_top' ); ?><div class="amp-content<?php echo $truncated ? ' amp-content--cut' : ''; ?>"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php do_action( 'seopro_amp_content_bottom' ); ?>
			<?php if ( $truncated ) : ?>
				<div class="amp-readmore">
					<p class="amp-readmore__hint"><?php esc_html_e( 'Yazının tamamını okumak için devam edin', 'seopro' ); ?></p>
					<a class="amp-readmore__btn" href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'Yazıyı Oku', 'seopro' ); ?></a>
				</div>
			<?php endif; ?>
		</article>
	</main>
	<footer class="amp-footer">&copy; <?php echo esc_html( wp_date( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?></footer>
</body>
</html>
		<?php
	}

	private function truncate_html( string $html ): string {
		$total = substr_count( $html, '</p>' );
		if ( $total <= 1 ) {
			return $html;
		}
		$keep  = (int) max( 1, ceil( $total / 2 ) );
		$parts = explode( '</p>', $html );
		$kept  = array_slice( $parts, 0, $keep );
		return implode( '</p>', $kept ) . '</p>';
	}
}
