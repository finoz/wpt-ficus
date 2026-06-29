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
 *   core/button   → outline, text-link
 *   core/image    → rounded
 *   core/separator→ leaf         (rimossi: wide, dots)
 *   core/quote    → highlight    (rimosso: plain)
 *   core/group    → card, section
 */

defined( 'ABSPATH' ) || exit;

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

    // --- IMAGE ---
    register_block_style( 'core/image', [
        'name'  => 'rounded',
        'label' => __( 'Rounded', 'ficus' ),
    ] );

    // --- SEPARATOR ---
    unregister_block_style( 'core/separator', 'wide' );
    unregister_block_style( 'core/separator', 'dots' );
    register_block_style( 'core/separator', [
        'name'  => 'leaf',
        'label' => __( 'Leaf', 'ficus' ),
    ] );

    // --- QUOTE ---
    unregister_block_style( 'core/quote', 'plain' );
    register_block_style( 'core/quote', [
        'name'  => 'highlight',
        'label' => __( 'Highlight', 'ficus' ),
    ] );

    // --- GROUP ---
    register_block_style( 'core/group', [
        'name'  => 'card',
        'label' => __( 'Card', 'ficus' ),
    ] );
    register_block_style( 'core/group', [
        'name'  => 'section',
        'label' => __( 'Section', 'ficus' ),
    ] );
} );
