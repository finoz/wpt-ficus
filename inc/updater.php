<?php
/**
 * Ficus_GitHub_Updater
 *
 * Abilita gli aggiornamenti di un tema WordPress direttamente
 * da una GitHub Release, senza plugin esterni.
 *
 * Requisiti:
 *   - Il repo GitHub deve essere pubblico, oppure
 *     definire in wp-config.php: define( 'FICUS_GITHUB_TOKEN', 'ghp_...' )
 *   - Le release devono usare tag semver: v1.0.0, v1.2.3, ecc.
 *
 * Uso nel parent (functions.php):
 *   new Ficus_GitHub_Updater( 'ficus', 'finoz/ficus-theme', wp_get_theme('ficus')->get('Version') );
 *
 * Uso nel child (functions.php del child):
 *   new Ficus_GitHub_Updater( 'lomais', 'finoz/lomais-wp', wp_get_theme()->get('Version') );
 */

defined( 'ABSPATH' ) || exit;

class Ficus_GitHub_Updater {

    private string $slug;
    private string $repo;
    private string $current_version;
    private string $transient_key;

    public function __construct( string $slug, string $repo, string $current_version ) {
        $this->slug            = $slug;
        $this->repo            = $repo;
        $this->current_version = $current_version;
        $this->transient_key   = 'ficus_gh_release_' . md5( $repo );

        add_filter( 'pre_set_site_transient_update_themes', [ $this, 'check_update' ] );
        add_filter( 'themes_api', [ $this, 'theme_info' ], 20, 3 );
        add_action( 'upgrader_process_complete', [ $this, 'clear_cache' ], 10, 2 );
    }

    /**
     * Inietta la nuova versione nel transient di WP se disponibile.
     */
    public function check_update( object $transient ): object {
        if ( empty( $transient->checked ) ) return $transient;

        $release = $this->get_latest_release();
        if ( ! $release ) return $transient;

        $latest = ltrim( $release['tag_name'], 'v' );

        if ( version_compare( $this->current_version, $latest, '<' ) ) {
            $transient->response[ $this->slug ] = [
                'theme'       => $this->slug,
                'new_version' => $latest,
                'url'         => $release['html_url'],
                'package'     => $release['zipball_url'],
            ];
        }

        return $transient;
    }

    /**
     * Fornisce le info del tema nella schermata di aggiornamento WP.
     */
    public function theme_info( mixed $result, string $action, object $args ): mixed {
        if ( $action !== 'theme_information' || ( $args->slug ?? '' ) !== $this->slug ) {
            return $result;
        }

        $release = $this->get_latest_release();
        if ( ! $release ) return $result;

        return (object) [
            'name'          => $this->slug,
            'slug'          => $this->slug,
            'version'       => ltrim( $release['tag_name'], 'v' ),
            'author'        => 'Finoz',
            'homepage'      => "https://github.com/{$this->repo}",
            'sections'      => [ 'description' => $release['body'] ?? '' ],
            'download_link' => $release['zipball_url'],
        ];
    }

    /**
     * Svuota la cache quando viene completato un aggiornamento.
     */
    public function clear_cache( WP_Upgrader $upgrader, array $options ): void {
        if ( ( $options['type'] ?? '' ) === 'theme' ) {
            delete_transient( $this->transient_key );
        }
    }

    /**
     * Recupera l'ultima release da GitHub API con cache 12h.
     */
    private function get_latest_release(): ?array {
        $cached = get_transient( $this->transient_key );
        if ( $cached !== false ) return $cached ?: null;

        $headers = [ 'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) ];

        if ( defined( 'FICUS_GITHUB_TOKEN' ) && FICUS_GITHUB_TOKEN ) {
            $headers['Authorization'] = 'Bearer ' . FICUS_GITHUB_TOKEN;
        }

        $response = wp_remote_get(
            "https://api.github.com/repos/{$this->repo}/releases/latest",
            [ 'headers' => $headers, 'timeout' => 10 ]
        );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            set_transient( $this->transient_key, [], HOUR_IN_SECONDS );
            return null;
        }

        $release = json_decode( wp_remote_retrieve_body( $response ), true );
        set_transient( $this->transient_key, $release, 12 * HOUR_IN_SECONDS );

        return $release;
    }
}
