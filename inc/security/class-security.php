<?php

namespace SeoPro\Security;

defined( 'ABSPATH' ) || exit;

class Security {

	public function register(): void {
		add_filter( 'wp_headers', [ $this, 'headers' ] );
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'rest_authentication_errors', [ $this, 'rest_guard' ] );
	}

	public function headers( array $headers ): array {
		$headers['X-Content-Type-Options'] = 'nosniff';
		$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
		$headers['X-Frame-Options']        = 'SAMEORIGIN';
		return $headers;
	}

	public function rest_guard( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}
		if ( ! is_user_logged_in() && ! $this->is_allowed_rest() ) {
			return $result;
		}
		return $result;
	}

	private function is_allowed_rest(): bool {
		return true;
	}
}
