<?php
/**
 * Yazı/sayfa bazında SEO alanları: SEO başlığı, meta açıklama, noindex, kanonik URL.
 *
 * Yalnızca bir SEO eklentisi etkin DEĞİLSE çalışır; eklenti varsa bu alanları
 * o yönetir. Kayıtlı değerler Head (meta/OG), Schema ve arşiv robots kurallarında
 * kullanılır; noindex işaretli içerik WordPress site haritasından da çıkarılır.
 *
 * @package SeoPro\Seo
 */

namespace SeoPro\Seo;

use SeoPro\Core\Helpers;
use SeoPro\Core\Options;

defined( 'ABSPATH' ) || exit;

class PostMeta {

	public const TITLE     = '_seopro_title';
	public const DESC      = '_seopro_desc';
	public const NOINDEX   = '_seopro_noindex';
	public const CANONICAL = '_seopro_canonical';

	private const TYPES = [ 'post', 'page' ];

	public function register(): void {
		if ( Helpers::seo_plugin_active() ) {
			return;
		}
		add_action( 'add_meta_boxes', [ $this, 'box' ] );
		add_action( 'save_post', [ $this, 'save' ], 10, 2 );
		add_filter( 'pre_get_document_title', [ $this, 'title' ], 20 );

		// Site haritası: noindex içerikleri ve noindex arşivleri çıkar.
		add_filter( 'wp_sitemaps_posts_query_args', [ $this, 'sitemap_posts' ] );
		add_filter( 'wp_sitemaps_taxonomies', [ $this, 'sitemap_taxonomies' ] );
		add_filter( 'wp_sitemaps_taxonomies_query_args', [ $this, 'sitemap_terms' ], 10, 2 );
		add_filter( 'wp_sitemaps_add_provider', [ $this, 'sitemap_provider' ], 10, 2 );
	}

	/**
	 * Tekil içerik için kayıtlı meta açıklama ('' = yok).
	 */
	public static function description( int $post_id ): string {
		return trim( (string) get_post_meta( $post_id, self::DESC, true ) );
	}

	/**
	 * Az yazılı ("zayıf") etiket eşiği: bundan az yazısı olan etiket noindex olur.
	 */
	public static function thin_min(): int {
		return max( 1, (int) apply_filters( 'seopro_thin_tag_min', 3 ) );
	}

	public function title( $title ) {
		if ( ! is_singular( self::TYPES ) ) {
			return $title;
		}
		$custom = trim( (string) get_post_meta( get_queried_object_id(), self::TITLE, true ) );
		return '' !== $custom ? $custom : $title;
	}

	public function box(): void {
		add_meta_box(
			'seopro_seo',
			__( 'SEO (SeoPro)', 'seopro' ),
			[ $this, 'render' ],
			self::TYPES,
			'side',
			'default'
		);
	}

