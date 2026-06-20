<?php
/**
 * Üst bar dönen başlık göstergesi (flip ticker).
 *
 * Kaymaz; her başlık 3D kart/küp gibi dönerek (rotateX flip) değişir.
 * Kaynak panelden seçilir: son yazılar / popüler (yorum sayısı) / rastgele.
 *
 * @package SeoPro
 */

defined( 'ABSPATH' ) || exit;

use SeoPro\Core\Options;
use SeoPro\Core\Helpers;

if ( ! Options::bool( 'seopro_flipbar_enable' ) ) {
	return;
}

$seopro_src   = (string) Options::get( 'seopro_flipbar_source' );
$seopro_count = max( 2, min( 15, (int) Options::get( 'seopro_flipbar_count' ) ) );

$seopro_args = [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $seopro_count,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
];
if ( 'popular' === $seopro_src ) {
	$seopro_args['orderby'] = 'comment_count';
	$seopro_args['order']   = 'DESC';
} elseif ( 'random' === $seopro_src ) {
	$seopro_args['orderby'] = 'rand';
} else {
	$seopro_args['orderby'] = 'date';
	$seopro_args['order']   = 'DESC';
}

// Başlık+URL listesini kaynağa göre cache'le. popular/random kaynakları her
// istekte filesort/RAND() yapardı; latest zaten ucuz ama cache sorgu sayısını
// yine düşürür. Yalnız hafif veri (başlık+permalink) saklanır.
$seopro_items = \SeoPro\Core\Cache::remember(
	'flipbar_' . $seopro_src . '_' . $seopro_count,
	15 * MINUTE_IN_SECONDS,
	static function () use ( $seopro_args ) {
		$q     = new WP_Query( $seopro_args );
		$items = [];
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$items[] = [
					't' => get_the_title(),
					'u' => get_permalink(),
				];
			}
		}
		wp_reset_postdata();
		return $items;
	}
);

if ( empty( $seopro_items ) ) {
	return;
}

$seopro_labels = [
	'popular' => __( 'Popüler', 'seopro' ),
	'random'  => __( 'Öne çıkan', 'seopro' ),
	'latest'  => __( 'Son yazılar', 'seopro' ),
];
$seopro_label = $seopro_labels[ $seopro_src ] ?? $seopro_labels['latest'];
$seopro_first = $seopro_items[0];
?>
<div class="seopro-flipbar" data-seopro-flip data-interval="4000">
	<span class="seopro-flipbar__icon"><?php echo Helpers::icon( 'bolt' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<span class="screen-reader-text"><?php echo esc_html( $seopro_label ); ?>:</span>
	<a class="seopro-flipbar__line" href="<?php echo esc_url( $seopro_first['u'] ); ?>"><?php echo esc_html( $seopro_first['t'] ); ?></a>
	<script type="application/json" class="seopro-flipbar__data"><?php echo wp_json_encode( $seopro_items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
</div>
