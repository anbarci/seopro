<?php

defined( 'ABSPATH' ) || exit;
?>
<div class="seopro-card seopro-card--empty">
	<h2 class="seopro-card__title"><?php esc_html_e( 'Bir şey bulunamadı.', 'seopro' ); ?></h2>
	<p><?php esc_html_e( 'Aramanızı değiştirip tekrar deneyebilirsiniz.', 'seopro' ); ?></p>
	<?php get_search_form(); ?>
</div>
