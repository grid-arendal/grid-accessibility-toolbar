<?php
/** Private, release-only GitHub updates. Credentials belong to the server. */
if ( ! defined( 'ABSPATH' ) ) exit;

final class GRID_A11Y_Private_Updates {
    const REPOSITORY = 'https://github.com/grid-arendal/grid-accessibility-toolbar/';
    const SLUG = 'grid-accessibility-toolbar';
    private static $plugin_file;
    private static $checker;

    public static function register( $plugin_file ) {
        self::$plugin_file = $plugin_file;
        add_action( 'plugins_loaded', array( __CLASS__, 'boot' ) );
        add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
    }

    public static function token() {
        $value = defined( 'GRID_A11Y_GITHUB_TOKEN' ) ? GRID_A11Y_GITHUB_TOKEN : getenv( 'GRID_A11Y_GITHUB_TOKEN' );
        if ( ! is_string( $value ) || preg_match( '/[\r\n\x00]/', $value ) ) return '';
        return trim( $value );
    }

    public static function enabled() {
        return ! ( defined( 'GRID_A11Y_ENABLE_PRIVATE_UPDATES' ) && ! GRID_A11Y_ENABLE_PRIVATE_UPDATES ) && '' !== self::token();
    }

    public static function boot() {
        if ( self::$checker || ! self::enabled() ) return;
        require_once dirname( __DIR__ ) . '/vendor/plugin-update-checker/plugin-update-checker.php';
        // Use the pinned implementation; no fallback to tags or branch archives.
        $api = new \YahnisElsts\PluginUpdateChecker\v6p0\Vcs\GitHubApi( self::REPOSITORY );
        $api->enableReleaseAssets( '/^grid-accessibility-toolbar-\d+\.\d+\.\d+\.zip$/D', \YahnisElsts\PluginUpdateChecker\v6p0\Vcs\Api::REQUIRE_RELEASE_ASSETS );
        $api->setReleaseFilter( array( __CLASS__, 'stable_release' ) );
        add_filter( 'puc_vcs_update_detection_strategies-' . self::SLUG, array( __CLASS__, 'release_only' ) );
        add_filter( 'puc_request_info_result-' . self::SLUG, array( __CLASS__, 'valid_package' ) );
        self::$checker = new \YahnisElsts\PluginUpdateChecker\v6p0\Vcs\PluginUpdateChecker( $api, self::$plugin_file, self::SLUG, 12 );
        self::$checker->setBranch( 'main' );
        self::$checker->setAuthentication( self::token() );
    }

    public static function stable_release( $version, $release ) {
        if ( ! is_string( $version ) || ! preg_match( '/^\d+\.\d+\.\d+$/D', $version ) || ! is_object( $release ) || ! empty( $release->draft ) || ! empty( $release->prerelease ) || empty( $release->assets ) || ! is_array( $release->assets ) ) return false;
        $expected = self::SLUG . '-' . $version . '.zip';
        // Give the library only the ZIP whose name matches this release's
        // version; a stale asset from an older release must never be selected.
        $release->assets = array_values( array_filter( $release->assets, function ( $asset ) use ( $expected ) {
            return is_object( $asset ) && isset( $asset->name ) && $expected === $asset->name && ( ! isset( $asset->state ) || 'uploaded' === $asset->state );
        } ) );
        return ! empty( $release->assets );
    }

    public static function release_only( $strategies ) {
        return isset( $strategies['latest_release'] ) ? array( 'latest_release' => $strategies['latest_release'] ) : array();
    }

    public static function valid_package( $info ) {
        if ( ! is_object( $info ) || empty( $info->download_url ) || empty( $info->version ) ) return null;
        if ( ! preg_match( '/^\d+\.\d+\.\d+$/D', $info->version ) ) return null;
        // Never accept a source archive, an arbitrary host or a token in a URL.
        return preg_match( '~^https://api\.github\.com/repos/grid-arendal/grid-accessibility-toolbar/releases/assets/[0-9]+$~D', $info->download_url ) ? $info : null;
    }

    public static function menu() {
        add_submenu_page( 'grid-arendal', __( 'Plugin updates', 'grid-accessibility-toolbar' ), __( 'Plugin updates', 'grid-accessibility-toolbar' ), 'manage_options', 'grid-a11y-updates', array( __CLASS__, 'admin_page' ) );
    }

    public static function admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        echo '<div class="wrap"><h1>' . esc_html__( 'GRID-Arendal plugin updates', 'grid-accessibility-toolbar' ) . '</h1>';
        echo '<p>' . esc_html__( 'Updates come from published stable releases in GRID-Arendal’s private GitHub repository. Unreleased code, drafts and pre-releases are never installed.', 'grid-accessibility-toolbar' ) . '</p>';
        if ( self::enabled() ) {
            echo '<div class="notice notice-info inline"><p>' . esc_html__( 'A server credential is configured. Check for updates on the Installed Plugins screen to verify repository access. GitHub permissions, token expiry and server connectivity can affect availability.', 'grid-accessibility-toolbar' ) . '</p></div>';
        } else {
            echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'Private updates are not connected on this website. Your hosting administrator must configure the GRID_A11Y_GITHUB_TOKEN server setting with read-only access to this repository. The widget works normally without it.', 'grid-accessibility-toolbar' ) . '</p></div>';
        }
        echo '<p>' . esc_html__( 'Keep credentials in wp-config.php or the server environment, never in the plugin ZIP, repository, page content or a chat message. Separate credentials per website make access easier to revoke. Renew expiring credentials before they expire.', 'grid-accessibility-toolbar' ) . '</p>';
        echo '<p>' . esc_html__( 'WordPress checks periodically. Install updates from the Plugins screen; automatic updates remain an administrator choice. Test a release on a staging site before wider rollout.', 'grid-accessibility-toolbar' ) . '</p>';
        echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . esc_html__( 'Open Installed Plugins', 'grid-accessibility-toolbar' ) . '</a> <a class="button" href="' . esc_url( self::REPOSITORY . 'releases' ) . '">' . esc_html__( 'View private releases', 'grid-accessibility-toolbar' ) . '</a></p></div>';
    }
}
