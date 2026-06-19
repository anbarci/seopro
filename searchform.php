<?php

defined( 'ABSPATH' ) || exit;

$seopro_id = 'seopro-search-' . wp_unique_id();
?>
<form role="search" method="get" class="seopro-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $seopro_id ); ?>" class="screen-reader-text"><?php esc_html_e( 'Ara:', 'seopro' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $seopro_id ); ?>" class="seopro-search-form__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Ara…', 'seopro' ); ?>" />
	<button type="submit" class="seopro-search-form__submit"><?php esc_html_e( 'Ara', 'seopro' ); ?></button>
</form>
