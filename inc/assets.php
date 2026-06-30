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
 * Verifica se il dev server Vite è attivo.
 *
 * Usa un file sentinel .vite-dev creato da vite.config.ts quando
 * il dev server parte e rimosso quando si ferma. Questo approccio
 * funziona sia in sviluppo nativo che in Docker (dove fsockopen
 * da dentro il container non raggiunge il Mac host).
 *
 * Gate WP_DEBUG: in produzione non si entra mai in dev mode
 * anche se il file esistesse per errore.
 * Override esplicito: define('FICUS_VITE_DEV', true/false) in wp-config.
 */
function ficus_is_vite_dev(): bool {
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return false;
    if ( defined( 'FICUS_VITE_DEV' ) ) return (bool) FICUS_VITE_DEV;
    return file_exists( get_stylesheet_directory() . '/.vite-dev' );
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
        $vite      = 'http://localhost:' . $port;
        $entry_url = $vite . '/ts/main.ts';

        // Vite inietta /@vite/client automaticamente nel modulo — non serve enqueue separato.
        // Usiamo script_loader_tag per emettere un tag <script type="module"> affidabile:
        // add_data('type','module') non funziona in tutti i contesti WP.
        wp_enqueue_script( 'ficus-main', $entry_url, [], null, true );
        add_filter( 'script_loader_tag', function ( $tag, $handle ) use ( $entry_url ) {
            if ( $handle !== 'ficus-main' ) return $tag;
            return '<script type="module" src="' . esc_url( $entry_url ) . '"></script>' . "\n";
        }, 10, 2 );

    } else {
        $manifest = ficus_get_vite_manifest( $theme_dir );
        $entry    = $manifest['ts/main.ts'] ?? null;

        if ( ! $entry ) return;

        $js_url = $theme_uri . '/assets/dist/' . $entry['file'];
        wp_enqueue_script( 'ficus-main', $js_url, [], $version, true );
        add_filter( 'script_loader_tag', function ( $tag, $handle ) use ( $js_url ) {
            if ( $handle !== 'ficus-main' ) return $tag;
            return '<script type="module" src="' . esc_url( $js_url ) . '"></script>' . "\n";
        }, 10, 2 );

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

/**
 * Carica il CSS nell'editor FSE (iframe Gutenberg).
 *
 * Chiamare in after_setup_theme.
 *
 * Dev mode: carica il modulo Vite via enqueue_block_editor_assets.
 * Vite inietta <style> nell'head del documento admin, e Gutenberg (WP 6.3+)
 * ha un MutationObserver che copia automaticamente tutti i nuovi <style> e
 * <link> nell'iframe FSE — stesso meccanismo usato per la backward compat
 * di plugin che usano enqueue_block_editor_assets.
 *
 * Prod mode: add_editor_style() con il CSS dal manifest, iniettato
 * direttamente da WP nell'iframe.
 *
 * @param string $theme_dir  get_stylesheet_directory()
 * @param string $theme_uri  get_stylesheet_directory_uri()
 */
function ficus_add_editor_styles( string $theme_dir, string $theme_uri ): void {
    if ( ficus_is_vite_dev() ) {
        // Dev mode: JS inline che trova gli iframe dell'editor (same-origin)
        // e inietta un <link> al CSS Vite nel loro contentDocument.
        // Vite serve i file SCSS come CSS puro quando richiesti con Accept: text/css
        // (come fa un <link rel="stylesheet">), quindi l'URL diretto funziona.
        $port    = defined( 'FICUS_VITE_PORT' ) ? (int) FICUS_VITE_PORT : 5173;
        $css_url = 'http://localhost:' . $port . '/scss/main.scss';

        add_action( 'enqueue_block_editor_assets', function () use ( $css_url ) {
            $js = <<<JS
(function(){
    var CSS_URL = '{$css_url}';
    var STYLE_ID = 'ficus-vite-editor-css';

    function inject() {
        document.querySelectorAll('iframe').forEach(function(frame) {
            try {
                var doc = frame.contentDocument;
                if (!doc || !doc.head || doc.getElementById(STYLE_ID)) return;
                var link = doc.createElement('link');
                link.rel = 'stylesheet';
                link.href = CSS_URL;
                link.id   = STYLE_ID;
                doc.head.appendChild(link);
            } catch(e) {}
        });
    }

    // Prova subito e a intervalli mentre l'editor carica
    [100, 500, 1000, 2000].forEach(function(ms){ setTimeout(inject, ms); });

    // Osserva nuovi iframe (es. quando si apre il site editor)
    new MutationObserver(function(muts) {
        muts.forEach(function(m) {
            m.addedNodes.forEach(function(n) {
                if (n.nodeName === 'IFRAME' ||
                    (n.querySelector && n.querySelector('iframe'))) {
                    setTimeout(inject, 200);
                }
            });
        });
    }).observe(document.body, { childList: true, subtree: true });
})();
JS;
            wp_add_inline_script( 'wp-blocks', $js, 'after' );
        } );
        return;
    }

    // Prod mode: add_editor_style() con il CSS dal manifest.
    $manifest = ficus_get_vite_manifest( $theme_dir );
    $entry    = $manifest['ts/main.ts'] ?? null;
    if ( ! $entry ) return;
    foreach ( $entry['css'] ?? [] as $css_file ) {
        add_editor_style( $theme_uri . '/assets/dist/' . $css_file );
    }
}
