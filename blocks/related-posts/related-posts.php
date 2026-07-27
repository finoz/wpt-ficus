<?php
/**
 * Block render: ficus/related-posts
 *
 * Mostra i post della stessa categoria del post corrente.
 * Il markup replica quello della card del Query Loop (pattern ficus/post-card)
 * così il CSS è unico e non duplicato.
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
	'no_found_rows'  => true, // non serve il conteggio totale
] );

if ( ! $query->have_posts() ) return;
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'posts-grid' ] ); ?>>
	<ul class="wp-block-post-template is-layout-grid">
		<?php while ( $query->have_posts() ) : $query->the_post(); ?>
		<li class="wp-block-post post type-post">
			<div class="wp-block-group post-card">

				<?php if ( has_post_thumbnail() ) : ?>
				<figure class="wp-block-post-featured-image">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'card' ); ?>
					</a>
				</figure>
				<?php endif; ?>

				<div class="wp-block-group post-card__body">

					<?php
					$cats = get_the_category();
					if ( ! empty( $cats ) ) :
						$cat_links = array_map(
							fn( $cat ) => sprintf(
								'<a href="%s">%s</a>',
								esc_url( get_category_link( $cat->term_id ) ),
								esc_html( $cat->name )
							),
							$cats
						);
					?>
					<div class="wp-block-post-terms post-card__category">
						<?php echo implode( ', ', $cat_links ); ?>
					</div>
					<?php endif; ?>

					<h3 class="wp-block-post-title post-card__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>

					<?php $excerpt = get_the_excerpt(); if ( $excerpt ) : ?>
					<div class="wp-block-post-excerpt post-card__excerpt">
						<p class="wp-block-post-excerpt__excerpt"><?php echo esc_html( $excerpt ); ?></p>
					</div>
					<?php endif; ?>

				</div>
			</div>
		</li>
		<?php endwhile; wp_reset_postdata(); ?>
	</ul>
</div>
