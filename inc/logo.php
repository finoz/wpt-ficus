<?php
/**
 * Sistema logo con fallback per child theme.
 *
 * Flusso:
 * 1. Il blocco wp:site-logo renderizza il logo caricato in admin (Settings > Identity).
 * 2. Se nessun logo admin è impostato, il blocco ritorna stringa vuota.
 * 3. Il filtro render_block_core/site-logo intercetta il caso vuoto
 *    e chiama ficus_default_logo_html.
 * 4. Il callback di default (qui sotto) cerca per convenzione il file
 *    assets/images/logo.{png,svg,webp,jpg} nella cartella del child theme.
 *    Il child non deve scrivere nessun PHP: basta mettere il file nel posto giusto.
 * 5. Se il child ha esigenze particolari (alt diverso, dimensioni esplicite, ecc.)
 *    può sovrascrivere il filtro con priorità più alta.
 *
 * Se il file non esiste e nessun logo admin è impostato,
 * il blocco non renderizza nulla — comportamento standard WP.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Helper: genera l'HTML del logo coerente con il markup di wp:site-logo.
 * Usarlo nel filtro ficus_default_logo_html garantisce markup uniforme.
 *
 * @param string $src  URL assoluto del file immagine (svg, png, webp…)
 * @param string $alt  Testo alternativo (solitamente il nome del sito)
 * @param int    $width   Larghezza opzionale
 * @param int    $height  Altezza opzionale
 */
function ficus_logo_img( string $src, string $alt, int $width = 0, int $height = 0 ): string {
    $size_attrs = '';
    if ( $width )  $size_attrs .= ' width="'  . $width  . '"';
    if ( $height ) $size_attrs .= ' height="' . $height . '"';

    return sprintf(
        '<a href="%s" class="custom-logo-link site-header__logo-link" rel="home" aria-current="page">'
        . '<img src="%s" alt="%s" class="custom-logo"%s />'
        . '</a>',
        esc_url( home_url( '/' ) ),
        esc_url( $src ),
        esc_attr( $alt ),
        $size_attrs
    );
}

/**
 * Callback di default: cerca assets/images/logo.{png,svg,webp,jpg}
 * nella directory del child theme (convenzione, nessun PHP richiesto nel child).
 */
add_filter( 'ficus_default_logo_html', function (): string {
    $base = get_stylesheet_directory() . '/assets/images/logo';
    $uri  = get_stylesheet_directory_uri() . '/assets/images/logo';

    foreach ( [ 'png', 'svg', 'webp', 'jpg' ] as $ext ) {
        if ( file_exists( $base . '.' . $ext ) ) {
            return ficus_logo_img( $uri . '.' . $ext, get_bloginfo( 'name' ) );
        }
    }

    return '';
} );

/**
 * Fallback: se wp:site-logo non ha logo da mostrare (nessun logo admin impostato),
 * prova con il logo di default del child theme via filtro ficus_default_logo_html.
 */
add_filter( 'render_block_core/site-logo', function ( string $block_content ): string {
    if ( ! empty( $block_content ) ) {
        return $block_content;
    }
    return (string) apply_filters( 'ficus_default_logo_html', '' );
} );
