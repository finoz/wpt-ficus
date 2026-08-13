<?php
/**
 * Registrazione block styles e rimozione di quelli core non necessari.
 *
 * CONTRATTO CON IL CHILD THEME:
 * Ogni stile registrato qui aggiunge una classe CSS al markup (es. .is-style-outline),
 * ma non ha CSS proprio. Il child theme DEVE includere nel suo SCSS
 * gli stili per tutti i nomi elencati qui.
 *
 * Nomi registrati:
 *   core/button                                             → outline, text-link
 *   core/quote                                              → highlight
 *   core/group                                              → valign-top, valign-space-between
 *   core/cover                                              → valign-top, valign-space-between
 *   core/separator                                          → special
 *   core/gallery                                            → carousel, carousel-arrows, carousel-dots, grid-lightbox
 *   core/paragraph, core/heading, core/group, core/columns  → reading-width
 *
 * core/image → "rounded" rimosso via filtro JS (vedi sotto), non tramite
 * unregister_block_style(): quello stile è dichiarato nel block.json core,
 * non nel registry server-side.
 */

defined( 'ABSPATH' ) || exit;

// --- IMAGE ---
// "rounded" è dichiarato nel campo "styles" del block.json core, non nel
// registry server-side: unregister_block_style() non ha alcun effetto su di
// esso. wp.domReady() + unregisterBlockStyle() gira DOPO che wp-block-library
// ha già registrato core/image, senza garanzia di rimozione effettiva.
// Si intercetta quindi la registrazione stessa col filtro JS
// blocks.registerBlockType, agganciato "after" su wp-blocks: essendo
// wp-block-library dipendente da wp-blocks, questo codice gira sempre PRIMA
// che core/image venga registrato con lo stile "rounded".
add_action( 'enqueue_block_editor_assets', function () {
    wp_add_inline_script(
        'wp-blocks',
        "wp.hooks.addFilter( 'blocks.registerBlockType', 'ficus/remove-rounded-image-style', function ( settings, name ) {
            if ( name === 'core/image' && settings.styles ) {
                settings.styles = settings.styles.filter( function ( style ) { return style.name !== 'rounded'; } );
            }
            return settings;
        } );"
    );
} );

add_action( 'init', function () {

    // --- BUTTON ---
    register_block_style( 'core/button', [
        'name'  => 'outline',
        'label' => __( 'Outline', 'ficus' ),
    ] );
    register_block_style( 'core/button', [
        'name'  => 'text-link',
        'label' => __( 'Text link', 'ficus' ),
    ] );

    // --- QUOTE ---
    unregister_block_style( 'core/quote', 'plain' );
    register_block_style( 'core/quote', [
        'name'  => 'highlight',
        'label' => __( 'Highlight', 'ficus' ),
    ] );

    // --- GROUP ---
    register_block_style( 'core/group', [
        'name'  => 'valign-top',
        'label' => __( 'Valign: Top', 'ficus' ),
    ] );
    register_block_style( 'core/group', [
        'name'  => 'valign-space-between',
        'label' => __( 'Valign: Space between', 'ficus' ),
    ] );

    // --- COVER ---
    register_block_style( 'core/cover', [
        'name'  => 'valign-top',
        'label' => __( 'Valign: Top', 'ficus' ),
    ] );
    register_block_style( 'core/cover', [
        'name'  => 'valign-space-between',
        'label' => __( 'Valign: Space between', 'ficus' ),
    ] );

    // --- SEPARATOR ---
    register_block_style( 'core/separator', [
        'name'  => 'special',
        'label' => __( 'Special', 'ficus' ),
    ] );

    // --- GALLERY ---
    register_block_style( 'core/gallery', [
        'name'  => 'carousel',
        'label' => __( 'Carousel', 'ficus' ),
    ] );
    register_block_style( 'core/gallery', [
        'name'  => 'carousel-arrows',
        'label' => __( 'Carousel (solo frecce)', 'ficus' ),
    ] );
    register_block_style( 'core/gallery', [
        'name'  => 'carousel-dots',
        'label' => __( 'Carousel (solo dots)', 'ficus' ),
    ] );
    register_block_style( 'core/gallery', [
        'name'  => 'grid-lightbox',
        'label' => __( 'Griglia + Lightbox', 'ficus' ),
    ] );

    // --- READING WIDTH --- (testo e contenuto)
    foreach ( [ 'core/paragraph', 'core/heading', 'core/group', 'core/columns' ] as $block ) {
        register_block_style( $block, [
            'name'  => 'reading-width',
            'label' => __( 'Reading', 'ficus' ),
        ] );
    }
} );
