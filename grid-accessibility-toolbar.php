<?php
/**
 * Plugin Name: GRID-Arendal
 * Description: Configurable GRID-Arendal tools: accessibility, ACF section names, front-end editing, review, resource imports, private files, maps and LMS connections.
 * Version: 2.0.0
 * Author: GRID-Arendal
 * License: GPL-2.0-or-later
 * Text Domain: grid-accessibility-toolbar
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Update URI: https://github.com/grid-arendal/grid-accessibility-toolbar
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class GRID_Accessibility_Toolbar {
    const VERSION = '2.0.0';
    const OPTION = 'grid_a11y_settings';

    public static function tools() {
        return array(
            'text'     => __( 'Text size', 'grid-accessibility-toolbar' ),
            'spacing'  => __( 'Extra text spacing', 'grid-accessibility-toolbar' ),
            'font'     => __( 'Readable font', 'grid-accessibility-toolbar' ),
            'dyslexia' => __( 'Dyslexia Friendly', 'grid-accessibility-toolbar' ),
            'contrast' => __( 'Contrast', 'grid-accessibility-toolbar' ),
            'links'    => __( 'Highlight links', 'grid-accessibility-toolbar' ),
            'motion'   => __( 'Reduce motion', 'grid-accessibility-toolbar' ),
            'cursor'   => __( 'Large pointer', 'grid-accessibility-toolbar' ),
            'guide'    => __( 'Reading guide', 'grid-accessibility-toolbar' ),
            'mask' => __( 'Reading mask', 'grid-accessibility-toolbar' ),
            'lineheight' => __( 'Line height', 'grid-accessibility-toolbar' ),
            'align' => __( 'Text align', 'grid-accessibility-toolbar' ),
            'saturation' => __( 'Saturation', 'grid-accessibility-toolbar' ),
            'images' => __( 'Hide images', 'grid-accessibility-toolbar' ),
            'tooltips' => __( 'Tooltips', 'grid-accessibility-toolbar' ),
            'structure' => __( 'Page structure', 'grid-accessibility-toolbar' ),
            'focus' => __( 'Strong focus', 'grid-accessibility-toolbar' ),
        );
    }

    public static function profiles() {
        return array(
            'motor' => array( 'label' => __( 'Motor Impaired', 'grid-accessibility-toolbar' ), 'description' => __( 'Large pointer, highlighted links and stronger focus. Menus and forms still need accessible underlying code.', 'grid-accessibility-toolbar' ), 'preferences' => array( 'cursor' => true, 'links' => true, 'focus' => true, 'motion' => true ) ),
            'blind' => array( 'label' => __( 'Blind', 'grid-accessibility-toolbar' ), 'description' => __( 'Opens navigation aids. Use your screen reader; this preset does not repair page semantics or replace assistive technology.', 'grid-accessibility-toolbar' ), 'preferences' => array( 'structure' => true, 'focus' => true ) ),
            'color' => array( 'label' => __( 'Color Blind', 'grid-accessibility-toolbar' ), 'description' => __( 'Light contrast and stronger link differentiation. Color-only charts and instructions still need accessible alternatives.', 'grid-accessibility-toolbar' ), 'preferences' => array( 'contrast' => 'light', 'links' => true, 'focus' => true ) ),
            'dyslexia' => array( 'label' => __( 'Dyslexia', 'grid-accessibility-toolbar' ), 'description' => __( 'OpenDyslexic, larger text and spacing. Change any setting to suit your reading preference.', 'grid-accessibility-toolbar' ), 'preferences' => array( 'dyslexia' => true, 'text' => '115', 'spacing' => true, 'lineheight' => 'relaxed', 'align' => 'left' ) ),
            'vision' => array( 'label' => __( 'Low vision', 'grid-accessibility-toolbar' ), 'description' => __( 'Larger text, dark contrast, highlighted links and stronger focus. Browser zoom remains available.', 'grid-accessibility-toolbar' ), 'preferences' => array( 'text' => '150', 'contrast' => 'dark', 'links' => true, 'focus' => true ) ),
            'learning' => array( 'label' => __( 'Cognitive & Learning', 'grid-accessibility-toolbar' ), 'description' => __( 'Readable font, spacing and a page outline. Content clarity still depends on how the page is written.', 'grid-accessibility-toolbar' ), 'preferences' => array( 'font' => true, 'text' => '115', 'spacing' => true, 'lineheight' => 'relaxed', 'structure' => true ) ),
            'motion' => array( 'label' => __( 'Seizure & Epileptic', 'grid-accessibility-toolbar' ), 'description' => __( 'Reduced motion and hidden images. CSS motion and native HTML video are paused; embedded, canvas or script-driven motion may remain. This is not a seizure-safety guarantee.', 'grid-accessibility-toolbar' ), 'preferences' => array( 'motion' => true, 'images' => true ) ),
            'attention' => array( 'label' => __( 'ADHD', 'grid-accessibility-toolbar' ), 'description' => __( 'Focus reading: reading guide, reduced motion, hidden images and more line spacing. This is a preference preset, not a therapeutic claim.', 'grid-accessibility-toolbar' ), 'preferences' => array( 'guide' => true, 'motion' => true, 'images' => true, 'lineheight' => 'relaxed' ) ),
        );
    }

    public static function defaults() {
        return array(
            'enabled' => 1, 'remember' => 1, 'profiles_enabled' => 1, 'translation_enabled' => 1, 'side' => 'right', 'bottom' => 24,
            'color' => '#823b93', 'title' => __( 'Accessibility Widget', 'grid-accessibility-toolbar' ),
            'label' => __( 'Accessibility', 'grid-accessibility-toolbar' ),
            'statement' => '', 'exclude' => '', 'tools' => array_keys( self::tools() ), 'tools_version' => 4, 'brand_version' => 2,
        );
    }

    public static function settings() {
        $stored = get_option( self::OPTION, array() );
        if ( is_array( $stored ) && ( ! isset( $stored['brand_version'] ) || $stored['brand_version'] < 2 ) && isset( $stored['title'] ) && 'Reading preferences' === $stored['title'] ) {
            $stored['title'] = self::defaults()['title'];
        }
        if ( is_array( $stored ) && ! isset( $stored['brand_version'] ) && isset( $stored['color'] ) && strtolower( $stored['color'] ) === '#005b70' ) {
            $stored['color'] = '#823b93';
        }
        // Offer the new control on upgrades, preserving every existing control
        // choice. An intentionally empty toolbar remains empty. Saving settings
        // stamps the new schema so a later opt-out stays opted out.
        if ( is_array( $stored ) && ! empty( $stored['tools'] ) && is_array( $stored['tools'] ) ) {
            $version = isset( $stored['tools_version'] ) ? (int) $stored['tools_version'] : 1;
            $additions = $version < 2 ? array( 'dyslexia' ) : array();
            if ( $version < 3 ) {
                $additions = array_merge( $additions, array( 'lineheight', 'align', 'saturation', 'images', 'tooltips', 'structure', 'focus' ) );
            }
            if ( $version < 4 ) $additions[] = 'mask';
            $stored['tools'] = array_values( array_unique( array_merge( $stored['tools'], $additions ) ) );
        }
        return array_merge( self::defaults(), is_array( $stored ) ? $stored : array() );
    }

    public static function sanitize( $input ) {
        $input = is_array( $input ) ? $input : array();
        $defaults = self::defaults();
        $out = $defaults;
        foreach ( array( 'enabled', 'remember', 'profiles_enabled', 'translation_enabled' ) as $key ) {
            $out[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
        }
        $out['side'] = isset( $input['side'] ) && 'left' === $input['side'] ? 'left' : 'right';
        $out['bottom'] = max( 16, min( 200, isset( $input['bottom'] ) ? absint( $input['bottom'] ) : 24 ) );
        $out['color'] = isset( $input['color'] ) && is_string( $input['color'] ) ? sanitize_hex_color( $input['color'] ) : '';
        $out['color'] = $out['color'] ? $out['color'] : $defaults['color'];
        foreach ( array( 'title', 'label' ) as $key ) {
            $value = isset( $input[ $key ] ) && is_string( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
            $out[ $key ] = '' !== $value ? wp_html_excerpt( $value, 80, '' ) : $defaults[ $key ];
        }
        $out['statement'] = isset( $input['statement'] ) && is_string( $input['statement'] ) ? esc_url_raw( $input['statement'], array( 'https', 'http' ) ) : '';
        $selected_tools = isset( $input['tools'] ) && is_array( $input['tools'] ) ? array_filter( $input['tools'], 'is_string' ) : array();
        $out['tools'] = array_values( array_intersect( array_keys( self::tools() ), $selected_tools ) );
        $paths = array();
        $raw = isset( $input['exclude'] ) && is_string( $input['exclude'] ) ? $input['exclude'] : '';
        foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $path ) {
            $path = trim( sanitize_text_field( $path ) );
            if ( '' !== $path && '/' === substr( $path, 0, 1 ) && false === strpos( $path, '://' ) && false === strpos( $path, '?' ) && false === strpos( $path, '#' ) ) {
                $paths[] = '/' === $path ? '/' : rtrim( $path, '/' );
            }
        }
        $out['exclude'] = implode( "\n", array_unique( $paths ) );
        return $out;
    }

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

    public static function should_display() {
        $settings = self::settings();
        if ( ! $settings['enabled'] || self::editor_active() ) {
            return false;
        }
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
        $path = rawurldecode( (string) wp_parse_url( $uri, PHP_URL_PATH ) );
        foreach ( explode( "\n", $settings['exclude'] ) as $exclude ) {
            $exclude = trim( $exclude );
            if ( '' !== $exclude && ( '/' === $exclude || $path === $exclude || 0 === strpos( $path, $exclude . '/' ) ) ) {
                return false;
            }
        }
        return (bool) apply_filters( 'grid_a11y_should_display', true );
    }

    public static function enqueue() {
        if ( ! self::should_display() ) {
            return;
        }
        wp_enqueue_style( 'grid-a11y-page', plugins_url( 'assets/page.css', __FILE__ ), array(), self::VERSION );
        wp_enqueue_script( 'grid-a11y', plugins_url( 'assets/toolbar.js', __FILE__ ), array(), self::VERSION, true );
    }

    public static function translation_script_order() {
        // Google Language Translator's legacy widget replaces the shared global
        // configuration. Run keyed GTranslate widgets after that legacy script
        // so both sets of settings survive without disabling either plugin.
        if ( ! wp_script_is( 'glt_widget_script', 'enqueued' ) ) {
            return;
        }
        $scripts = wp_scripts();
        foreach ( $scripts->queue as $handle ) {
            if ( 0 === strpos( $handle, 'gt_widget_script_' ) && isset( $scripts->registered[ $handle ] ) && ! in_array( 'glt_widget_script', $scripts->registered[ $handle ]->deps, true ) ) {
                $scripts->registered[ $handle ]->deps[] = 'glt_widget_script';
            }
        }
    }

    public static function suppress_translation_floaters() {
        $settings = self::settings();
        if ( ! self::should_display() || empty( $settings['translation_enabled'] ) || ! shortcode_exists( 'gtranslate' ) ) {
            return;
        }
        // Request-local suppression only. Stored translator settings stay intact,
        // so their original floating controls return when this integration is off.
        add_filter( 'option_googlelanguagetranslator_floating_widget', array( __CLASS__, 'floating_widget_off' ) );
        remove_action( 'wp_footer', 'gtranslate_display_floating' );
    }

    public static function floating_widget_off( $value ) {
        return 'no';
    }

    public static function render() {
        if ( ! self::should_display() ) {
            return;
        }
        $settings = self::settings();
        $translation = ! empty( $settings['translation_enabled'] ) && shortcode_exists( 'gtranslate' );
        $config = array(
            'settings' => $settings,
            'brand' => array( 'logo' => plugins_url( 'assets/branding/grid-flower.svg', __FILE__ ), 'url' => 'https://www.grida.no/' ),
            'translation' => $translation,
            'profiles' => $settings['profiles_enabled'] ? self::profiles() : array(),
            'css' => plugins_url( 'assets/toolbar.css', __FILE__ ) . '?ver=' . self::VERSION,
            'storageKey' => 'grid-a11y-v1-' . substr( md5( home_url( '/' ) ), 0, 12 ),
            'labels' => array(
                'close' => __( 'Close preferences', 'grid-accessibility-toolbar' ),
                'reset' => __( 'Reset preferences', 'grid-accessibility-toolbar' ),
                'subtitle' => __( 'Make this page easier to read.', 'grid-accessibility-toolbar' ),
                'credit' => __( 'Brought to you by', 'grid-accessibility-toolbar' ),
                'on' => __( 'On', 'grid-accessibility-toolbar' ),
                'off' => __( 'Off', 'grid-accessibility-toolbar' ),
                'about' => __( 'About these preferences', 'grid-accessibility-toolbar' ),
                'dyslexiaNote' => __( 'Dyslexia Friendly uses OpenDyslexic. This is an optional reading preference; individual preferences vary.', 'grid-accessibility-toolbar' ),
                'fontStyle' => __( 'Font style', 'grid-accessibility-toolbar' ),
                'siteFont' => __( 'Site default', 'grid-accessibility-toolbar' ),
                'openDyslexic' => __( 'OpenDyslexic', 'grid-accessibility-toolbar' ),
                'arial' => __( 'Arial', 'grid-accessibility-toolbar' ),
                'fontCycleHint' => __( 'Activate to cycle through OpenDyslexic, Arial and the site default font.', 'grid-accessibility-toolbar' ),
                'readingAids' => __( 'Reading aids', 'grid-accessibility-toolbar' ),
                'readingAidsHint' => __( 'Activate to cycle through the enabled cursor, reading mask and reading guide options, then turn them off.', 'grid-accessibility-toolbar' ),
                'maskHeight' => __( 'Reading window height', 'grid-accessibility-toolbar' ),
                'maskDimming' => __( 'Background dimming', 'grid-accessibility-toolbar' ),
                'profiles' => __( 'Accessibility profiles', 'grid-accessibility-toolbar' ),
                'custom' => __( 'Custom / choose a profile', 'grid-accessibility-toolbar' ),
                'profileHint' => __( 'Presets replace your current preferences. Only controls available on this device and enabled by this site are applied; you can adjust each one.', 'grid-accessibility-toolbar' ),
                'profileApplied' => __( 'Profile applied. You can adjust individual controls.', 'grid-accessibility-toolbar' ),
                'comfortable' => __( 'Comfortable (1.5)', 'grid-accessibility-toolbar' ),
                'relaxed' => __( 'Relaxed (1.8)', 'grid-accessibility-toolbar' ),
                'loose' => __( 'Loose (2.0)', 'grid-accessibility-toolbar' ),
                'left' => __( 'Left', 'grid-accessibility-toolbar' ),
                'center' => __( 'Center', 'grid-accessibility-toolbar' ),
                'right' => __( 'Right', 'grid-accessibility-toolbar' ),
                'muted' => __( 'Muted images', 'grid-accessibility-toolbar' ),
                'gray' => __( 'Grayscale images', 'grid-accessibility-toolbar' ),
                'vivid' => __( 'Vivid images', 'grid-accessibility-toolbar' ),
                'main' => __( 'Main content', 'grid-accessibility-toolbar' ),
                'navigation' => __( 'Navigation', 'grid-accessibility-toolbar' ),
                'outlineEmpty' => __( 'No visible headings or navigation landmarks were found.', 'grid-accessibility-toolbar' ),
                'outlineHint' => __( 'Choose a section to move keyboard focus there. This list reflects the existing page structure; it does not repair it.', 'grid-accessibility-toolbar' ),
                'closeOutline' => __( 'Hide page structure', 'grid-accessibility-toolbar' ),
                'motionNote' => __( 'Reduce motion stops CSS effects and pauses native HTML video while enabled. Video stays paused when you turn this off; resume it yourself if wanted. Other embedded or script-driven motion may remain.', 'grid-accessibility-toolbar' ),
                'imagesNote' => __( 'Hide images hides supported content images and backgrounds, preserving layout and accessible alternatives. Controls, opted-out widgets and embedded media may remain visible. Saturation adjusts images and visual media only.', 'grid-accessibility-toolbar' ),
                'normal' => __( 'Default', 'grid-accessibility-toolbar' ),
                'dark' => __( 'Dark', 'grid-accessibility-toolbar' ),
                'light' => __( 'Light', 'grid-accessibility-toolbar' ),
                'tools' => self::tools(),
                'statement' => __( 'Accessibility statement', 'grid-accessibility-toolbar' ),
                'note' => __( 'These controls adjust this page for you. They do not fix every accessibility barrier.', 'grid-accessibility-toolbar' ),
                'touchNotice' => __( 'Pointer-based reading aids and hover tooltips are hidden on touch-only devices. Profiles apply only the controls available here.', 'grid-accessibility-toolbar' ),
                'storage' => $settings['remember'] ? __( 'Preferences are saved in this browser for this site. Reset removes them.', 'grid-accessibility-toolbar' ) : __( 'Preferences apply only to this page visit.', 'grid-accessibility-toolbar' ),
                'unsaved' => __( 'Your browser could not save preferences. They apply only to this page visit.', 'grid-accessibility-toolbar' ),
                'updated' => __( 'Preferences updated.', 'grid-accessibility-toolbar' ),
                'cleared' => __( 'Preferences reset.', 'grid-accessibility-toolbar' ),
                'translationUnavailable' => __( 'Translation is unavailable. Please reload the page or try again later.', 'grid-accessibility-toolbar' ),
                'translationPrivacy' => __( 'Page translation uses external GTranslate and Google services. Page text and connection information are sent to those services when used.', 'grid-accessibility-toolbar' ),
            ),
        );
        echo '<div id="grid-a11y-root" class="notranslate" translate="no" data-config="' . esc_attr( wp_json_encode( $config ) ) . '">';
        if ( $translation ) {
            // Keep the official selector in light DOM so GTranslate can find it;
            // the frontend slots it into the isolated accessibility panel.
            echo '<div id="grid-a11y-translation" slot="translation" class="notranslate" translate="no" data-ga-a11y-ignore hidden>';
            echo '<div class="grid-a11y-language-heading"><label for="grid-a11y-language">' . esc_html__( 'Website language', 'grid-accessibility-toolbar' ) . '</label>';
            echo '<button type="button" id="grid-a11y-language-reset" aria-label="' . esc_attr__( 'Restore original website language', 'grid-accessibility-toolbar' ) . '" disabled>' . esc_html__( 'Reset', 'grid-accessibility-toolbar' ) . '</button></div>';
            echo do_shortcode( '[gtranslate widget_look="dropdown"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted registered plugin shortcode.
            echo '<p id="grid-a11y-translation-status" role="status">' . esc_html__( 'Loading language selector…', 'grid-accessibility-toolbar' ) . '</p>';
            echo '<p id="grid-a11y-translation-note" class="grid-a11y-translation-note">' . esc_html__( 'Automatic page translation by GTranslate. Toolbar labels follow the site interface language.', 'grid-accessibility-toolbar' ) . '</p></div>';
        }
        echo '</div>';
    }

    public static function redirect_legacy_settings() {
        // Preserve links bookmarked before the settings moved to GRID-Arendal.
        global $pagenow;
        if ( is_admin() && 'options-general.php' === $pagenow && isset( $_GET['page'] ) && 'grid-a11y' === wp_unslash( $_GET['page'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only legacy navigation.
            wp_safe_redirect( admin_url( 'admin.php?page=grid-a11y' ) );
            exit;
        }
    }

    public static function admin_init() {
        register_setting( 'grid_a11y', self::OPTION, array( 'type' => 'array', 'sanitize_callback' => array( __CLASS__, 'sanitize' ), 'default' => self::defaults() ) );
    }

    public static function dependency_notice() {
        $settings = self::settings();
        if ( ! current_user_can( 'manage_options' ) || empty( $settings['enabled'] ) || empty( $settings['translation_enabled'] ) || shortcode_exists( 'gtranslate' ) ) {
            return;
        }
        // GTranslate is needed only for translation. Core reading controls stay
        // available; WordPress handles installation, activation and nonce checks.
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin = 'gtranslate/gtranslate.php';
        $plugins = get_plugins();
        $url = '';
        $label = '';
        if ( isset( $plugins[ $plugin ] ) ) {
            if ( ! is_plugin_active( $plugin ) && current_user_can( 'activate_plugin', $plugin ) ) {
                $url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin ) ), 'activate-plugin_' . $plugin );
                $label = __( 'Activate GTranslate', 'grid-accessibility-toolbar' );
            }
        } elseif ( current_user_can( 'install_plugins' ) ) {
            $url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=gtranslate' ), 'install-plugin_gtranslate' );
            $label = __( 'Install GTranslate', 'grid-accessibility-toolbar' );
        }
        echo '<div class="notice notice-info grid-a11y-dependency"><p><strong>' . esc_html__( 'GRID Accessibility: enable website translation', 'grid-accessibility-toolbar' ) . '</strong></p><p>';
        echo esc_html__( 'The language selector needs the free GTranslate plugin. Reading controls work without it. GTranslate uses external translation services; installation is your choice.', 'grid-accessibility-toolbar' );
        if ( $url ) {
            echo '</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a> ';
        } else {
            echo ' ' . esc_html__( 'Ask your site or network administrator to install and activate GTranslate, or check its configuration if already active.', 'grid-accessibility-toolbar' ) . '</p><p>';
        }
        if ( ! is_network_admin() ) {
            echo '<a href="' . esc_url( admin_url( 'admin.php?page=grid-a11y' ) ) . '">' . esc_html__( 'Reading controls only? Turn off the GTranslate integration in settings.', 'grid-accessibility-toolbar' ) . '</a>';
        }
        echo '</p></div>';
    }

    public static function admin_menu() {
        add_menu_page( __( 'GRID-Arendal', 'grid-accessibility-toolbar' ), __( 'GRID-Arendal', 'grid-accessibility-toolbar' ), 'manage_options', 'grid-arendal', array( __CLASS__, 'admin_overview' ), plugins_url( 'assets/branding/grid-flower.svg', __FILE__ ), 81 );
        add_submenu_page( 'grid-arendal', __( 'GRID-Arendal', 'grid-accessibility-toolbar' ), __( 'Overview', 'grid-accessibility-toolbar' ), 'manage_options', 'grid-arendal', array( __CLASS__, 'admin_overview' ) );
        add_submenu_page( 'grid-arendal', __( 'Accessibility Widget', 'grid-accessibility-toolbar' ), __( 'Accessibility Widget', 'grid-accessibility-toolbar' ), 'manage_options', 'grid-a11y', array( __CLASS__, 'admin_page' ) );
    }

    public static function admin_assets() {
        wp_enqueue_style( 'grid-a11y-admin', plugins_url( 'assets/admin.css', __FILE__ ), array(), self::VERSION );
    }

    public static function admin_overview() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        echo '<div class="wrap"><h1>GRID-Arendal</h1><p>One configurable plugin for visitor accessibility and optional editorial tools.</p><p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=grid-modules' ) ) . '">Modules &amp; workspace</a> <a class="button" href="' . esc_url( admin_url( 'admin.php?page=grid-labels' ) ) . '">Section &amp; field names</a></p></div>';
        echo '<div class="wrap"><h2>' . esc_html__( 'Accessibility Widget', 'grid-accessibility-toolbar' ) . '</h2><p>' . esc_html__( 'Help visitors choose how they read and navigate your website. Configure the widget controls, profiles, appearance and translation integration.', 'grid-accessibility-toolbar' ) . '</p><p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=grid-a11y' ) ) . '">' . esc_html__( 'Manage Accessibility Widget', 'grid-accessibility-toolbar' ) . '</a></p></div>';
    }

    public static function admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $s = self::settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'GRID Accessibility Toolbar', 'grid-accessibility-toolbar' ); ?></h1>
            <p><?php esc_html_e( 'Visitor preferences, not a compliance certification. Audit and fix the underlying theme, content and forms separately. No account or external service is needed.', 'grid-accessibility-toolbar' ); ?></p>
            <?php settings_errors(); ?>
            <form action="options.php" method="post">
                <?php settings_fields( 'grid_a11y' ); ?>
                <table class="form-table" role="presentation">
                    <?php foreach ( array( 'enabled' => __( 'Enable on public pages', 'grid-accessibility-toolbar' ), 'remember' => __( 'Remember visitor preferences locally', 'grid-accessibility-toolbar' ), 'profiles_enabled' => __( 'Offer preference profiles', 'grid-accessibility-toolbar' ), 'translation_enabled' => __( 'Embed GTranslate selector when GTranslate is active', 'grid-accessibility-toolbar' ) ) as $key => $label ) : ?>
                    <tr><th scope="row"><?php echo esc_html( $label ); ?></th><td><label><input type="checkbox" name="grid_a11y_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $s[ $key ], 1 ); ?>> <?php esc_html_e( 'Enabled', 'grid-accessibility-toolbar' ); ?></label></td></tr>
                    <?php endforeach; ?>
                    <?php foreach ( array( 'title' => __( 'Panel title', 'grid-accessibility-toolbar' ), 'label' => __( 'Launcher label', 'grid-accessibility-toolbar' ) ) as $key => $label ) : ?>
                    <tr><th scope="row"><label for="ga-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th><td><input class="regular-text" id="ga-<?php echo esc_attr( $key ); ?>" name="grid_a11y_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $s[ $key ] ); ?>" maxlength="80"></td></tr>
                    <?php endforeach; ?>
                    <tr><th scope="row"><label for="ga-side"><?php esc_html_e( 'Launcher side', 'grid-accessibility-toolbar' ); ?></label></th><td><select id="ga-side" name="grid_a11y_settings[side]"><option value="right" <?php selected( $s['side'], 'right' ); ?>><?php esc_html_e( 'Right', 'grid-accessibility-toolbar' ); ?></option><option value="left" <?php selected( $s['side'], 'left' ); ?>><?php esc_html_e( 'Left', 'grid-accessibility-toolbar' ); ?></option></select></td></tr>
                    <tr><th scope="row"><label for="ga-bottom"><?php esc_html_e( 'Distance from bottom (pixels)', 'grid-accessibility-toolbar' ); ?></label></th><td><input type="number" id="ga-bottom" name="grid_a11y_settings[bottom]" min="16" max="200" value="<?php echo esc_attr( $s['bottom'] ); ?>"><p class="description"><?php esc_html_e( '16–200. Adjust to avoid cookie banners and other floating controls.', 'grid-accessibility-toolbar' ); ?></p></td></tr>
                    <tr><th scope="row"><label for="ga-color"><?php esc_html_e( 'Brand color', 'grid-accessibility-toolbar' ); ?></label></th><td><input type="color" id="ga-color" name="grid_a11y_settings[color]" value="<?php echo esc_attr( $s['color'] ); ?>"><p class="description"><?php esc_html_e( 'The launcher text color is chosen automatically for contrast.', 'grid-accessibility-toolbar' ); ?></p></td></tr>
                    <tr><th scope="row"><?php esc_html_e( 'Available controls', 'grid-accessibility-toolbar' ); ?></th><td><fieldset><legend class="screen-reader-text"><?php esc_html_e( 'Available controls', 'grid-accessibility-toolbar' ); ?></legend><?php foreach ( self::tools() as $key => $label ) : ?><label><input type="checkbox" name="grid_a11y_settings[tools][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $s['tools'], true ) ); ?>> <?php echo esc_html( $label ); ?></label><br><?php endforeach; ?></fieldset></td></tr>
                    <tr><th scope="row"><label for="ga-statement"><?php esc_html_e( 'Accessibility statement URL', 'grid-accessibility-toolbar' ); ?></label></th><td><input type="url" class="regular-text" id="ga-statement" name="grid_a11y_settings[statement]" value="<?php echo esc_attr( $s['statement'] ); ?>"><p class="description"><?php esc_html_e( 'Optional. Use a full https:// URL to your statement or accessibility contact page.', 'grid-accessibility-toolbar' ); ?></p></td></tr>
                    <tr><th scope="row"><label for="ga-exclude"><?php esc_html_e( 'Excluded paths', 'grid-accessibility-toolbar' ); ?></label></th><td><textarea id="ga-exclude" name="grid_a11y_settings[exclude]" rows="5" class="large-text code" aria-describedby="ga-exclude-help"><?php echo esc_textarea( $s['exclude'] ); ?></textarea><p id="ga-exclude-help" class="description"><?php esc_html_e( 'One path per line, for example /checkout. Includes subpages. Use the full site path on subdirectory sites. No wildcards, query strings or full URLs. / excludes everything.', 'grid-accessibility-toolbar' ); ?></p></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <p><?php esc_html_e( 'Automatically hidden in Beaver Builder, the Customizer and detected front-end editors. Settings apply to this site; on multisite, configure each site separately. Clear page caches after changes.', 'grid-accessibility-toolbar' ); ?></p>
        </div>
        <?php
    }

    public static function action_links( $links ) {
        array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=grid-a11y' ) ) . '">' . esc_html__( 'Settings', 'grid-accessibility-toolbar' ) . '</a>' );
        return $links;
    }
}

add_action( 'wp_enqueue_scripts', array( 'GRID_Accessibility_Toolbar', 'enqueue' ) );
add_action( 'wp', array( 'GRID_Accessibility_Toolbar', 'suppress_translation_floaters' ), 20 );
add_action( 'wp_print_footer_scripts', array( 'GRID_Accessibility_Toolbar', 'translation_script_order' ), 0 );
add_action( 'wp_footer', array( 'GRID_Accessibility_Toolbar', 'render' ), 5 );
add_action( 'admin_init', array( 'GRID_Accessibility_Toolbar', 'admin_init' ) );
add_action( 'init', array( 'GRID_Accessibility_Toolbar', 'redirect_legacy_settings' ), 20 );
add_action( 'admin_notices', array( 'GRID_Accessibility_Toolbar', 'dependency_notice' ) );
add_action( 'network_admin_notices', array( 'GRID_Accessibility_Toolbar', 'dependency_notice' ) );
add_action( 'admin_menu', array( 'GRID_Accessibility_Toolbar', 'admin_menu' ) );
add_action( 'admin_enqueue_scripts', array( 'GRID_Accessibility_Toolbar', 'admin_assets' ) );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'GRID_Accessibility_Toolbar', 'action_links' ) );

require_once __DIR__ . '/includes/class-grid-core.php';
GRID_Arendal_Core::boot();

require_once __DIR__ . '/includes/class-grid-plugin-updates.php';
GRID_A11Y_Plugin_Updates::register( __FILE__ );
