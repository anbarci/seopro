<?php

namespace SeoPro\Optimizer;

use SeoPro\Core\Options;

defined( 'ABSPATH' ) || exit;

class Watermark {

	public function register(): void {
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'process' ], 20, 2 );
	}

	public function process( $metadata, $attachment_id ) {
		if ( ! Options::bool( 'seopro_watermark_enable' ) || ! function_exists( 'imagecreatetruecolor' ) ) {
			return $metadata;
		}
		$mime = (string) get_post_mime_type( $attachment_id );
		if ( ! in_array( $mime, [ 'image/jpeg', 'image/png', 'image/webp' ], true ) ) {
			return $metadata;
		}
		$type    = 'logo' === Options::get( 'seopro_watermark_type' ) ? 'logo' : 'text';
		$logo_id = (int) Options::get( 'seopro_watermark_logo' );
		if ( 'logo' === $type && ( ! $logo_id || $logo_id === (int) $attachment_id ) ) {
			return $metadata;
		}

		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) ) {
			return $metadata;
		}
		$files = [ $file ];
		$dir   = trailingslashit( dirname( $file ) );
		if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size ) {
				if ( ! empty( $size['file'] ) && (int) ( $size['width'] ?? 0 ) >= 320 ) {
					$files[] = $dir . $size['file'];
				}
			}
		}
		foreach ( array_unique( $files ) as $path ) {
			$this->stamp( $path, $mime, $type );
		}
		return $metadata;
	}

	private function load( string $path, string $mime ) {
		switch ( $mime ) {
			case 'image/jpeg':
				return @imagecreatefromjpeg( $path );
			case 'image/png':
				return @imagecreatefrompng( $path );
			case 'image/webp':
				return function_exists( 'imagecreatefromwebp' ) ? @imagecreatefromwebp( $path ) : false;
		}
		return false;
	}

	private function save( $img, string $path, string $mime ): void {
		$q = (int) Options::get( 'seopro_image_quality' );
		$q = ( $q >= 1 && $q <= 100 ) ? $q : 85;
		switch ( $mime ) {
			case 'image/jpeg':
				imagejpeg( $img, $path, $q );
				break;
			case 'image/png':
				imagesavealpha( $img, true );
				imagepng( $img, $path );
				break;
			case 'image/webp':
				if ( function_exists( 'imagewebp' ) ) {
					imagewebp( $img, $path, $q );
				}
				break;
		}
	}

	private function stamp( string $path, string $mime, string $type ): void {
		if ( ! file_exists( $path ) ) {
			return;
		}
		$img = $this->load( $path, $mime );
		if ( ! $img ) {
			return;
		}
		imagealphablending( $img, true );
		if ( 'logo' === $type ) {
			$this->apply_logo( $img );
		} else {
			$this->apply_text( $img );
		}
		$this->save( $img, $path, $mime );
		imagedestroy( $img );
	}

	private function opacity_pct(): int {
		return max( 0, min( 100, (int) Options::get( 'seopro_watermark_opacity' ) ) );
	}

	private function size_pct(): int {
		$s = (int) Options::get( 'seopro_watermark_size' );
		return max( 5, min( 60, $s ?: 18 ) );
	}

	private function position(): string {
		$p     = (string) Options::get( 'seopro_watermark_position' );
		$allow = [ 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'center' ];
		return in_array( $p, $allow, true ) ? $p : 'bottom-right';
	}

	private function font_path(): string {
		$f = (string) apply_filters( 'seopro_watermark_font', '' );
		if ( $f && file_exists( $f ) ) {
			return $f;
		}
		$bundled = SEOPRO_DIR . '/assets/fonts/watermark.ttf';
		if ( file_exists( $bundled ) ) {
			return $bundled;
		}
		foreach ( [
			'/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
			'/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
			'/Library/Fonts/Arial.ttf',
		] as $p ) {
			if ( file_exists( $p ) ) {
				return $p;
			}
		}
		return '';
	}

	private function apply_text( $img ): void {
		$text = trim( (string) Options::get( 'seopro_watermark_text' ) );
		if ( '' === $text ) {
			$text = (string) get_bloginfo( 'name' );
		}
		$font = $this->font_path();
		if ( '' === $text || ! $font || ! function_exists( 'imagettftext' ) ) {
			return;
		}
		$w        = imagesx( $img );
		$h        = imagesy( $img );
		$fontsize = max( 11, (int) round( $w / 30 * ( $this->size_pct() / 18 ) ) );
		$alpha    = max( 0, min( 127, (int) round( ( 100 - $this->opacity_pct() ) / 100 * 127 ) ) );
		$margin   = (int) round( $w * 0.028 );

		$bbox = imagettfbbox( $fontsize, 0, $font, $text );
		$tw   = abs( $bbox[2] - $bbox[0] );
		$th   = abs( $bbox[7] - $bbox[1] );

		$pos = $this->position();
		if ( false !== strpos( $pos, 'left' ) ) {
			$x = $margin;
		} elseif ( 'center' === $pos ) {
			$x = (int) round( ( $w - $tw ) / 2 );
		} else {
			$x = $w - $tw - $margin;
		}
		if ( false !== strpos( $pos, 'top' ) ) {
			$top = $margin;
		} elseif ( 'center' === $pos ) {
			$top = (int) round( ( $h - $th ) / 2 );
		} else {
			$top = $h - $th - $margin;
		}
		$y = $top + $th;

		$shadow = imagecolorallocatealpha( $img, 0, 0, 0, min( 127, $alpha + 25 ) );
		$white  = imagecolorallocatealpha( $img, 255, 255, 255, $alpha );
		imagettftext( $img, $fontsize, 0, $x + 1, $y + 1, $shadow, $font, $text );
		imagettftext( $img, $fontsize, 0, $x, $y, $white, $font, $text );
	}

	private function apply_logo( $img ): void {
		$logo_id = (int) Options::get( 'seopro_watermark_logo' );
		if ( ! $logo_id ) {
			return;
		}
		$logo_path = get_attached_file( $logo_id );
		if ( ! $logo_path || ! file_exists( $logo_path ) ) {
			return;
		}
		$logo = $this->load( $logo_path, (string) get_post_mime_type( $logo_id ) );
		if ( ! $logo ) {
			return;
		}
		$lw = imagesx( $logo );
		$lh = imagesy( $logo );
		if ( $lw < 1 || $lh < 1 ) {
			imagedestroy( $logo );
			return;
		}
		$w        = imagesx( $img );
		$h        = imagesy( $img );
		$target_w = max( 32, (int) round( $w * ( $this->size_pct() / 100 ) ) );
		$target_h = max( 1, (int) round( $lh * ( $target_w / $lw ) ) );
		$margin   = (int) round( $w * 0.028 );

		$scaled = imagecreatetruecolor( $target_w, $target_h );
		imagealphablending( $scaled, false );
		imagesavealpha( $scaled, true );
		imagefill( $scaled, 0, 0, imagecolorallocatealpha( $scaled, 0, 0, 0, 127 ) );
		imagecopyresampled( $scaled, $logo, 0, 0, 0, 0, $target_w, $target_h, $lw, $lh );

		$pos = $this->position();
		if ( false !== strpos( $pos, 'left' ) ) {
			$x = $margin;
		} elseif ( 'center' === $pos ) {
			$x = (int) round( ( $w - $target_w ) / 2 );
		} else {
			$x = $w - $target_w - $margin;
		}
		if ( false !== strpos( $pos, 'top' ) ) {
			$y = $margin;
		} elseif ( 'center' === $pos ) {
			$y = (int) round( ( $h - $target_h ) / 2 );
		} else {
			$y = $h - $target_h - $margin;
		}

		$this->merge_alpha( $img, $scaled, $x, $y, $target_w, $target_h, $this->opacity_pct() );
		imagedestroy( $logo );
		imagedestroy( $scaled );
	}

	private function merge_alpha( $dst, $src, $dx, $dy, $sw, $sh, $pct ): void {
		$pct = max( 0, min( 100, $pct ) );
		$cut = imagecreatetruecolor( $sw, $sh );
		imagecopy( $cut, $dst, 0, 0, $dx, $dy, $sw, $sh );
		imagecopy( $cut, $src, 0, 0, 0, 0, $sw, $sh );
		imagecopymerge( $dst, $cut, $dx, $dy, 0, 0, $sw, $sh, $pct );
		imagedestroy( $cut );
	}
}
