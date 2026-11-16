<?php
/** Published public releases; no website credential or GitHub API access. */
if ( ! defined( 'ABSPATH' ) ) exit;

final class GRID_A11Y_Plugin_Updates {
    const REPOSITORY = 'https://github.com/grid-arendal/grid-accessibility-toolbar/';
    const METADATA_URL = 'https://raw.githubusercontent.com/grid-arendal/grid-accessibility-toolbar/main/widget-updates.json';
    const SLUG = 'grid-accessibility-toolbar';
    private static $plugin_file;
    private static $checker;

    public static function register( $plugin_file ) {
        self::$plugin_file = $plugin_file;
        add_action( 'plugins_loaded', array( __CLASS__, 'boot' ) );
    }

    public static function enabled() {
        return ! ( defined( 'GRID_A11Y_ENABLE_UPDATES' ) && ! GRID_A11Y_ENABLE_UPDATES );
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
        $expected = self::REPOSITORY . 'releases/download/v' . $info->version . '/' . self::SLUG . '-' . $info->version . '.zip';
        // Reject development/source archives, mismatched versions, other hosts,
        // credentials, query strings and unrelated repositories.
        return $expected === $info->download_url ? $info : null;
    }

}
