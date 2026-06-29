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
