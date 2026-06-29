<?php
/**
 * CHILDNAME — child theme di Ficus
 */

defined( 'ABSPATH' ) || exit;

// Enqueue degli asset Vite (logica definita nel parent inc/assets.php)
add_action( 'wp_enqueue_scripts', function () {
    ficus_enqueue_assets(
        get_stylesheet_directory(),
        get_stylesheet_directory_uri(),
        wp_get_theme()->get( 'Version' )
    );
} );

// Aggiornamento automatico del child da GitHub
// (richiede Ficus_GitHub_Updater dal parent, già caricato da WP)
new Ficus_GitHub_Updater(
    'CHILDNAME',
    'finoz/wpt-CHILDNAME',
    wp_get_theme()->get( 'Version' )
);
