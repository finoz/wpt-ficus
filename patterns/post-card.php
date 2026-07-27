<?php
/**
 * Title: Card Articolo
 * Slug: ficus/post-card
 * Categories: posts
 * Block Types: core/post-template
 * Description: Card verticale: immagine featured, categoria, titolo, abstract.
 */
?>
<!-- wp:group {"className":"post-card","layout":{"type":"default"}} -->
<div class="wp-block-group post-card">
	<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->
	<!-- wp:group {"className":"post-card__body","layout":{"type":"default"}} -->
	<div class="wp-block-group post-card__body">
		<!-- wp:post-terms {"term":"category","className":"post-card__category"} /-->
		<!-- wp:post-title {"isLink":true,"level":3,"className":"post-card__title"} /-->
		<!-- wp:post-excerpt {"moreText":"","className":"post-card__excerpt"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
