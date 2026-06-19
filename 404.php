<?php

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="seopro-container seopro-404">
	<h1 class="seopro-404__code">404</h1>
	<p class="seopro-404__text"><?php esc_html_e( 'Aradığınız sayfa bulunamadı.', 'seopro' ); ?></p>
	<?php get_search_form(); ?>
	<p><a class="seopro-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Anasayfaya dön', 'seopro' ); ?></a></p>
</div>
<?php
get_footer();
