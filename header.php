<?php

defined( 'ABSPATH' ) || exit;

use SeoPro\Core\Options;
use SeoPro\Core\Helpers;

$seopro_sticky  = Options::bool( 'seopro_sticky_header' ) ? ' seopro-site-header--sticky' : '';
$seopro_socials = Options::social_links();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<script>
	(function () {
		try {
			var mode = localStorage.getItem('seopro-theme');
			if (mode !== 'light' && mode !== 'dark') {
				mode = '<?php echo esc_js( Options::get( 'seopro_default_theme' ) ); ?>';
				if (window.matchMedia('(prefers-color-scheme: dark)').matches) { mode = 'dark'; }
			}
			document.documentElement.setAttribute('data-theme', mode);
		} catch (e) {}
	})();
	</script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="seopro-skip-link" href="#seopro-main"><?php esc_html_e( 'İçeriğe geç', 'seopro' ); ?></a>

<div class="seopro-topbar">
	<div class="seopro-container seopro-topbar__inner">
		<div class="seopro-topbar__left">
			<span class="seopro-topbar__date"><?php echo esc_html( wp_date( 'j F Y, l' ) ); ?></span>
			<?php get_template_part( 'template-parts/flipbar' ); ?>
		</div>
		<div class="seopro-topbar__right">
			<?php if ( $seopro_socials ) : ?>
				<div class="seopro-topbar__social">
					<?php foreach ( $seopro_socials as $key => $social ) : ?>
						<a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer me" aria-label="<?php echo esc_attr( $social['label'] ); ?>"><?php echo Helpers::social_icon( $key ); // phpcs:ignore ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<header class="seopro-site-header<?php echo esc_attr( $seopro_sticky ); ?>" role="banner">
	<div class="seopro-container seopro-site-header__inner">
		<div class="seopro-branding">
			<?php the_custom_logo(); ?>
			<p class="seopro-site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
		</div>

		<nav class="seopro-nav seopro-nav--primary" aria-label="<?php esc_attr_e( 'Ana menü', 'seopro' ); ?>">
			<?php
			wp_nav_menu( [
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'seopro-nav__list',
				'fallback_cb'    => false,
				'depth'          => 2,
				'walker'         => new \SeoPro\Core\NavWalker(),
			] );
			?>
		</nav>

		<div class="seopro-header-tools">
			<button type="button" class="seopro-icon-btn" data-seopro-search-toggle aria-label="<?php esc_attr_e( 'Ara', 'seopro' ); ?>"><?php echo Helpers::icon( 'search' ); // phpcs:ignore ?></button>
			<button type="button" class="seopro-icon-btn seopro-theme-toggle" data-seopro-theme-toggle aria-pressed="false" aria-label="<?php esc_attr_e( 'Koyu / açık tema', 'seopro' ); ?>">
				<span class="seopro-theme-toggle__moon" aria-hidden="true"><?php echo Helpers::icon( 'moon' ); // phpcs:ignore ?></span>
				<span class="seopro-theme-toggle__sun" aria-hidden="true"><?php echo Helpers::icon( 'sun' ); // phpcs:ignore ?></span>
			</button>
			<button type="button" class="seopro-icon-btn seopro-nav-toggle" data-seopro-nav-toggle aria-expanded="false" aria-controls="seopro-mobile-nav" aria-label="<?php esc_attr_e( 'Menü', 'seopro' ); ?>"><span class="seopro-nav-toggle__bar" aria-hidden="true"></span></button>
		</div>
	</div>
</header>

<?php if ( has_nav_menu( 'primary' ) === false ) : ?>
<div class="seopro-catbar">
	<div class="seopro-container">
		<ul class="seopro-catbar__list">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Anasayfa', 'seopro' ); ?></a></li>
			<?php wp_list_categories( [ 'title_li' => '', 'number' => 10, 'orderby' => 'count', 'order' => 'DESC' ] ); ?>
		</ul>
	</div>
</div>
<?php endif; ?>

<div class="seopro-search-overlay" data-seopro-search hidden>
	<button type="button" class="seopro-search-overlay__close" data-seopro-search-close aria-label="<?php esc_attr_e( 'Kapat', 'seopro' ); ?>"><?php echo Helpers::icon( 'close' ); // phpcs:ignore ?></button>
	<div class="seopro-search-overlay__inner">
		<span class="seopro-search-overlay__title"><?php esc_html_e( 'Ne aramıştınız?', 'seopro' ); ?></span>
		<?php get_search_form(); ?>
		<?php
		$seopro_pop = get_categories( [ 'number' => 6, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ] );
		if ( $seopro_pop ) :
			?>
			<div class="seopro-search-overlay__pop">
				<span class="seopro-search-overlay__pop-label"><?php esc_html_e( 'Popüler:', 'seopro' ); ?></span>
				<?php foreach ( $seopro_pop as $seopro_c ) : ?>
					<a href="<?php echo esc_url( get_category_link( $seopro_c->term_id ) ); ?>"><?php echo esc_html( $seopro_c->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<div id="seopro-mobile-nav" class="seopro-mobile-nav" hidden>
	<div class="seopro-mobile-nav__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Mobil menü', 'seopro' ); ?>">
		<button type="button" class="seopro-mobile-nav__close" data-seopro-nav-close aria-label="<?php esc_attr_e( 'Kapat', 'seopro' ); ?>">&times;</button>
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( [ 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'seopro-mobile-nav__list', 'fallback_cb' => false, 'depth' => 2 ] );
		} else {
			echo '<ul class="seopro-mobile-nav__list">';
			wp_list_categories( [ 'title_li' => '', 'number' => 12 ] );
			echo '</ul>';
		}
		?>
		<?php get_search_form(); ?>
	</div>
</div>

<?php do_action( 'seopro_after_header' ); ?>

<main id="seopro-main" class="seopro-main" role="main">
