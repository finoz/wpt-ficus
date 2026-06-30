<?php
/**
 * Logica di enqueue degli asset Vite.
 *
 * Il parent NON aggangia direttamente wp_enqueue_scripts:
 * è compito del child theme chiamare ficus_enqueue_assets()
 * nel proprio hook, passando i path del child stesso.
 *
 * Il child può definire la porta Vite con:
 *   define( 'FICUS_VITE_PORT', 5174 );
 * prima di chiamare ficus_enqueue_assets() — utile se si sviluppano
 * due child in parallelo su porte diverse.
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
 * Verifica se il dev server Vite è attivo sulla porta indicata.
 * Usa fsockopen: controlla se la porta risponde davvero,
 * indipendentemente da WP_DEBUG o dalla presenza del manifest.
 */
function ficus_is_vite_dev(): bool {
    static $cache = [];
    $port = defined( 'FICUS_VITE_PORT' ) ? (int) FICUS_VITE_PORT : 5173;

    if ( isset( $cache[ $port ] ) ) {
        return $cache[ $port ];
    }

    $conn = @fsockopen( 'localhost', $port, $errno, $errstr, 0.3 );
    if ( is_resource( $conn ) ) {
        fclose( $conn );
        return $cache[ $port ] = true;
    }

    return $cache[ $port ] = false;
}

/**
 * Enqueue degli asset del child theme compilati da Vite.
 *
 * @param string $theme_dir  get_stylesheet_directory()
 * @param string $theme_uri  get_stylesheet_directory_uri()
 * @param string $version    wp_get_theme()->get('Version')
 */
function ficus_enqueue_assets( string $theme_dir, string $theme_uri, string $version ): void {

    $port = defined( 'FICUS_VITE_PORT' ) ? (int) FICUS_VITE_PORT : 5173;

    if ( ficus_is_vite_dev() ) {
        $vite = 'http://localhost:' . $port;

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
