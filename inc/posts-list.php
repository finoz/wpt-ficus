<?php
/**
 * Ficus — helper per il rendering delle liste di post.
 *
 * Struttura HTML garantita in ogni contesto (Query Loop e blocchi PHP):
 *
 *   div.{wrapper_class}
 *     ul.wp-block-post-template
 *       li.wp-block-post
 *         {contenuto del parziale card}
 *
 * Il Query Loop genera questa struttura automaticamente via WP core.
 * I blocchi PHP (related-posts, ecc.) devono replicarla usando
 * ficus_render_posts_list() per garantire coerenza.
 *
 * I child theme passano il proprio parziale card ($card_part_path)
 * e la classe container appropriata ($wrapper_class).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renderizza una lista di post con il parziale specificato.
 *
 * Deve essere chiamata DOPO get_block_wrapper_attributes() — ovvero
 * dentro il render callback di un blocco, dove il contesto WP_Block è attivo.
 *
 * @param WP_Query $query          Query già eseguita (have_posts() deve essere true).
 * @param string   $wrapper_class  Classe CSS del div contenitore (es. 'posts-grid').
 * @param string   $card_part      Path assoluto del file PHP del parziale card.
 * @return string  HTML completo della lista.
 */
function ficus_render_posts_list( WP_Query $query, string $wrapper_class, string $card_part ): string {
	if ( ! $query->have_posts() ) return '';

	ob_start();
	echo '<div ' . get_block_wrapper_attributes( [ 'class' => $wrapper_class ] ) . '>';
	echo '<ul class="wp-block-post-template">';
	while ( $query->have_posts() ) {
		$query->the_post();
		require $card_part;
	}
	wp_reset_postdata();
	echo '</ul></div>';
	return ob_get_clean();
}
