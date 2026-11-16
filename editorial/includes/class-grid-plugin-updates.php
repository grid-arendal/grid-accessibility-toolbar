<?php
/** Published public releases; no website credential or GitHub API access. */
if ( ! defined( 'ABSPATH' ) ) exit;

final class GRID_Editorial_Plugin_Updates {
    const REPOSITORY = 'https://github.com/grid-arendal/grid-accessibility-toolbar/';
    const METADATA_URL = 'https://raw.githubusercontent.com/grid-arendal/grid-accessibility-toolbar/main/editorial-updates.json';
    const SLUG = 'grid-editorial-tools';
    private static $plugin_file;
    private static $checker;

    public static function register( $plugin_file ) {
        self::$plugin_file = $plugin_file;
        add_action( 'plugins_loaded', array( __CLASS__, 'boot' ) );
        add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
    }

    public static function enabled() {
        return ! ( defined( 'GRID_EDITORIAL_ENABLE_UPDATES' ) && ! GRID_EDITORIAL_ENABLE_UPDATES );
    }

    public static function boot() {
        if ( self::$checker || ! self::enabled() ) return;
        require_once dirname( __DIR__ ) . '/vendor/plugin-update-checker/plugin-update-checker.php';
        add_filter( 'puc_request_info_result-' . self::SLUG, array( __CLASS__, 'valid_package' ) );
        // A published-release feed avoids GitHub API credentials and shared-IP
        // API rate limits when many WordPress websites use the same hosting.
        self::$checker = new \YahnisElsts\PluginUpdateChecker\v6p0\Plugin\UpdateChecker( self::METADATA_URL, self::$plugin_file, self::SLUG, 12 );
    }

    public static function valid_package( $info ) {
        if ( ! is_object( $info ) || ! isset( $info->version, $info->download_url ) || ! is_string( $info->version ) || ! is_string( $info->download_url ) ) return null;
        if ( ! preg_match( '/^\d+\.\d+\.\d+$/D', $info->version ) ) return null;
        if ( isset( $info->slug ) && self::SLUG !== $info->slug ) return null;
        $expected = self::REPOSITORY . 'releases/download/editorial-v' . $info->version . '/' . self::SLUG . '-' . $info->version . '.zip';
        // Reject development/source archives, mismatched versions, other hosts,
        // credentials, query strings and unrelated repositories.
        return $expected === $info->download_url ? $info : null;
    }

    public static function menu() {
        add_submenu_page( 'grid-editorial-tools', __( 'Plugin updates', 'grid-editorial-tools' ), __( 'Plugin updates', 'grid-editorial-tools' ), 'manage_options', 'grid-editorial-updates', array( __CLASS__, 'admin_page' ) );
    }

    public static function admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        echo '<div class="wrap"><h1>' . esc_html__( 'GRID-Arendal plugin updates', 'grid-editorial-tools' ) . '</h1>';
        echo '<p>' . esc_html__( 'Updates come from published stable releases in GRID-Arendal’s public GitHub repository. No GitHub account, access token or website registration is needed.', 'grid-editorial-tools' ) . '</p>';
        echo '<div class="notice notice-info inline"><p>' . esc_html( self::enabled() ? __( 'Release update checks are enabled. Use Check for updates on the Installed Plugins screen. WordPress checks periodically; automatic installation remains your choice.', 'grid-editorial-tools' ) : __( 'Update checks are disabled by the server’s GRID_EDITORIAL_ENABLE_UPDATES setting. Editorial tools still work normally.', 'grid-editorial-tools' ) ) . '</p></div>';
        echo '<p>' . esc_html__( 'Only release ZIPs published through the tested release workflow are offered. Unreleased branches and source archives are not installation packages. Test releases on a staging website before wider rollout.', 'grid-editorial-tools' ) . '</p>';
        echo '<p>' . esc_html__( 'The updater contacts GitHub to read release information and download packages. It sends no visitor reading preferences or page content.', 'grid-editorial-tools' ) . '</p>';
        echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . esc_html__( 'Open Installed Plugins', 'grid-editorial-tools' ) . '</a> <a class="button" href="' . esc_url( self::REPOSITORY . 'releases' ) . '">' . esc_html__( 'View releases', 'grid-editorial-tools' ) . '</a></p></div>';
    }
}
