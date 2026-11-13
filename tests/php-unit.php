<?php
// Isolated unit tests with narrow WordPress stubs, not a WordPress integration test.
error_reporting( E_ALL );
set_error_handler( function ( $level, $message ) { throw new Exception( $message ); } );
define( 'ABSPATH', '/' );
$options = array();
$contexts = array();
function __( $text, $domain = '' ) { return $text; }
function add_action() {}
function add_filter() { global $captured_filters; $captured_filters[] = func_get_args(); }
function remove_action() { global $removed_actions; $removed_actions[] = func_get_args(); }
function shortcode_exists( $name ) { global $gt_shortcode_active; return $name === 'gtranslate' && ! empty( $gt_shortcode_active ); }
function wp_script_is( $handle, $status = 'enqueued' ) { global $legacy_script_active; return $handle === 'glt_widget_script' && ! empty( $legacy_script_active ); }
function wp_scripts() { global $test_scripts; return $test_scripts; }
function plugin_basename( $path ) { return basename( $path ); }
function get_option( $key, $default = false ) { global $options; return isset( $options[ $key ] ) ? $options[ $key ] : $default; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_hex_color( $value ) { return preg_match( '/^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i', $value ) ? $value : null; }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function wp_html_excerpt( $text, $length, $more ) { return substr( $text, 0, $length ); }
function esc_url_raw( $value, $protocols ) { return preg_match( '#^https?://#i', $value ) ? $value : ''; }
function is_admin() { global $contexts; return ! empty( $contexts['admin'] ); }
function is_customize_preview() { global $contexts; return ! empty( $contexts['customizer'] ); }
function is_preview() { global $contexts; return ! empty( $contexts['preview'] ); }
function is_feed() { global $contexts; return ! empty( $contexts['feed'] ); }
function is_embed() { global $contexts; return ! empty( $contexts['embed'] ); }
function wp_unslash( $value ) { return stripslashes( $value ); }
function wp_parse_url( $url, $component ) { return parse_url( $url, $component ); }
function apply_filters( $key, $value ) { return $value; }
class FLBuilderModel {
    public static $active = false;
    public static function is_builder_active() { return self::$active; }
}

function current_user_can( $capability, ...$args ) { global $caps; return ! empty( $caps[ $capability ] ); }
function get_plugins() { global $installed_plugins; return $installed_plugins; }
function is_plugin_active( $plugin ) { global $plugin_active; return $plugin_active; }
function is_network_admin() { global $network_admin; return ! empty( $network_admin ); }
function self_admin_url( $path ) { return 'https://test.invalid/wp-admin/' . ( is_network_admin() ? 'network/' : '' ) . $path; }
function admin_url( $path ) { return 'https://test.invalid/wp-admin/' . $path; }
function wp_nonce_url( $url, $action ) { return $url . '&_wpnonce=' . rawurlencode( $action ); }
function esc_html__( $text, $domain = '' ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_html( $text ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_url( $url ) { return htmlspecialchars( $url, ENT_QUOTES ); }
function dependency_output() { ob_start(); GRID_Accessibility_Toolbar::dependency_notice(); return ob_get_clean(); }

require dirname(__DIR__) . '/grid-accessibility-toolbar.php';
$count = 0;
function verify( $name, $condition ) {
    global $count;
    if ( ! $condition ) { throw new Exception( 'FAIL: ' . $name ); }
    ++$count;
    echo 'PASS ' . $name . "\n";
}
$captured_filters = array(); $removed_actions = array(); $gt_shortcode_active = false;
GRID_Accessibility_Toolbar::suppress_translation_floaters();
verify( 'Without an embedded translator original floaters remain', empty( $captured_filters ) && empty( $removed_actions ) );
$gt_shortcode_active = true;
$before_options = $options;
GRID_Accessibility_Toolbar::suppress_translation_floaters();
verify( 'Embedded selector suppresses both duplicate floating controls', $captured_filters[0][0] === 'option_googlelanguagetranslator_floating_widget' && $removed_actions[0] === array( 'wp_footer', 'gtranslate_display_floating' ) && GRID_Accessibility_Toolbar::floating_widget_off( 'yes' ) === 'no' );
verify( 'Suppression does not persist changes to translator options', $before_options === $options );
$captured_filters = array(); $removed_actions = array();
$options[GRID_Accessibility_Toolbar::OPTION] = array( 'enabled' => 0 );
GRID_Accessibility_Toolbar::suppress_translation_floaters();
verify( 'Disabled toolbar restores the original floating-control behavior', empty( $captured_filters ) && empty( $removed_actions ) );
$options[GRID_Accessibility_Toolbar::OPTION] = array( 'translation_enabled' => 0 );
GRID_Accessibility_Toolbar::suppress_translation_floaters();
verify( 'Disabled integration leaves translator floaters unchanged', empty( $captured_filters ) && empty( $removed_actions ) );
$options[GRID_Accessibility_Toolbar::OPTION] = array( 'color' => '#005b70' );
verify( 'Old default color migrates to approved lavender', GRID_Accessibility_Toolbar::settings()['color'] === '#823b93' );
$options[GRID_Accessibility_Toolbar::OPTION] = array( 'color' => '#123456' );
verify( 'Existing custom launcher colors stay intact', GRID_Accessibility_Toolbar::settings()['color'] === '#123456' );
$options[GRID_Accessibility_Toolbar::OPTION] = GRID_Accessibility_Toolbar::sanitize( array( 'color' => '#005b70' ) );
verify( 'Explicitly saved launcher colors are preserved', GRID_Accessibility_Toolbar::settings()['color'] === '#005b70' );
$options = array();
$test_scripts = (object) array( 'queue' => array( 'gt_widget_script_123', 'unrelated' ), 'registered' => array( 'gt_widget_script_123' => (object) array( 'deps' => array( 'existing' ) ), 'unrelated' => (object) array( 'deps' => array() ) ) );
$legacy_script_active = false;
GRID_Accessibility_Toolbar::translation_script_order();
verify( 'No legacy translator leaves dependencies alone', $test_scripts->registered['gt_widget_script_123']->deps === array( 'existing' ) );
$legacy_script_active = true;
GRID_Accessibility_Toolbar::translation_script_order();
verify( 'Translator compatibility preserves existing dependency order', $test_scripts->registered['gt_widget_script_123']->deps === array( 'existing', 'glt_widget_script' ) && $test_scripts->registered['unrelated']->deps === array() );
GRID_Accessibility_Toolbar::translation_script_order();
verify( 'Translation ordering is idempotent', count( $test_scripts->registered['gt_widget_script_123']->deps ) === 2 );
verify( 'Defaults display without activation-time database writes', GRID_Accessibility_Toolbar::should_display() );
verify( 'Translation integration is offered by default', GRID_Accessibility_Toolbar::defaults()['translation_enabled'] === 1 );
verify( 'Translation integration can be disabled', GRID_Accessibility_Toolbar::sanitize( array( 'translation_enabled' => 0 ) )['translation_enabled'] === 0 );
verify( 'New installs offer Dyslexia Friendly', in_array( 'dyslexia', GRID_Accessibility_Toolbar::settings()['tools'], true ) );
$options[ GRID_Accessibility_Toolbar::OPTION ] = array( 'tools' => array( 'font', 'text' ) );
verify( 'Legacy upgrades offer new controls and preserve previous controls', array_slice( GRID_Accessibility_Toolbar::settings()['tools'], 0, 3 ) === array( 'font', 'text', 'dyslexia' ) && in_array( 'structure', GRID_Accessibility_Toolbar::settings()['tools'], true ) );
$options[ GRID_Accessibility_Toolbar::OPTION ] = array( 'tools_version' => 2, 'tools' => array( 'text' ) );
verify( 'Schema two upgrade adds new local controls without re-enabling a disabled font', in_array( 'lineheight', GRID_Accessibility_Toolbar::settings()['tools'], true ) && ! in_array( 'dyslexia', GRID_Accessibility_Toolbar::settings()['tools'], true ) );
$options[ GRID_Accessibility_Toolbar::OPTION ] = array( 'tools' => array() );
verify( 'Legacy intentionally empty control selection stays empty', array() === GRID_Accessibility_Toolbar::settings()['tools'] );
$options[ GRID_Accessibility_Toolbar::OPTION ] = GRID_Accessibility_Toolbar::sanitize( array( 'tools' => array( 'text' ) ) );
verify( 'Saving stamps current control schema', 4 === $options[ GRID_Accessibility_Toolbar::OPTION ]['tools_version'] );
verify( 'New control can remain disabled after saving', array( 'text' ) === GRID_Accessibility_Toolbar::settings()['tools'] );
verify( 'Dyslexia is accepted by the control whitelist', array( 'dyslexia' ) === GRID_Accessibility_Toolbar::sanitize( array( 'tools' => array( 'dyslexia' ) ) )['tools'] );
verify( 'Eight transparent profiles offered', count( GRID_Accessibility_Toolbar::profiles() ) === 8 );
foreach ( GRID_Accessibility_Toolbar::profiles() as $profile_key => $profile ) {
    verify( 'Profile uses known controls: ' . $profile_key, array() === array_diff( array_keys( $profile['preferences'] ), array_keys( GRID_Accessibility_Toolbar::tools() ) ) && ! empty( $profile['description'] ) );
}
$options = array();
$clean = GRID_Accessibility_Toolbar::sanitize( array( 'enabled' => 1, 'title' => '<b>Hello</b>', 'color' => 'red;display:none', 'statement' => 'javascript:alert(1)', 'bottom' => 999, 'tools' => array( 'text', 'invented', array() ), 'exclude' => "/checkout/\nhttps://evil.test\n/path?x=1\n/path#x\n/checkout\n/space" ) );
verify( 'Brand color uses safe fallback', '#823b93' === $clean['color'] );
verify( 'Title strips HTML', 'Hello' === $clean['title'] );
verify( 'Executable statement URL rejected', '' === $clean['statement'] );
verify( 'Offset clamped', 200 === $clean['bottom'] );
verify( 'Control whitelist rejects malformed entries', array( 'text' ) === $clean['tools'] );
verify( 'Exclusions normalized and deduplicated', "/checkout\n/space" === $clean['exclude'] );
verify( 'No controls selected stays empty', array() === GRID_Accessibility_Toolbar::sanitize( array() )['tools'] );
verify( 'Malformed scalar settings handled', is_array( GRID_Accessibility_Toolbar::sanitize( 'bad' ) ) );
verify( 'Malformed title handled', 'Accessibility Widget' === GRID_Accessibility_Toolbar::sanitize( array( 'title' => array() ) )['title'] );
$options[ GRID_Accessibility_Toolbar::OPTION ] = $clean;
$_SERVER['REQUEST_URI'] = '/checkout/complete/?x=1';
verify( 'Subtree excluded', ! GRID_Accessibility_Toolbar::should_display() );
$_SERVER['REQUEST_URI'] = '/checkout-extra/';
verify( 'Similar path is not excluded', GRID_Accessibility_Toolbar::should_display() );
$_SERVER['REQUEST_URI'] = '/%63heckout/';
verify( 'Encoded path matches exclusion', ! GRID_Accessibility_Toolbar::should_display() );
$_SERVER['REQUEST_URI'] = '/';
foreach ( array( 'fl_builder', 'fl_builder_ui', 'fl_builder_preview', 'elementor-preview', 'et_fb', 'vc_editable', 'ct_builder' ) as $key ) {
    $_GET = array( $key => '' );
    verify( 'Editor query suppressed: ' . $key, ! GRID_Accessibility_Toolbar::should_display() );
}
$_GET = array();
FLBuilderModel::$active = true;
verify( 'Beaver Builder API suppresses toolbar', ! GRID_Accessibility_Toolbar::should_display() );
FLBuilderModel::$active = false;
foreach ( array( 'admin', 'customizer', 'preview', 'feed', 'embed' ) as $key ) {
    $contexts = array( $key => true );
    verify( 'Non-public context suppressed: ' . $key, ! GRID_Accessibility_Toolbar::should_display() );
}
$contexts = array();
$options[ GRID_Accessibility_Toolbar::OPTION ]['enabled'] = 0;
verify( 'Sitewide disable respected', ! GRID_Accessibility_Toolbar::should_display() );

$options = array(); $caps = array( 'manage_options' => true, 'install_plugins' => true, 'activate_plugin' => true );
$installed_plugins = array(); $plugin_active = false; $gt_shortcode_active = false;
$notice = dependency_output();
verify( 'Missing translator offers nonce-protected WordPress install', strpos( $notice, 'Install GTranslate' ) !== false && strpos( $notice, 'install-plugin_gtranslate' ) !== false );
$installed_plugins = array( 'gtranslate/gtranslate.php' => array() );
$notice = dependency_output();
verify( 'Inactive installed translator offers activation, not reinstallation', strpos( $notice, 'Activate GTranslate' ) !== false && strpos( $notice, 'activate-plugin_gtranslate' ) !== false && strpos( $notice, 'Install GTranslate' ) === false );
$gt_shortcode_active = true;
verify( 'Working translator does not prompt', dependency_output() === '' );
$gt_shortcode_active = false; $caps = array();
verify( 'Visitors without administrator rights see no dependency notice', dependency_output() === '' );
$caps = array( 'manage_options' => true );
verify( 'Restricted administrator gets guidance without forbidden actions', strpos( dependency_output(), 'Ask your site or network administrator' ) !== false && strpos( dependency_output(), '_wpnonce' ) === false );
$options[GRID_Accessibility_Toolbar::OPTION] = array( 'translation_enabled' => 0 );
verify( 'Reading-only sites do not prompt for translator', dependency_output() === '' );
$options[GRID_Accessibility_Toolbar::OPTION] = array( 'enabled' => 0 );
verify( 'Disabled toolbar does not prompt for translator', dependency_output() === '' );
$options[GRID_Accessibility_Toolbar::OPTION] = array( 'title' => 'Reading preferences', 'brand_version' => 1 );
verify( 'Old default title migrates to compact widget title', GRID_Accessibility_Toolbar::settings()['title'] === 'Accessibility Widget' );
$options[GRID_Accessibility_Toolbar::OPTION] = array( 'title' => 'My custom title', 'brand_version' => 1 );
verify( 'Custom panel title is preserved', GRID_Accessibility_Toolbar::settings()['title'] === 'My custom title' );
$options = array(); $installed_plugins = array(); $caps = array( 'manage_options' => true, 'install_plugins' => true ); $network_admin = true;
verify( 'Network install uses the network administration destination', strpos( dependency_output(), '/network/update.php' ) !== false );
echo 'TOTAL ' . $count . "\n";
