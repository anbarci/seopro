<?php

namespace SeoPro\Admin;

use SeoPro\Core\Options;

defined( 'ABSPATH' ) || exit;

class Panel {

	private const CAP  = 'edit_theme_options';
	private const SLUG = 'seopro';

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'menu' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
		add_action( 'admin_post_seopro_save_settings', [ $this, 'save' ] );
	}

	public function menu(): void {
		add_menu_page(
			__( 'SeoPro Tema', 'seopro' ),
			__( 'SeoPro', 'seopro' ),
			self::CAP,
			self::SLUG,
			[ $this, 'render' ],
			'dashicons-superhero',
			59
		);
		add_submenu_page( self::SLUG, __( 'Tema Ayarları', 'seopro' ), __( 'Tema Ayarları', 'seopro' ), self::CAP, self::SLUG, [ $this, 'render' ] );
	}

	private function groups(): array {
		$fonts = [ 'Work Sans' => 'Work Sans', 'Inter' => 'Inter', 'Space Grotesk' => 'Space Grotesk', 'Sora' => 'Sora', 'IBM Plex Sans' => 'IBM Plex Sans', 'Roboto' => 'Roboto', 'system-ui' => 'Sistem' ];

		return [
			'genel'      => [
				'label'  => __( 'Genel', 'seopro' ),
				'fields' => [
					'seopro_site_mode'     => [ 'type' => 'radio', 'label' => __( 'Site modu', 'seopro' ), 'choices' => [ 'blog' => __( 'Blog', 'seopro' ), 'news' => __( 'Haber', 'seopro' ) ] ],
					'seopro_default_theme' => [ 'type' => 'radio', 'label' => __( 'Varsayılan görünüm', 'seopro' ), 'choices' => [ 'light' => __( 'Açık', 'seopro' ), 'dark' => __( 'Koyu', 'seopro' ) ] ],
				],
			],
			'renkler'    => [
				'label'  => __( 'Renk Paleti', 'seopro' ),
				'fields' => [
					'seopro_brand_primary'   => [ 'type' => 'color', 'label' => __( 'Birincil renk', 'seopro' ) ],
					'seopro_brand_hover'     => [ 'type' => 'color', 'label' => __( 'Birincil hover', 'seopro' ) ],
					'seopro_brand_secondary' => [ 'type' => 'color', 'label' => __( 'İkincil renk', 'seopro' ) ],
					'seopro_bg_base'         => [ 'type' => 'color', 'label' => __( 'Zemin (açık tema)', 'seopro' ) ],
					'seopro_text_primary'    => [ 'type' => 'color', 'label' => __( 'Metin (açık tema)', 'seopro' ) ],
				],
			],
			'tipografi'  => [
				'label'  => __( 'Tipografi', 'seopro' ),
				'fields' => [
					'seopro_font_head' => [ 'type' => 'select', 'label' => __( 'Başlık fontu', 'seopro' ), 'choices' => $fonts ],
					'seopro_font_body' => [ 'type' => 'select', 'label' => __( 'Metin fontu', 'seopro' ), 'choices' => $fonts ],
				],
			],
			'duzen'      => [
				'label'  => __( 'Yerleşim', 'seopro' ),
				'fields' => [
					'seopro_container_max'    => [ 'type' => 'number', 'label' => __( 'Konteyner genişliği (px)', 'seopro' ) ],
					'seopro_radius'           => [ 'type' => 'number', 'label' => __( 'Köşe yuvarlaklığı (px)', 'seopro' ) ],
					'seopro_sidebar_position' => [ 'type' => 'select', 'label' => __( 'Yan sütun', 'seopro' ), 'choices' => [ 'right' => __( 'Sağ', 'seopro' ), 'left' => __( 'Sol', 'seopro' ), 'none' => __( 'Yok', 'seopro' ) ] ],
					'seopro_sticky_header'    => [ 'type' => 'checkbox', 'label' => __( 'Sabit header', 'seopro' ), 'hint' => __( 'Kaydırırken üstte sabit kalsın', 'seopro' ) ],
				],
			],
			'schema'     => [
				'label'  => __( 'Schema & SEO', 'seopro' ),
				'fields' => [
					'seopro_schema_enable'   => [ 'type' => 'checkbox', 'label' => __( 'Otomatik Schema (JSON-LD)', 'seopro' ), 'hint' => __( 'Yapısal veri çıktısı', 'seopro' ) ],
					'seopro_schema_type'     => [ 'type' => 'select', 'label' => __( 'Makale şeması', 'seopro' ), 'choices' => [ 'auto' => __( 'Otomatik (moda göre)', 'seopro' ), 'BlogPosting' => 'BlogPosting', 'NewsArticle' => 'NewsArticle' ], 'desc' => __( 'Otomatik: blog modunda BlogPosting, haber modunda NewsArticle.', 'seopro' ) ],
					'seopro_schema_org_name' => [ 'type' => 'text', 'label' => __( 'Yayıncı adı', 'seopro' ), 'desc' => __( 'Boş bırakılırsa site adı kullanılır.', 'seopro' ) ],
					'seopro_schema_org_logo' => [ 'type' => 'media', 'label' => __( 'Yayıncı logosu', 'seopro' ), 'desc' => __( 'Schema publisher logosu (boşsa özel logo).', 'seopro' ) ],
					'seopro_og_default'      => [ 'type' => 'media', 'label' => __( 'Varsayılan paylaşım görseli (OG)', 'seopro' ), 'desc' => __( 'Anasayfa, kategori, arşiv ve öne çıkan görseli olmayan yazılar paylaşılınca kullanılır. 1200×630 önerilir. Boşsa site logosuna düşer.', 'seopro' ) ],
					'seopro_amp_enable'      => [ 'type' => 'checkbox', 'label' => __( 'Dahili AMP katmanı', 'seopro' ), 'hint' => __( '/amp/ sürümü', 'seopro' ) ],
				],
			],
			'performans' => [
				'label'  => __( 'Performans', 'seopro' ),
				'fields' => [
					'seopro_lazyload' => [ 'type' => 'checkbox', 'label' => __( 'Lazy loading', 'seopro' ), 'hint' => __( 'Görsel/iframe geç yükleme', 'seopro' ) ],
					'seopro_toc_enable' => [ 'type' => 'checkbox', 'label' => __( 'İçindekiler (TOC)', 'seopro' ), 'hint' => __( 'Uzun yazılarda otomatik içerik tablosu', 'seopro' ) ],
				],
			],
			'gorsel'     => [
				'label'  => __( 'Görsel & Damga', 'seopro' ),
				'fields' => [
					'seopro_image_quality'      => [ 'type' => 'number', 'label' => __( 'Görsel kalitesi (JPEG/WebP)', 'seopro' ), 'desc' => __( '1-100. Düşük değer = küçük dosya. Sadece bundan sonra yüklenen görselleri etkiler.', 'seopro' ) ],
					'seopro_watermark_enable'   => [ 'type' => 'checkbox', 'label' => __( 'Watermark / damga', 'seopro' ), 'hint' => __( 'Yüklenen görsellere yazı veya logo damgası ekle', 'seopro' ) ],
					'seopro_watermark_type'     => [ 'type' => 'radio', 'label' => __( 'Damga türü', 'seopro' ), 'choices' => [ 'text' => __( 'Yazı', 'seopro' ), 'logo' => __( 'Logo (görsel)', 'seopro' ) ] ],
					'seopro_watermark_text'     => [ 'type' => 'text', 'label' => __( 'Damga yazısı', 'seopro' ), 'desc' => __( 'Boş bırakılırsa site adı kullanılır.', 'seopro' ) ],
					'seopro_watermark_logo'     => [ 'type' => 'media', 'label' => __( 'Damga logosu', 'seopro' ), 'desc' => __( 'Şeffaf PNG önerilir.', 'seopro' ) ],
					'seopro_watermark_position' => [ 'type' => 'select', 'label' => __( 'Konum', 'seopro' ), 'choices' => [ 'top-left' => __( 'Sol üst', 'seopro' ), 'top-right' => __( 'Sağ üst', 'seopro' ), 'bottom-left' => __( 'Sol alt', 'seopro' ), 'bottom-right' => __( 'Sağ alt', 'seopro' ), 'center' => __( 'Orta', 'seopro' ) ] ],
					'seopro_watermark_opacity'  => [ 'type' => 'number', 'label' => __( 'Opaklık (%)', 'seopro' ) ],
					'seopro_watermark_size'     => [ 'type' => 'number', 'label' => __( 'Damga boyutu', 'seopro' ), 'desc' => __( 'Yazı ve logo boyutunu ölçekler (varsayılan 18).', 'seopro' ) ],
				],
			],
			'reklam'     => [
				'label'  => __( 'Reklamlar', 'seopro' ),
				'fields' => [
					'seopro_ads_enable'   => [ 'type' => 'checkbox', 'label' => __( 'Reklamları göster', 'seopro' ), 'hint' => __( 'Tüm reklam bölgeleri için ana anahtar', 'seopro' ) ],
					'seopro_ads_lazy'     => [ 'type' => 'checkbox', 'label' => __( 'Tembel yükleme (hız)', 'seopro' ), 'hint' => __( 'Reklamlar sayfa yükünü yavaşlatmasın diye ilk etkileşim sonrası ve görünüme yaklaşınca yüklenir (inert <template> + IntersectionObserver). Önerilir.', 'seopro' ) ],
					'seopro_ad_in1'       => [ 'type' => 'adcode', 'label' => __( '★ İçerik-içi #1 — girişten sonra (en yüksek gelir)', 'seopro' ), 'desc' => __( 'AdSense\'te en yüksek CTR bölgesi. 300×250 veya responsive önerilir.', 'seopro' ) ],
					'seopro_ad_in1_after' => [ 'type' => 'number', 'label' => __( '#1: kaçıncı paragraftan sonra', 'seopro' ), 'desc' => __( 'Varsayılan 2.', 'seopro' ) ],
					'seopro_ad_in2'       => [ 'type' => 'adcode', 'label' => __( 'İçerik-içi #2 — orta', 'seopro' ), 'desc' => __( 'Okuma akışına gömülü. 300×250.', 'seopro' ) ],
					'seopro_ad_in2_after' => [ 'type' => 'number', 'label' => __( '#2: kaçıncı paragraftan sonra', 'seopro' ), 'desc' => __( 'Varsayılan 6.', 'seopro' ) ],
					'seopro_ad_header'    => [ 'type' => 'adcode', 'label' => __( 'Header altı (fold üstü)', 'seopro' ), 'desc' => __( 'Footer\'dan ~%25-40 daha yüksek CTR. 728×90 / responsive.', 'seopro' ) ],
					'seopro_ad_before'    => [ 'type' => 'adcode', 'label' => __( 'Yazı başlığı altı (içerik öncesi)', 'seopro' ), 'desc' => __( 'Fold üstü, içeriğe yakın.', 'seopro' ) ],
					'seopro_ad_after'     => [ 'type' => 'adcode', 'label' => __( 'İçerik sonu', 'seopro' ), 'desc' => __( 'Makale bitişi, ilgili yazılardan önce.', 'seopro' ) ],
					'seopro_ad_sidebar'   => [ 'type' => 'adcode', 'label' => __( 'Sidebar (sticky)', 'seopro' ), 'desc' => __( 'Masaüstü gelirinin %15-25\'i. 300×250 / 300×600.', 'seopro' ) ],
					'seopro_ad_mobile'    => [ 'type' => 'adcode', 'label' => __( 'Mobil alt sticky anchor', 'seopro' ), 'desc' => __( 'Güçlü mobil görüntülenebilirlik. Responsive.', 'seopro' ) ],
				],
			],
			'ampreklam'  => [
				'label'  => __( 'AMP Reklam', 'seopro' ),
				'fields' => [
					'seopro_amp_ads_enable' => [ 'type' => 'checkbox', 'label' => __( 'AMP reklamlarını göster', 'seopro' ), 'hint' => __( 'AMP sayfalarında standart AdSense JS çalışmaz; bu bölge amp-ad/amp-auto-ads kullanır. (AMP katmanı açık olmalı.)', 'seopro' ) ],
					'seopro_amp_ad_client'  => [ 'type' => 'text', 'label' => __( 'AdSense yayıncı ID', 'seopro' ), 'desc' => __( 'ca-pub-XXXXXXXXXXXXXXXX biçiminde. AdSense hesabından alınır.', 'seopro' ) ],
					'seopro_amp_ad_mode'    => [ 'type' => 'radio', 'label' => __( 'Yerleşim modu', 'seopro' ), 'choices' => [ 'auto' => __( 'Otomatik (amp-auto-ads — önerilen, Google yerleştirir)', 'seopro' ), 'manual' => __( 'Manuel (amp-ad — slot ID ile)', 'seopro' ) ] ],
					'seopro_amp_slot_top'   => [ 'type' => 'text', 'label' => __( 'Manuel: içerik üstü slot ID', 'seopro' ), 'desc' => __( 'Sadece manuel modda. AdSense reklam birimi slot numarası (data-ad-slot).', 'seopro' ) ],
					'seopro_amp_slot_bottom'=> [ 'type' => 'text', 'label' => __( 'Manuel: içerik altı slot ID', 'seopro' ), 'desc' => __( 'Sadece manuel modda.', 'seopro' ) ],
				],
			],
			'sosyal'     => [
				'label'  => __( 'Sosyal Medya', 'seopro' ),
				'fields' => [
					'seopro_social_x'         => [ 'type' => 'url', 'label' => 'X' ],
					'seopro_social_facebook'  => [ 'type' => 'url', 'label' => 'Facebook' ],
					'seopro_social_instagram' => [ 'type' => 'url', 'label' => 'Instagram' ],
					'seopro_social_youtube'   => [ 'type' => 'url', 'label' => 'YouTube' ],
					'seopro_social_linkedin'  => [ 'type' => 'url', 'label' => 'LinkedIn' ],
				],
			],
			'icerik'     => [
				'label'  => __( 'İçerik & Footer', 'seopro' ),
				'fields' => [
					'seopro_excerpt_length' => [ 'type' => 'number', 'label' => __( 'Özet kelime sayısı', 'seopro' ) ],
					'seopro_flipbar_enable' => [ 'type' => 'checkbox', 'label' => __( 'Üst bar dönen başlıklar', 'seopro' ), 'hint' => __( 'Tarihin yanında 3D dönen (flip) başlık göstergesi — kaymaz', 'seopro' ) ],
					'seopro_flipbar_source' => [ 'type' => 'select', 'label' => __( 'Dönen başlık kaynağı', 'seopro' ), 'choices' => [ 'latest' => __( 'Son yazılar', 'seopro' ), 'popular' => __( 'Popüler (yorum sayısı)', 'seopro' ), 'random' => __( 'Rastgele', 'seopro' ) ] ],
					'seopro_flipbar_count'  => [ 'type' => 'number', 'label' => __( 'Kaç başlık dönsün', 'seopro' ), 'desc' => __( 'Varsayılan 6.', 'seopro' ) ],
					'seopro_tabposts_show'   => [ 'type' => 'checkbox', 'label' => __( 'Sidebar sekmeli yazılar', 'seopro' ), 'hint' => __( 'Yan sütunda Popüler / Son / Rastgele sekmeli yazı listesini otomatik göster (widget eklemeye gerek yok)', 'seopro' ) ],
					'seopro_tabposts_style'  => [ 'type' => 'select', 'label' => __( 'Sekmeli yazılar görünümü', 'seopro' ), 'choices' => [ 'cards' => __( 'Resimli', 'seopro' ), 'list' => __( 'Sade liste (önceki tasarım, resimsiz)', 'seopro' ) ], 'desc' => __( 'Sekmeli yazıların görünümü. Sade liste, üç sekme açıkken de kullanılabilir.', 'seopro' ) ],
					'seopro_tabposts_popular' => [ 'type' => 'checkbox', 'label' => __( 'Sekme: Popüler', 'seopro' ), 'hint' => __( 'Popüler yazılar sekmesini göster', 'seopro' ) ],
					'seopro_tabposts_latest'  => [ 'type' => 'checkbox', 'label' => __( 'Sekme: Son yazılar', 'seopro' ), 'hint' => __( 'Son yazılar sekmesini göster', 'seopro' ) ],
					'seopro_tabposts_random'  => [ 'type' => 'checkbox', 'label' => __( 'Sekme: Rastgele', 'seopro' ), 'hint' => __( 'Rastgele yazılar sekmesini göster (tek sekme kalırsa sekme çubuğu gizlenir)', 'seopro' ) ],
					'seopro_footer_text'    => [ 'type' => 'textarea', 'label' => __( 'Footer telif metni', 'seopro' ) ],
				],
			],
		];
	}

	public function assets( string $hook ): void {
		if ( 'toplevel_page_seopro' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'seopro-admin', SEOPRO_URI . '/assets/css/admin.css', [ 'wp-color-picker' ], SEOPRO_VERSION );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();
		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(function($){' .
			'$(".seopro-color").wpColorPicker();' .
			'$(".seopro-media-btn").on("click",function(e){e.preventDefault();var b=$(this);var f=wp.media({title:"Görsel seç",button:{text:"Kullan"},multiple:false});f.on("select",function(){var a=f.state().get("selection").first().toJSON();var u=(a.sizes&&a.sizes.thumbnail)?a.sizes.thumbnail.url:a.url;b.siblings("input").val(a.id);b.siblings(".seopro-media-prev").html("<img src=\\""+u+"\\" style=\\"max-width:90px;height:auto;border-radius:6px\\">");});f.open();});' .
			'$(".seopro-media-clear").on("click",function(e){e.preventDefault();var b=$(this);b.siblings("input").val("");b.siblings(".seopro-media-prev").empty();});' .
			'});'
		);
	}

	public function render(): void {
		$groups = $this->groups();
		$tab    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : (string) array_key_first( $groups ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $groups[ $tab ] ) ) {
			$tab = (string) array_key_first( $groups );
		}
		?>
		<div class="wrap seopro-admin">
			<h1><?php esc_html_e( 'SeoPro Tema Ayarları', 'seopro' ); ?></h1>
			<p class="seopro-admin__links">
				<a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Özelleştirici (canlı önizleme)', 'seopro' ); ?></a>
				<?php if ( class_exists( '\WPContentBot\Plugin' ) ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wpcb-dashboard' ) ); ?>"><?php esc_html_e( 'İçerik Botu', 'seopro' ); ?></a>
				<?php endif; ?>
			</p>

			<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Ayarlar kaydedildi.', 'seopro' ); ?></p></div>
			<?php endif; ?>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $groups as $key => $group ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=seopro&tab=' . $key ) ); ?>" class="nav-tab <?php echo $key === $tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $group['label'] ); ?></a>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="seopro_save_settings">
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">
				<?php wp_nonce_field( 'seopro_save_settings' ); ?>
				<table class="form-table" role="presentation"><tbody>
					<?php
					foreach ( $groups[ $tab ]['fields'] as $key => $field ) {
						$this->field( $key, $field );
					}
					?>
				</tbody></table>
				<?php submit_button( __( 'Ayarları Kaydet', 'seopro' ) ); ?>
			</form>

			<p class="description"><?php esc_html_e( 'Bu ayarlar Özelleştirici ile aynı değerleri kullanır (theme_mod). İkisinden de düzenleyebilirsiniz.', 'seopro' ); ?></p>
		</div>
		<?php
	}

	private function field( string $key, array $f ): void {
		$val = Options::get( $key );
		echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';

		switch ( $f['type'] ) {
			case 'color':
				printf( '<input type="text" class="seopro-color" id="%1$s" name="%1$s" value="%2$s" data-default-color="%2$s">', esc_attr( $key ), esc_attr( (string) $val ) );
				break;
			case 'text':
				printf( '<input type="text" class="regular-text" id="%1$s" name="%1$s" value="%2$s">', esc_attr( $key ), esc_attr( (string) $val ) );
				break;
			case 'url':
				printf( '<input type="url" class="regular-text" id="%1$s" name="%1$s" value="%2$s" placeholder="https://">', esc_attr( $key ), esc_attr( (string) $val ) );
				break;
			case 'number':
				printf( '<input type="number" class="small-text" id="%1$s" name="%1$s" value="%2$s">', esc_attr( $key ), esc_attr( (string) $val ) );
				break;
			case 'textarea':
				printf( '<textarea class="large-text" rows="3" id="%1$s" name="%1$s">%2$s</textarea>', esc_attr( $key ), esc_textarea( (string) $val ) );
				break;
			case 'adcode':
				printf( '<textarea class="large-text code" rows="5" spellcheck="false" id="%1$s" name="%1$s" placeholder="%3$s" style="font-family:monospace;font-size:12px">%2$s</textarea>', esc_attr( $key ), esc_textarea( (string) $val ), esc_attr__( 'AdSense / reklam kodunu buraya yapıştırın (<script> dahil)', 'seopro' ) );
				break;
			case 'checkbox':
				printf( '<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s> %3$s</label>', esc_attr( $key ), checked( (bool) $val, true, false ), esc_html( $f['hint'] ?? '' ) );
				break;
			case 'radio':
				foreach ( $f['choices'] as $cv => $cl ) {
					printf( '<label style="margin-right:16px"><input type="radio" name="%1$s" value="%2$s" %3$s> %4$s</label>', esc_attr( $key ), esc_attr( (string) $cv ), checked( $val, $cv, false ), esc_html( $cl ) );
				}
				break;
			case 'select':
				echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
				foreach ( $f['choices'] as $cv => $cl ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( (string) $cv ), selected( $val, $cv, false ), esc_html( $cl ) );
				}
				echo '</select>';
				break;
			case 'media':
				$img = $val ? wp_get_attachment_image( (int) $val, 'thumbnail', false, [ 'style' => 'max-width:90px;height:auto;border-radius:6px' ] ) : '';
				printf(
					'<input type="hidden" name="%1$s" value="%2$s"><span class="seopro-media-prev">%3$s</span> <button class="button seopro-media-btn">%4$s</button> <button class="button seopro-media-clear">%5$s</button>',
					esc_attr( $key ),
					esc_attr( (string) $val ),
					$img, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_html__( 'Görsel seç', 'seopro' ),
					esc_html__( 'Temizle', 'seopro' )
				);
				break;
		}

		if ( ! empty( $f['desc'] ) ) {
			echo '<p class="description">' . esc_html( $f['desc'] ) . '</p>';
		}
		echo '</td></tr>';
	}

	public function save(): void {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Yetkiniz yok.', 'seopro' ) );
		}
		check_admin_referer( 'seopro_save_settings' );

		$groups = $this->groups();
		$tab    = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : '';

		if ( isset( $groups[ $tab ] ) ) {
			foreach ( $groups[ $tab ]['fields'] as $key => $f ) {
				$raw = $_POST[ $key ] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				switch ( $f['type'] ) {
					case 'color':
						set_theme_mod( $key, sanitize_hex_color( (string) $raw ) ?: '' );
						break;
					case 'text':
						set_theme_mod( $key, sanitize_text_field( wp_unslash( (string) $raw ) ) );
						break;
					case 'url':
						set_theme_mod( $key, esc_url_raw( wp_unslash( (string) $raw ) ) );
						break;
					case 'number':
						set_theme_mod( $key, absint( $raw ) );
						break;
					case 'textarea':
						set_theme_mod( $key, wp_kses_post( wp_unslash( (string) $raw ) ) );
						break;
					case 'adcode':
						set_theme_mod( $key, $this->sanitize_adcode( wp_unslash( (string) $raw ) ) );
						break;
					case 'checkbox':
						set_theme_mod( $key, isset( $_POST[ $key ] ) );
						break;
					case 'media':
						set_theme_mod( $key, absint( $raw ) );
						break;
					case 'radio':
					case 'select':
						$v = (string) $raw;
						set_theme_mod( $key, array_key_exists( $v, $f['choices'] ) ? $v : Options::get( $key ) );
						break;
				}
			}
		}

		wp_safe_redirect( add_query_arg( [ 'page' => self::SLUG, 'tab' => $tab, 'updated' => 1 ], admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Reklam kodunu güvenle kaydet.
	 *
	 * unfiltered_html olan kullanıcıda (tek-site admin) ham saklanır. Olmayanda
	 * (çok-site alt-yönetici veya unfiltered_html'in kapatıldığı kurulum)
	 * wp_kses_post AdSense'in <script>'ini silip kodu bozuyordu; bunun yerine
	 * reklam etiketlerine (script/ins/amp-ad…) izin veren özel allowlist kullan.
	 * Böylece "AMP eklenebiliyor ama normal reklam eklenemiyor" sorunu çözülür.
	 */
	private function sanitize_adcode( string $code ): string {
		$code = trim( $code );
		if ( '' === $code || current_user_can( 'unfiltered_html' ) ) {
			return $code;
		}

		$base = [ 'class' => true, 'id' => true, 'style' => true ];
		$data = [
			'data-ad-client'                => true,
			'data-ad-slot'                  => true,
			'data-ad-format'                => true,
			'data-full-width-responsive'    => true,
			'data-ad-layout'                => true,
			'data-ad-layout-key'            => true,
			'data-ad-region'                => true,
			'data-matched-content-ui-type'  => true,
			'data-matched-content-rows-num' => true,
			'data-matched-content-columns-num' => true,
			'data-auto-format'              => true,
		];

		$allowed = [
			'script'       => [ 'async' => true, 'defer' => true, 'src' => true, 'crossorigin' => true, 'type' => true ],
			'ins'          => array_merge( $base, $data ),
			'div'          => array_merge( $base, $data ),
			'span'         => $base,
			'a'            => [ 'href' => true, 'target' => true, 'rel' => true, 'class' => true, 'style' => true ],
			'img'          => [ 'src' => true, 'alt' => true, 'width' => true, 'height' => true, 'style' => true, 'loading' => true, 'class' => true ],
			'iframe'       => [ 'src' => true, 'width' => true, 'height' => true, 'frameborder' => true, 'scrolling' => true, 'style' => true, 'allow' => true, 'allowfullscreen' => true, 'loading' => true, 'class' => true, 'id' => true ],
			'amp-ad'       => [ 'width' => true, 'height' => true, 'type' => true, 'layout' => true, 'data-ad-client' => true, 'data-ad-slot' => true, 'data-auto-format' => true, 'data-full-width' => true, 'data-multi-size' => true, 'json' => true, 'rtc-config' => true ],
			'amp-auto-ads' => [ 'type' => true, 'data-ad-client' => true ],
			'amp-embed'    => [ 'width' => true, 'height' => true, 'type' => true, 'layout' => true, 'data-ad-client' => true, 'json' => true ],
		];

		return wp_kses( $code, $allowed );
	}
}
