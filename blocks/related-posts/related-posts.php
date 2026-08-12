<?php
/**
 * Block render: ficus/related-posts
 *
 * Mostra i post della stessa categoria del post corrente.
 * Usa ficus_render_posts_list() per garantire lo stesso markup
 * del Query Loop nei template HTML.
 *
 * @var array    $attributes Attributi del blocco (count).
 * @var string   $content    Contenuto interno (vuoto, blocco dinamico).
 * @var WP_Block $block      Istanza del blocco (context: postId).
 */

$post_id = $block->context['postId'] ?? get_the_ID();
$count   = max( 1, intval( $attributes['count'] ?? 3 ) );

$categories = get_the_category( $post_id );
if ( empty( $categories ) ) return;

$query = new WP_Query( [
	'category__in'   => wp_list_pluck( $categories, 'term_id' ),
	'post__not_in'   => [ $post_id ],
	'posts_per_page' => $count,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
	'no_found_rows'  => true,
] );

if ( ! $query->have_posts() ) return;

echo ficus_render_posts_list(
	$query,
	'posts-grid',
	get_template_directory() . '/parts/post-card.php'
);
