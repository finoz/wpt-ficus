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