	public function render( $post ): void {
		wp_nonce_field( 'seopro_seo_meta', 'seopro_seo_nonce' );
		$title     = (string) get_post_meta( $post->ID, self::TITLE, true );
		$desc      = (string) get_post_meta( $post->ID, self::DESC, true );
		$noindex   = (bool) get_post_meta( $post->ID, self::NOINDEX, true );
		$canonical = (string) get_post_meta( $post->ID, self::CANONICAL, true );
		$css       = 'width:100%;box-sizing:border-box';
		?>
		<p style="margin:0 0 4px"><label for="seopro_seo_title"><strong><?php esc_html_e( 'SEO başlığı', 'seopro' ); ?></strong></label></p>
		<input type="text" id="seopro_seo_title" name="seopro_seo_title" value="<?php echo esc_attr( $title ); ?>" maxlength="120" style="<?php echo esc_attr( $css ); ?>" data-seopro-count="60">
		<p class="description" style="margin:2px 0 12px"><?php esc_html_e( 'Arama sonucundaki başlık. Boşsa "Yazı başlığı – Site adı". ~60 karakter.', 'seopro' ); ?> <span data-seopro-counter></span></p>

		<p style="margin:0 0 4px"><label for="seopro_seo_desc"><strong><?php esc_html_e( 'Meta açıklama', 'seopro' ); ?></strong></label></p>
		<textarea id="seopro_seo_desc" name="seopro_seo_desc" rows="3" maxlength="300" style="<?php echo esc_attr( $css ); ?>" data-seopro-count="155"><?php echo esc_textarea( $desc ); ?></textarea>
		<p class="description" style="margin:2px 0 12px"><?php esc_html_e( 'Boşsa özetten üretilir. ~120-155 karakter.', 'seopro' ); ?> <span data-seopro-counter></span></p>

		<p style="margin:0 0 12px"><label><input type="checkbox" name="seopro_seo_noindex" value="1" <?php checked( $noindex ); ?>> <?php esc_html_e( 'Arama motorlarında gösterme (noindex)', 'seopro' ); ?></label></p>

		<p style="margin:0 0 4px"><label for="seopro_canonical_field"><strong><?php esc_html_e( 'Kanonik URL', 'seopro' ); ?></strong></label></p>
		<input type="url" id="seopro_canonical_field" name="seopro_canonical" value="<?php echo esc_attr( $canonical ); ?>" placeholder="https://..." style="<?php echo esc_attr( $css ); ?>">
		<p class="description" style="margin:2px 0 0"><?php esc_html_e( 'Yazı önce başka bir sitede yayımlandıysa orijinal adresi. Boşsa bu yazının kendi adresi.', 'seopro' ); ?></p>
		<script>
		document.querySelectorAll('#seopro_seo [data-seopro-count]').forEach(function (el) {
			var out = el.nextElementSibling && el.nextElementSibling.querySelector('[data-seopro-counter]');
			if (!out) return;
			var max = parseInt(el.getAttribute('data-seopro-count'), 10);
			var upd = function () { var n = el.value.length; out.textContent = '(' + n + '/' + max + ')'; out.style.color = n > max ? '#b32d2e' : ''; };
			el.addEventListener('input', upd); upd();
		});
		</script>
		<?php
	}

	public function save( $post_id, $post ): void {
		if ( ! isset( $_POST['seopro_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['seopro_seo_nonce'] ) ), 'seopro_seo_meta' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! in_array( $post->post_type, self::TYPES, true ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$values = [
			self::TITLE     => sanitize_text_field( wp_unslash( $_POST['seopro_seo_title'] ?? '' ) ),
			self::DESC      => sanitize_textarea_field( wp_unslash( $_POST['seopro_seo_desc'] ?? '' ) ),
			self::NOINDEX   => empty( $_POST['seopro_seo_noindex'] ) ? '' : '1',
			self::CANONICAL => esc_url_raw( wp_unslash( $_POST['seopro_canonical'] ?? '' ) ),
		];
		foreach ( $values as $key => $value ) {
			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/* ===== Site haritası ===== */

	public function sitemap_posts( array $args ): array {
		$args['meta_query']   = (array) ( $args['meta_query'] ?? [] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$args['meta_query'][] = [
			'key'     => self::NOINDEX,
			'compare' => 'NOT EXISTS',
		];
		return $args;
	}

	public function sitemap_taxonomies( array $taxonomies ): array {
		if ( 'noindex' === Options::get( 'seopro_noindex_tags' ) ) {
			unset( $taxonomies['post_tag'] );
		}
		return $taxonomies;
	}

	public function sitemap_terms( array $args, string $taxonomy ): array {
		if ( 'post_tag' !== $taxonomy || 'thin' !== Options::get( 'seopro_noindex_tags' ) ) {
			return $args;
		}
		global $wpdb;
		$thin = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'post_tag' AND count < %d",
				self::thin_min()
			)
		);
		if ( $thin ) {
			$args['exclude'] = array_merge( (array) ( $args['exclude'] ?? [] ), array_map( 'intval', $thin ) );
		}
		return $args;
	}

	public function sitemap_provider( $provider, string $name ) {
		if ( 'users' === $name && Options::bool( 'seopro_noindex_author' ) ) {
			return false;
		}
		return $provider;
	}
}
