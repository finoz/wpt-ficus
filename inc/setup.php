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

    register_nav_menus( [
        'primary' => __( 'Navigazione principale', 'ficus' ),
    ] );
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

add_action( 'wp_enqueue_scripts', function () {
    // Navigation block: rimuoviamo il JS nativo, gestiamo noi il toggle
    wp_dequeue_script( 'wp-block-navigation-view' );
    wp_deregister_script( 'wp-block-navigation-view' );
}, 200 );

// ── Blocco custom: navigazione principale ─────────────────────────────────────
// Render server-side: bottone toggle + wp_nav_menu (location "primary").
// Usato in parts/header.html come <!-- wp:ficus/primary-nav /-->

add_action( 'init', function () {
    register_block_type(
        get_template_directory() . '/blocks/primary-nav/',
        [
            'render_callback' => function (): string {
                if ( ! has_nav_menu( 'primary' ) ) return '';

                ob_start();
                ?>
                <nav class="site-header__nav">
                    <button
                        class="site-nav__toggle"
                        aria-expanded="false"
                        aria-controls="site-nav-menu"
                        aria-label="<?php esc_attr_e( 'Apri menu', 'ficus' ); ?>"
                        type="button"
                    ><span></span></button>
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_id'        => 'site-nav-menu',
                        'menu_class'     => 'site-nav__menu',
                        'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        'depth'          => 2,
                        'fallback_cb'    => false,
                    ] );
                    ?>
                </nav>
                <?php
                return ob_get_clean();
            },
        ]
    );
} );

// Rimuove l'SVG dal bottone hamburger — l'icona è gestita via CSS (::before/::after)
add_filter( 'render_block_core/navigation', function ( string $html ): string {
    return preg_replace(
        '/(<button[^>]+wp-block-navigation__responsive-container-open[^>]*>)\s*<svg[\s\S]*?<\/svg>\s*(<\/button>)/i',
        '$1$2',
        $html
    );
} );

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

// ── Block styles custom ───────────────────────────────────────────────────────

add_action( 'init', function () {
    $blocks = [
        'core/paragraph',
        'core/heading',
        'core/group',
        'core/columns',
        'core/image',
        'core/buttons',
    ];
    foreach ( $blocks as $block ) {
        register_block_style( $block, [
            'name'  => 'reading-width',
            'label' => 'Reading',
        ] );
    }
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
