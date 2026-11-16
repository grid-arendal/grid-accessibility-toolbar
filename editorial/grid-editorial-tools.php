<?php
/**
 * Plugin Name: GRID Editorial Tools
 * Description: Optional GRID-Arendal content workspace, ACF groups and labels, review, imports, private files, maps and LMS connections. Independent of the accessibility widget.
 * Version: 1.0.1
 * Author: GRID-Arendal
 * License: GPL-2.0-or-later
 * Text Domain: grid-editorial-tools
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Update URI: https://github.com/grid-arendal/grid-accessibility-toolbar/tree/main/editorial
 */
if ( ! defined( 'ABSPATH' ) ) exit;
final class GRID_Editorial_Tools {
    const VERSION = '1.0.1';
    public static function editor_active() {
        if ( is_admin() || is_customize_preview() || is_preview() || is_feed() || is_embed() ) {
            return true;
        }
        if ( class_exists( 'FLBuilderModel' ) && is_callable( array( 'FLBuilderModel', 'is_builder_active' ) ) && FLBuilderModel::is_builder_active() ) {
            return true;
        }
        // Presence only: no untrusted request value is used or echoed.
        foreach ( array( 'fl_builder', 'fl_builder_ui', 'fl_builder_preview', 'elementor-preview', 'et_fb', 'vc_editable', 'ct_builder' ) as $flag ) {
            if ( isset( $_GET[ $flag ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return true;
            }
        }
        return false;
    }


    public static function legacy_active() {
        return class_exists( 'GRID_Accessibility_Toolbar', false ) && version_compare( GRID_Accessibility_Toolbar::VERSION, '3.0.0', '<' );
    }
    public static function boot() {
        if ( ! get_option( 'grid_editorial_origin', false ) ) update_option( 'grid_editorial_origin', self::legacy_active() ? 'legacy' : 'native', false );
        // Install this plugin first. Legacy 2.x keeps serving its editor until
        // the widget is replaced, irrespective of plugin loading order.
        if ( self::legacy_active() ) { add_action( 'admin_notices', array( __CLASS__, 'legacy_notice' ) ); return; }
        require_once __DIR__ . '/includes/class-grid-core.php';
        GRID_Arendal_Core::boot();
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'action_links' ) );
    }
    public static function admin_assets() { wp_enqueue_style( 'grid-editorial-admin', plugins_url( 'assets/admin.css', __FILE__ ), array(), self::VERSION ); }
    public static function action_links( $links ) { array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=grid-editorial-tools' ) ) . '">Settings</a>' ); return $links; }
    public static function legacy_notice() {
        if ( current_user_can( 'manage_options' ) ) echo '<div class="notice notice-info"><p><strong>GRID Editorial Tools is staged.</strong> The combined GRID plugin is still running the existing editor. Install GRID Accessibility Widget 3.0.0 or later to complete the split; settings, field groups and workspace URLs are retained. Do not deactivate the combined plugin before replacing it.</p></div>';
    }
}
add_action( 'plugins_loaded', array( 'GRID_Editorial_Tools', 'boot' ), 20 );
require_once __DIR__ . '/includes/class-grid-plugin-updates.php';
GRID_Editorial_Plugin_Updates::register( __FILE__ );
