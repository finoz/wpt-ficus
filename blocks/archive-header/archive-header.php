<?php
/**
 * Block render: ficus/archive-header
 *
 * Gestisce due casi in un unico blocco:
 *
 * is_home() — pagina degli articoli (Impostazioni › Lettura):
 *   legge titolo ed excerpt dalla pagina impostata come page_for_posts.
 *   Necessario perché in quel contesto il loop WP è già sugli articoli,
 *   quindi i blocchi nativi wp:post-title / wp:post-excerpt leggerebbero
 *   il primo articolo, non la pagina.
 *
 * is_archive() — tassonomie, autori, date:
 *   usa get_the_archive_title() e get_the_archive_description(),
 *   le funzioni WP standard che leggono dal termine/autore/data corrente.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

if ( is_home() ) {

    $page_id = (int) get_option( 'page_for_posts' );
    if ( ! $page_id ) return;

    $title = get_the_title( $page_id );
    $intro = get_the_excerpt( $page_id );

} else {

    $title = get_the_archive_title();
    $intro = get_the_archive_description();

}

if ( ! $title ) return;
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'archive-header' ] ); ?>>
	<h1 class="archive-header-title"><?php echo esc_html( $title ); ?></h1>
	<?php if ( $intro ) : ?>
	<p class="archive-header-intro"><?php echo wp_kses_post( $intro ); ?></p>
	<?php endif; ?>
</div>
