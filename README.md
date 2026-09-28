# SeoPro — WordPress Blog & Haber Teması

Çok amaçlı, performans öncelikli, eklenti bağımlılığı olmayan WordPress teması.
Native Customizer, dual-mode (blog/haber), otomatik schema, dahili AMP katmanı ve
kapsamlı lazy loading hedefiyle geliştirilir.

**Telif & İletişim:** Telif hakkı (c) 2026 [hazermedya.com](https://hazermedya.com) - Hikmet Anbarcı.
Kod lisansı **GPL-2.0-or-later** (tam metin: https://www.gnu.org/licenses/gpl-2.0.html); "SeoPro" adı, marka ve görsel tasarım
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
├── header.php / footer.php / index.php / single.php / archive.php …
├── template-parts/      # Yeniden kullanılabilir parçalar
├── page-templates/      # Özel sayfa şablonları (tam genişlik, yazarlar)
├── inc/
│   ├── core/            # Theme, Setup, Assets, Mode, DynamicCss, Toc…
│   ├── admin/           # Tema ayar paneli
│   ├── customizer/      # Customizer panelleri
│   ├── schema/          # Otomatik schema (JSON-LD)
│   ├── seo/             # Meta/OG etiketleri + kanonik URL
│   ├── amp/             # AMP katmanı (endpoint + sanitizer)
│   ├── lazyload/        # Lazy load / YouTube facade
│   ├── optimizer/       # Head temizliği, görsel kalitesi, watermark
│   ├── members/         # Üyelik kısa kodları
│   ├── security/        # Güvenlik başlıkları
│   └── widgets/
└── assets/css|js|fonts/
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
- [x] **Faz 10** — Tema admin paneli
- [ ] WPContentBot gömülü entegrasyonu — henüz yok. Panel yalnızca eklenti ayrıca
      kuruluysa bir kısayol düğmesi gösterir.

## Customizer ayar grupları
Site Modu · Renkler · Tipografi · Yerleşim · Anasayfa Blokları · Performans
(lazyload/AMP/schema) · Sosyal Medya · Footer. Renkler canlı önizlemeyle (postMessage)
CSS değişkenlerine bağlanır (`inc/core/class-dynamic-css.php`).

## Eksikler
- Çeviri dosyaları (`languages/`) ve demo içerik yok.
- AB/AEA, Birleşik Krallık ve İsviçre ziyaretçilerine AdSense reklamı göstermek için Google
  sertifikalı bir CMP (çerez/rıza aracı) gerekir (16 Ocak 2024'ten beri). Tema bunu içermez;
  ayrı bir CMP eklentisi kullanılmalı.
