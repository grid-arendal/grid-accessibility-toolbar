<?php
/**
 * Plugin Name: GRID Cookie Consent
 * Description: Local, configurable consent controls, strict opt-in Analytics/Clarity, consent receipts and editable cookie policies. Supports compliance; does not certify it.
 * Version: 1.0.1
 * Author: GRID-Arendal
 * License: GPL-2.0-or-later
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Text Domain: grid-cookie-consent
 * Update URI: https://github.com/grid-arendal/grid-accessibility-toolbar/tree/main/consent
 */
if ( ! defined( 'ABSPATH' ) ) exit;
final class GRID_Cookie_Consent {
    const VERSION = '1.0.1';
    const OPTION = 'grid_cookie_consent';
    const FILE = __FILE__;
    public static function boot() {
        require_once __DIR__ . '/includes/settings.php';
        require_once __DIR__ . '/includes/blocker.php';
        require_once __DIR__ . '/includes/receipts.php';
        require_once __DIR__ . '/includes/admin.php';
        require_once __DIR__ . '/includes/updates.php';
        GRID_Cookie_Settings::boot(); GRID_Cookie_Receipts::boot(); GRID_Cookie_Admin::boot(); GRID_Cookie_Updates::boot();
        add_action( 'template_redirect', array( __CLASS__, 'frontend' ), -999 );
        add_shortcode( 'grid_cookie_settings', array( __CLASS__, 'settings_link' ) );
        add_shortcode( 'grid_cookie_policy', array( 'GRID_Cookie_Settings', 'policy' ) );
    }
    public static function activate() {
        self::boot(); GRID_Cookie_Receipts::install();
        if ( ! wp_next_scheduled( 'grid_cookie_prune' ) ) wp_schedule_event( time()+3600, 'daily', 'grid_cookie_prune' );
    }
    public static function deactivate() { wp_clear_scheduled_hook( 'grid_cookie_prune' ); }
    public static function frontend() {
        $s = GRID_Cookie_Settings::get();
        if ( ! $s['enabled'] || is_admin() || is_feed() || is_trackback() || wp_doing_ajax() || ( defined('REST_REQUEST') && REST_REQUEST ) || wp_is_json_request() ) return;
        ob_start( array( 'GRID_Cookie_Blocker', 'filter' ) );
        add_action( 'wp_head', array( __CLASS__, 'head' ), -999 );
        add_action( 'wp_footer', array( __CLASS__, 'root' ), 0 );
    }
    public static function config( $preview=false ) {
        $s=GRID_Cookie_Settings::get(); $home=home_url('/'); $path=wp_parse_url($home,PHP_URL_PATH)?:'/';
        return array('version'=>self::VERSION,'revision'=>GRID_Cookie_Settings::revision(),'ready'=>$preview?true:(bool)$s['reviewed'],'preview'=>$preview,'days'=>$s['days'],'endpoint'=>rest_url('grid-cookie/v1/'),'cookie'=>GRID_Cookie_Settings::cookie_name(),'path'=>$path,'secure'=>is_ssl(),'position'=>$s['position'],'accent'=>$s['accent'],'background'=>$s['background'],'text'=>$s['text'],'radius'=>$s['radius'],'font'=>$s['font'],'language'=>$s['language'],'locales'=>GRID_Cookie_Settings::locales(),'privacy'=>$s['privacy'],'controller'=>$s['controller'],'ga'=>$s['ga'],'clarity'=>$s['clarity'],'services'=>$s['services'],'functional'=>(bool)$s['functional'],'trackingAllowed'=>!is_user_logged_in()||!empty($s['logged_in']),'mask'=>true);
    }
    public static function head() {
        $config=self::config();$nonce=(string)apply_filters('grid_cookie_csp_nonce','');$n=$nonce?' nonce="'.esc_attr($nonce).'"':'';
        echo '<script id="grid-cookie-config" type="application/json">'.wp_json_encode($config,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).'</script>';
        echo '<script id="grid-cookie-defaults"'.$n.'>window.dataLayer=window.dataLayer||[];window.gtag=window.gtag||function(){window.dataLayer.push(arguments)};window.gtag("consent","default",{analytics_storage:"denied",ad_storage:"denied",ad_user_data:"denied",ad_personalization:"denied",functionality_storage:"denied",personalization_storage:"denied",security_storage:"granted"});</script>';
        echo '<link rel="stylesheet" href="'.esc_url(plugins_url('assets/consent.css',self::FILE)).'?ver='.self::VERSION.'">';
        echo '<script id="grid-cookie-runtime" src="'.esc_url(plugins_url('assets/consent.js',self::FILE)).'?ver='.self::VERSION.'" defer'.$n.'></script>';
    }
    public static function root() { echo '<div id="grid-cookie-consent" data-ga-a11y-ignore data-clarity-mask="true"></div><noscript><p>Optional services managed by GRID Cookie Consent stay blocked without JavaScript. This site uses a necessary cookie to remember choices.</p></noscript>'; }
    public static function settings_link() { return '<button type="button" class="grid-cookie-open">Cookie settings / informasjonskapsler</button>'; }
}
add_action( 'plugins_loaded', array( 'GRID_Cookie_Consent', 'boot' ) );
register_activation_hook( __FILE__, array( 'GRID_Cookie_Consent', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GRID_Cookie_Consent', 'deactivate' ) );
