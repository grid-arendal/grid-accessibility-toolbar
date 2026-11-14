<?php
/** Shared, opt-in GRID-Arendal tools. GPL-2.0-or-later. */
if ( ! defined( 'ABSPATH' ) ) exit;
final class GRID_Arendal_Core {
    const OPTION = 'grid_arendal_tools';
    const LABELS = 'grid_arendal_labels';
    public static function catalog() {
        return array(
            'accessibility' => array( 'Accessibility Widget', 'Visitor reading controls, profiles and the GTranslate integration.', array() ),
            'labels' => array( 'Section and field names', 'Rename ACF groups, tabs, fields and content-type display labels without changing stored keys or URLs.', array( 'acf' ) ),
            'workspace' => array( 'Front-end content workspace', 'Permission-based content lists, search, sorting, pagination, ACF editing and contextual edit links.', array( 'acf' ) ),
            'review' => array( 'Editorial review and activity', 'Submit changes for review, preserve published content until approval, reject stale edits and record activity.', array( 'workspace' ) ),
            'csv' => array( 'CSV resource imports', 'Preview and import new drafts into configured content sections. Existing records are never overwritten by imports.', array( 'workspace' ) ),
            'notifications' => array( 'Editorial notifications', 'Notify explicitly selected reviewers when content is submitted. No recipients are selected automatically.', array( 'review' ) ),
            'private_files' => array( 'Private resource files', 'Upload resource files outside the public web root and serve them only after per-record permission checks.', array( 'workspace' ) ),
            'maps' => array( 'Map editing', 'Optional coordinate picker for configured ACF latitude and longitude fields. Existing point and region data is preserved.', array( 'workspace' ) ),
            'lms' => array( 'LMS connections', 'Published LearnPress course cards and links to native course editors. Resources use the shared workspace; learner progress is untouched.', array( 'learnpress' ) ),
        );
    }
    public static function settings() {
        $s = get_option( self::OPTION, array() );
        return array_merge( array( 'modules' => array( 'accessibility', 'labels' ), 'sections' => array(), 'workspace_page' => 0, 'reviewers' => array(), 'private_root' => '' ), is_array( $s ) ? $s : array() );
    }
    public static function enabled( $key ) { return in_array( $key, (array) self::settings()['modules'], true ); }
    public static function ready( $key, $seen = array() ) {
        if ( ! self::enabled( $key ) || in_array( $key, $seen, true ) ) return false;
        $all = self::catalog();
        if ( ! isset( $all[$key] ) ) return false;
        foreach ( $all[$key][2] as $dep ) {
            if ( 'acf' === $dep && ! function_exists( 'acf_get_field_groups' ) ) return false;
            if ( 'learnpress' === $dep && ! defined( 'LEARNPRESS_VERSION' ) ) return false;
            if ( isset( $all[$dep] ) && ! self::ready( $dep, array_merge( $seen, array( $key ) ) ) ) return false;
        }
        return true;
    }
    public static function boot() {
        add_action( 'admin_init', array( __CLASS__, 'register' ) );
        add_action( 'admin_menu', array( __CLASS__, 'menus' ), 30 );
        add_filter( 'grid_a11y_should_display', function( $show ) { return $show && GRID_Arendal_Core::enabled( 'accessibility' ); } );
        require_once __DIR__ . '/modules/class-grid-labels.php';
        GRID_Arendal_Labels::boot();
        add_action( 'plugins_loaded', array( __CLASS__, 'modules' ), 30 );
    }
    public static function modules() {
        if ( self::ready( 'workspace' ) ) {
            require_once __DIR__ . '/modules/class-grid-workspace.php';
            GRID_Arendal_Workspace::boot();
        }
        if ( self::ready( 'lms' ) ) {
            require_once __DIR__ . '/modules/class-grid-lms.php';
            GRID_Arendal_LMS::boot();
        }
    }
    public static function permitted_types() {
        // Builder layouts, field definitions, commerce orders and learning records
        // always retain their native, specialised editors.
        $out = array();
        foreach ( get_post_types( array( 'show_ui' => true ), 'objects' ) as $name => $obj ) {
            if ( preg_match( '/^(attachment|revision|nav_menu_item|acf-|fl-|wp_|shop_order|shop_coupon|product_variation|lp_|user_request)/', $name ) ) continue;
            $out[$name] = $obj;
        }
        return $out;
    }
    public static function sanitize( $input ) {
        $input = is_array( $input ) ? $input : array();
        $out = self::settings();
        $mods = isset( $input['modules'] ) && is_array( $input['modules'] ) ? array_filter( $input['modules'], 'is_string' ) : array();
        $out['modules'] = array_values( array_intersect( array_keys( self::catalog() ), $mods ) );
        $out['workspace_page'] = isset( $input['workspace_page'] ) && is_scalar( $input['workspace_page'] ) ? absint( $input['workspace_page'] ) : 0;
        if ( $out['workspace_page'] && 'page' !== get_post_type( $out['workspace_page'] ) ) $out['workspace_page'] = 0;
        $out['sections'] = array(); $types = self::permitted_types();
        foreach ( (array) ( $input['sections'] ?? array() ) as $type => $s ) {
            if ( ! isset( $types[$type] ) || ! is_array( $s ) || empty( $s['enabled'] ) ) continue;
            $groups = array();
            foreach ( (array) ( $s['groups'] ?? array() ) as $key ) {
                if ( is_string( $key ) && function_exists( 'acf_get_field_group' ) && acf_get_field_group( $key ) ) $groups[] = $key;
            }
            $out['sections'][$type] = array( 'label' => self::text( $s['label'] ?? '', 100 ), 'groups' => array_values( array_unique( $groups ) ), 'latitude' => self::text( $s['latitude'] ?? '', 100 ), 'longitude' => self::text( $s['longitude'] ?? '', 100 ) );
        }
        $out['reviewers'] = array();
        foreach ( (array) ( $input['reviewers'] ?? array() ) as $id ) if ( is_scalar( $id ) && get_userdata( absint( $id ) ) ) $out['reviewers'][] = absint( $id );
        $out['reviewers'] = array_values( array_unique( $out['reviewers'] ) );
        $root = isset( $input['private_root'] ) && is_string( $input['private_root'] ) ? trim( $input['private_root'] ) : '';
        $out['private_root'] = '';
        if ( '' !== $root ) {
            $real = realpath( $root ); $public = realpath( ABSPATH );
            if ( $real && $public && is_dir( $real ) && is_writable( $real ) && ! self::within( $real, $public ) && ! self::within( $public, $real ) ) $out['private_root'] = $real;
            else add_settings_error( self::OPTION, 'grid_private_root', 'Private file storage must be an existing writable directory outside the public web root. The directory was not accepted.' );
        }
        return $out;
    }
    public static function within( $path, $root ) { return $path === $root || 0 === strpos( $path, rtrim( $root, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR ); }
    public static function text( $value, $length = 200 ) { return is_scalar( $value ) ? wp_html_excerpt( sanitize_text_field( (string) $value ), $length, '' ) : ''; }
    public static function register() {
        register_setting( 'grid_arendal_tools', self::OPTION, array( 'type' => 'array', 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
        register_setting( 'grid_arendal_labels', self::LABELS, array( 'type' => 'array', 'sanitize_callback' => array( 'GRID_Arendal_Labels', 'sanitize' ) ) );
    }
    public static function menus() {
        add_submenu_page( 'grid-arendal', 'Modules and workspace', 'Modules & workspace', 'manage_options', 'grid-modules', array( __CLASS__, 'page' ) );
        add_submenu_page( 'grid-arendal', 'Section and field names', 'Section & field names', 'manage_options', 'grid-labels', array( 'GRID_Arendal_Labels', 'page' ) );
    }
    public static function page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $s = self::settings(); $groups = function_exists( 'acf_get_field_groups' ) ? acf_get_field_groups() : array();
        echo '<div class="wrap"><h1>GRID-Arendal modules</h1><p>Enable only the tools this website needs. Existing accessibility preferences remain in their own settings. ACF, Beaver Builder/Themer, GTranslate, WooCommerce and LearnPress remain separate plugins.</p>';
        settings_errors(); echo '<form action="options.php" method="post">'; settings_fields( 'grid_arendal_tools' );
        echo '<input type="hidden" name="' . esc_attr( self::OPTION ) . '[modules][]" value=""><table class="widefat striped"><thead><tr><th>Module</th><th>Purpose</th><th>Status</th></tr></thead><tbody>';
        foreach ( self::catalog() as $key => $m ) {
            echo '<tr><td><label><input type="checkbox" name="' . esc_attr( self::OPTION ) . '[modules][]" value="' . esc_attr( $key ) . '" ' . checked( self::enabled( $key ), true, false ) . '> <strong>' . esc_html( $m[0] ) . '</strong></label></td><td>' . esc_html( $m[1] ) . '</td><td>' . esc_html( ! self::enabled( $key ) ? 'Off' : ( self::ready( $key ) ? 'Available' : 'Waiting for required module/plugin' ) ) . '</td></tr>';
        }
        echo '</tbody></table><h2>Front-end workspace</h2><p>Place <code>[grid_workspace]</code> on a normal WordPress page, then select that page below. It is private to signed-in users with existing per-content editing permissions. No roles or capabilities are granted by this plugin. Course, order and builder editors are excluded.</p><label>Workspace page ID <input type="number" min="0" name="' . esc_attr( self::OPTION ) . '[workspace_page]" value="' . esc_attr( $s['workspace_page'] ) . '"></label><h3>Content sections</h3><p>Choose content types and the ACF groups their forms may edit. Only selected field groups are accepted on submission. Content keys, relationships, commerce fields and builder layouts outside the form are retained.</p>';
        foreach ( self::permitted_types() as $type => $obj ) {
            $v = $s['sections'][$type] ?? array(); $name = self::OPTION . '[sections][' . $type . ']';
            echo '<details class="grid-admin-section"><summary><strong>' . esc_html( $obj->label ) . '</strong> <code>' . esc_html( $type ) . '</code></summary><p><label><input type="checkbox" name="' . esc_attr( $name ) . '[enabled]" value="1" ' . checked( isset( $s['sections'][$type] ), true, false ) . '> Include in workspace</label></p><p><label>Workspace name <input name="' . esc_attr( $name ) . '[label]" value="' . esc_attr( $v['label'] ?? '' ) . '" placeholder="' . esc_attr( $obj->label ) . '"></label></p><fieldset><legend>Editable ACF groups</legend>';
            foreach ( $groups as $g ) echo '<label style="display:block"><input type="checkbox" name="' . esc_attr( $name ) . '[groups][]" value="' . esc_attr( $g['key'] ) . '" ' . checked( in_array( $g['key'], $v['groups'] ?? array(), true ), true, false ) . '> ' . esc_html( $g['title'] ) . ' <code>' . esc_html( $g['key'] ) . '</code></label>';
            echo '</fieldset><p>Map editing (optional): enter ACF field keys, not names, for numeric coordinate fields.</p><p><label>Latitude <input name="' . esc_attr( $name ) . '[latitude]" value="' . esc_attr( $v['latitude'] ?? '' ) . '"></label> <label>Longitude <input name="' . esc_attr( $name ) . '[longitude]" value="' . esc_attr( $v['longitude'] ?? '' ) . '"></label></p></details>';
        }
        echo '<h3>Review notifications</h3><p>Recipients also need publishing permission for the submitted content type. No mail is sent unless the notifications module is enabled and reviewers are selected.</p>';
        foreach ( get_users( array( 'fields' => array( 'ID', 'display_name' ), 'orderby' => 'display_name' ) ) as $u ) echo '<label style="display:block"><input type="checkbox" name="' . esc_attr( self::OPTION ) . '[reviewers][]" value="' . esc_attr( $u->ID ) . '" ' . checked( in_array( (int) $u->ID, $s['reviewers'], true ), true, false ) . '> ' . esc_html( $u->display_name ) . '</label>';
        echo '<h3>Private resource file storage</h3><p>For the private-file module, provide an existing writable directory outside the web root. Files are never added to the public media library.</p><input class="large-text" name="' . esc_attr( self::OPTION ) . '[private_root]" value="' . esc_attr( $s['private_root'] ) . '">';
        submit_button(); echo '</form><h2>Dependencies</h2><p><a href="' . esc_url( admin_url( 'plugin-install.php?s=Advanced+Custom+Fields&tab=search&type=term' ) ) . '">Find ACF</a> · <a href="' . esc_url( admin_url( 'plugin-install.php?s=GTranslate&tab=search&type=term' ) ) . '">Find GTranslate</a> · <a href="' . esc_url( admin_url( 'plugin-install.php?s=LearnPress&tab=search&type=term' ) ) . '">Find LearnPress</a></p><p>These tools consolidate shared patterns from the CAMCA, BCK Hub and LMS editors. Project-specific permissions and existing data are retained; no legacy plugin or snippet is automatically deactivated. Test a configured section before switching an existing website to this workspace.</p></div>';
    }
}
