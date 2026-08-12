<?php
/**
 * Parziale: card post — variante default
 *
 * Incluso dentro un loop WP attivo (while have_posts / the_post già chiamato).
 * Le funzioni the_title(), the_permalink(), ecc. leggono il post corrente.
 */

$cats = get_the_category();
$cat_links = array_map(
	fn( $cat ) => sprintf(
		'<a href="%s">%s</a>',
		esc_url( get_category_link( $cat->term_id ) ),
		esc_html( $cat->name )
	),
	$cats
);
?>
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

			<?php if ( ! empty( $cats ) ) : ?>
			<div class="wp-block-post-terms post-card__category">
				<?php echo implode( '', $cat_links ); ?>
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
