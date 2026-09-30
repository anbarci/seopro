<?php

namespace SeoPro\Security;

defined( 'ABSPATH' ) || exit;

class Security {

	public function register(): void {
		add_filter( 'wp_headers', [ $this, 'headers' ] );
		add_filter( 'xmlrpc_enabled', '__return_false' );
	}

	public function headers( array $headers ): array {
		$headers['X-Content-Type-Options'] = 'nosniff';
		$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
		$headers['X-Frame-Options']        = 'SAMEORIGIN';
		return $headers;
	}
}
