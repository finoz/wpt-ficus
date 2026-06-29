<?php
/**
 * Logica di enqueue degli asset Vite.
 *
 * Il parent NON aggangia direttamente wp_enqueue_scripts:
 * è compito del child theme chiamare ficus_enqueue_assets()
 * nel proprio hook, passando i path del child stesso.
 *
 * Esempio nel functions.php del child:
 *
 *   add_action( 'wp_enqueue_scripts', function () {
 *       ficus_enqueue_assets(
 *           get_stylesheet_directory(),
 *           get_stylesheet_directory_uri(),
 *           wp_get_theme()->get( 'Version' )
 *       );
 *   } );
 */

defined( 'ABSPATH' ) || exit;

/**
 * Legge il manifest Vite dal child theme.
 */
function ficus_get_vite_manifest( string $theme_dir ): array {
    static $manifests = [];
    if ( ! isset( $manifests[ $theme_dir ] ) ) {
        $path = $theme_dir . '/assets/dist/.vite/manifest.json';
        $manifests[ $theme_dir ] = file_exists( $path )
            ? json_decode( file_get_contents( $path ), true ) ?? []
            : [];
    }
    return $manifests[ $theme_dir ];
}

/**
 * Restituisce true se Vite è in modalità dev (manifest assente + WP_DEBUG).
 */
function ficus_is_vite_dev( string $theme_dir ): bool {
    return defined( 'WP_DEBUG' ) && WP_DEBUG && empty( ficus_get_vite_manifest( $theme_dir ) );
}

/**
 * Enqueue degli asset del child theme compilati da Vite.
 *
 * @param string $theme_dir  get_stylesheet_directory()
 * @param string $theme_uri  get_stylesheet_directory_uri()
 * @param string $version    wp_get_theme()->get('Version')
 */
function ficus_enqueue_assets( string $theme_dir, string $theme_uri, string $version ): void {

    if ( ficus_is_vite_dev( $theme_dir ) ) {
        $vite = 'http://localhost:5173';

        wp_enqueue_script( 'vite-client', $vite . '/@vite/client', [], null, false );
        wp_scripts()->add_data( 'vite-client', 'type', 'module' );

        wp_enqueue_script( 'ficus-main', $vite . '/ts/main.ts', [ 'vite-client' ], null, true );
        wp_scripts()->add_data( 'ficus-main', 'type', 'module' );

    } else {
        $manifest = ficus_get_vite_manifest( $theme_dir );
        $entry    = $manifest['ts/main.ts'] ?? null;

        if ( ! $entry ) return;

        wp_enqueue_script(
            'ficus-main',
            $theme_uri . '/assets/dist/' . $entry['file'],
            [], $version, true
        );
        wp_scripts()->add_data( 'ficus-main', 'type', 'module' );

        foreach ( $entry['css'] ?? [] as $i => $css_file ) {
            wp_enqueue_style(
                'ficus-style-' . $i,
                $theme_uri . '/assets/dist/' . $css_file,
                [], $version
            );
        }
    }

    // Rimuove stili WP superflui nei block themes
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'classic-theme-styles' );
}
