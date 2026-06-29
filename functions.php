<?php
/**
 * Ficus — parent theme
 *
 * Questo file carica i moduli del parent.
 * Il child theme NON deve includere questo file: WordPress lo carica automaticamente.
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/assets.php';
require_once get_template_directory() . '/inc/block-styles.php';
require_once get_template_directory() . '/inc/updater.php';

// Aggiornamento automatico del parent da GitHub.
// Il repo deve essere pubblico oppure configurare un token in wp-config.php:
// define( 'FICUS_GITHUB_TOKEN', 'ghp_...' );
new Ficus_GitHub_Updater(
    'ficus',
    'finoz/wpt-ficus',
    wp_get_theme( 'ficus' )->get( 'Version' )
);
