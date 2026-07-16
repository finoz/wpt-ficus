<?php
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [
        'search-form', 'comment-form', 'comment-list',
        'gallery', 'caption', 'style', 'script',
    ] );

    add_image_size( 'card', 800, 500, true );
    add_image_size( 'hero', 1600, 800, true );

    load_theme_textdomain( 'ficus', get_template_directory() . '/languages' );
} );

// ── Block styles — rimossi per controllo CSS totale dal child theme ──────────

add_theme_support( 'disable-layout-styles' );

add_action( 'wp_enqueue_scripts', function () {
    $handles = [
        'wp-block-library',
        'wp-block-library-theme',
        'wp-block-site-logo',
        'wp-block-page-list',
        'wp-block-navigation',
        'wp-block-group',
        'wp-block-button',
        'wp-block-buttons',
        'wp-block-heading',
        'wp-block-paragraph',
        'wp-block-post-content',
        'wp-block-site-title',
        'wp-block-query',
        'wp-block-post-template',
        'wp-block-post-featured-image',
        'wp-block-post-title',
        'wp-block-post-excerpt',
        'wp-block-post-terms',
        'wp-block-post-date',
        'wp-block-columns',
        'wp-block-column',
        'wp-block-image',
        'wp-block-separator',
        'wp-block-quote',
    ];
    foreach ( $handles as $handle ) {
        wp_dequeue_style( $handle );
        wp_deregister_style( $handle );
    }
}, 200 );

// ── Pulizia <head> ────────────────────────────────────────────────────────────

add_action( 'after_setup_theme', function () {
    remove_action( 'wp_head',        'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head',        'wp_generator' );
    remove_action( 'wp_head',        'rsd_link' );
    remove_action( 'wp_head',        'rest_output_link_wp_head' );
    remove_action( 'wp_head',        'wp_oembed_add_host_js' );
    remove_action( 'wp_body_open',   'wp_global_styles_render_svg_filters' );
} );

// ── Commenti — disabilitati a livello tema ────────────────────────────────────

add_action( 'init', function () {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
} );

add_filter( 'comments_open',  '__return_false', 20, 2 );
add_filter( 'pings_open',     '__return_false', 20, 2 );
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

add_action( 'admin_menu', function () {
    remove_menu_page( 'edit-comments.php' );                          // voce "Commenti" sidebar
    remove_submenu_page( 'options-general.php', 'options-discussion.php' ); // voce "Discussione" in Impostazioni
} );

// ── Dimensioni media — setup automatico all'attivazione tema ─────────────────
// Gira una sola volta: non sovrascrive se l'admin ha cambiato i valori a mano.

add_action( 'after_switch_theme', function () {

    // Miniatura: 600×600, crop centrato
    update_option( 'thumbnail_size_w', 600 );
    update_option( 'thumbnail_size_h', 600 );
    update_option( 'thumbnail_crop',   1   );

    // Media: 1200 di larghezza, altezza proporzionale
    update_option( 'medium_size_w', 1200 );
    update_option( 'medium_size_h', 0    );

    // Grande: 2400 di larghezza, altezza proporzionale
    update_option( 'large_size_w', 2400 );
    update_option( 'large_size_h', 0    );
} );
