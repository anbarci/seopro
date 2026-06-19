# SeoPro — WordPress Blog & Haber Teması

Çok amaçlı, performans öncelikli, eklenti bağımlılığı olmayan WordPress teması.
Native Customizer, dual-mode (blog/haber), otomatik schema, dahili AMP katmanı ve
kapsamlı lazy loading hedefiyle geliştirilir.

**Telif & İletişim:** Telif hakkı (c) 2026 [hazermedya.com](https://hazermedya.com) - Hikmet Anbarcı.
Kod lisansı **GPL-2.0-or-later** (bkz. `license.txt`); "SeoPro" adı, marka ve görsel tasarım
hazermedya.com'a aittir. Haklar, lisanslama ve iş birliği için: **https://hazermedya.com**

## Teknik temel
- PHP 8.2+ · OOP · `SeoPro\` namespace · PSR-4 benzeri autoloader (`functions.php`)
- Native WP Customizer / Settings API (3rd-party framework yok)
- Vanilla JS (jQuery yok) · CSS custom properties · mobile-first
- Text-domain: `seopro`

## Klasör yapısı
```
seopro/
├── style.css            # Tema başlığı (stiller assets/css altında)
├── functions.php        # Bootstrap + autoloader
├── header.php / footer.php / index.php
├── template-parts/      # Yeniden kullanılabilir parçalar
├── page-templates/      # Özel sayfa şablonları
├── templates/
│   ├── standard/        # Normal render
│   └── amp/             # AMP render (Faz 7)
├── inc/
│   ├── core/            # Theme, Setup, Assets, Mode
│   ├── customizer/      # Customizer panelleri (Faz 1)
│   ├── schema/          # Otomatik schema (Faz 6)
│   ├── amp/             # AMP katmanı (Faz 7)
│   ├── lazyload/        # Lazy load motoru (Faz 5)
│   ├── widgets/ shortcodes/ admin/ optimizer/ license/ mode-switcher/
├── assets/css|js|fonts|img|icons/
├── languages/
└── demos/
```

## Geliştirme yol haritası
- [x] **Faz 0** — Temel iskelet (bootstrap, token'lar, dark/light, header/footer)
- [x] **Faz 1** — Customizer altyapısı + dinamik CSS + Blog/Haber mode switch
- [x] **Faz 2** — Template hiyerarşisi + semantic markup + dual-mode layout
- [x] **Faz 3** — Header/Footer/Nav (mega menü, mobil, sticky)
- [x] **Faz 4** — Anasayfa blok sistemi (Customizer'dan sıralanır)
- [x] **Faz 5** — Lazy loading motoru (img/iframe facade) + optimizer
- [x] **Faz 6** — Otomatik schema (JSON-LD) + OG/Twitter (SEO eklenti çakışma koruması)
- [x] **Faz 7** — Dahili AMP katmanı (`/amp/` endpoint + sanitizer)
- [x] **Faz 8** — Widget'lar + özel sayfa şablonları
- [x] **Faz 9** — Üye sistemi (shortcode) + güvenlik başlıkları + a11y
- [x] **Faz 10** — Tema admin paneli + native **WPContentBot** entegrasyonu

## WPContentBot entegrasyonu
İçerik botu eklentisi temaya `inc/content-bot/` altında **gömülüdür** (ayrı eklenti
gerektirmez). `SeoPro\Core\ContentBot` sınıfı WPCB sabitlerini tanımlar, kendi
autoloader'ını kaydeder ve motoru `after_setup_theme`'de başlatır; tablolar
`after_switch_theme`'de kurulur. Bot admin sayfaları, üst seviye **SeoPro** tema
menüsünün altına submenu olarak yerleşir (`seopro_contentbot_parent` filtresiyle
değiştirilebilir; `false` dönerse eski bağımsız menüye düşer).

## Customizer ayar grupları
Site Modu · Renkler · Tipografi · Yerleşim · Anasayfa Blokları · Performans
(lazyload/AMP/schema) · Sosyal Medya · Footer. Renkler canlı önizlemeyle (postMessage)
CSS değişkenlerine bağlanır (`inc/core/class-dynamic-css.php`).

## Çalışma alanı
`screenshot.png` (1200×900) henüz eklenmedi. Demo içerik import dosyaları `demos/`
altına eklenecek.
