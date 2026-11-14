<?php
/** Editable ACF starter schemas. Existing definitions are never overwritten. */
if ( ! defined( 'ABSPATH' ) ) exit;
final class GRID_Arendal_Fields {
    const OPTION = 'grid_arendal_starter_groups';
    private static $issues = array();
    public static function definitions() {
        $groups = array(
            'page' => array( 'Page details', array(
                array( 'summary', 'Page summary', 'textarea' ),
                array( 'related_link', 'Related link', 'link' ),
                array( 'credit', 'Content credit / source', 'text' ),
            ) ),
            'post' => array( 'Article details', array(
                array( 'standfirst', 'Standfirst / short introduction', 'textarea' ),
                array( 'source_url', 'Source URL', 'url' ),
                array( 'credit', 'Article credit', 'text' ),
            ) ),
            'product' => array( 'Resource details', array(
                array( 'url', 'External resource URL', 'url' ),
                array( 'year', 'Publication year', 'number' ),
                array( 'citation', 'Citation / reference', 'textarea' ),
                array( 'license', 'Licence / reuse terms', 'text' ),
            ) ),
            'awsm_team_member' => array( 'Team profile details', array(
                array( 'role', 'Role / job title', 'text' ),
                array( 'organisation', 'Organisation', 'text' ),
                array( 'profile_url', 'Public profile URL', 'url' ),
            ) ),
        );
        $out = array();
        foreach ( $groups as $type => $group ) {
            $prefixes = array( 'page' => 'page', 'post' => 'article', 'product' => 'resource', 'awsm_team_member' => 'team' );
            $prefix = 'grid_' . $prefixes[$type]; $fields = array();
            foreach ( $group[1] as $def ) {
                $field = array( 'key' => 'field_' . $prefix . '_' . $def[0], 'name' => $prefix . '_' . $def[0], 'label' => $def[1], 'type' => $def[2], 'required' => 0 );
                if ( 'textarea' === $def[2] ) { $field['rows'] = 3; $field['new_lines'] = ''; }
                if ( 'link' === $def[2] ) $field['return_format'] = 'array';
                if ( 'number' === $def[2] ) { $field['min'] = 1000; $field['max'] = 2200; $field['step'] = 1; }
                $fields[] = $field;
            }
            $out[$type] = array( 'key' => 'group_' . $prefix . '_details', 'title' => $group[0], 'fields' => $fields, 'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => $type ) ) ), 'position' => 'normal', 'style' => 'default', 'label_placement' => 'top', 'instruction_placement' => 'label', 'active' => true, 'show_in_rest' => 0 );
        }
        return $out;
    }
    public static function boot() {
        add_action( 'admin_init', array( __CLASS__, 'seed' ), 30 );
        add_action( 'admin_menu', array( __CLASS__, 'menu' ), 31 );
        add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
    }
    public static function seed() {
        if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_import_field_group' ) || ! function_exists( 'acf_get_field_group' ) ) return;
        global $wpdb;
        $lock = 'grid_acf_seed_' . get_current_blog_id();
        if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) ) ) return;
        try {
            $stored = get_option( self::OPTION, array() ); $done = is_array( $stored ) ? $stored : array();
            foreach ( self::definitions() as $type => $group ) {
                $key = $group['key'];
                // The marker also respects deliberate deletion/trashing by the owner.
                if ( array_key_exists( $key, $done ) || ! post_type_exists( $type ) ) continue;
                $existing = acf_get_field_group( $key );
                if ( $existing ) { $done[$key] = (int) ( $existing['ID'] ?? 0 ); continue; }
                $conflict = false;
                foreach ( $group['fields'] as $f ) if ( function_exists( 'acf_get_field' ) && acf_get_field( $f['key'] ) ) { $conflict = true; break; }
                if ( $conflict ) { self::$issues[] = 'A starter field key already exists outside its group: ' . $key . '. Existing fields were retained.'; continue; }
                $created = acf_import_field_group( $group );
                $created = is_array( $created ) ? $created : acf_get_field_group( $key );
                if ( is_array( $created ) && ! empty( $created['ID'] ) ) $done[$key] = (int) $created['ID'];
                else self::$issues[] = 'The starter group could not be created: ' . $group['title'];
            }
            if ( $done !== $stored ) update_option( self::OPTION, $done, false );
        } catch ( Throwable $e ) { self::$issues[] = 'Starter groups could not be completed. Existing groups and content were retained. Open GRID-Arendal → Field groups to review the setup.'; }
        finally { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); }
    }
    public static function menu() { add_submenu_page( 'grid-arendal', 'Editable field groups', 'Field groups', 'manage_options', 'grid-fields', array( __CLASS__, 'page' ) ); }
    public static function notices() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        foreach ( self::$issues as $issue ) echo '<div class="notice notice-warning"><p>' . esc_html( $issue ) . '</p></div>';
    }
    public static function page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        echo '<div class="wrap"><h1>Editable field groups</h1><p>Starter groups are created automatically for available content types. These are ordinary database-backed ACF definitions: you can add fields, change labels/types and adjust location rules in ACF’s native designer. Edits and deletions are preserved on future plugin updates. Existing content values are never seeded or overwritten.</p>';
        if ( ! function_exists( 'acf_get_field_groups' ) ) { echo '<p>Install and activate ACF to create the starter groups.</p><p><a class="button" href="' . esc_url( admin_url( 'plugin-install.php?s=Advanced+Custom+Fields&tab=search&type=term' ) ) . '">Find ACF</a></p></div>'; return; }
        echo '<table class="widefat striped"><thead><tr><th>Field group</th><th>Content type</th><th>Fields</th><th>Manage</th></tr></thead><tbody>';
        $marker = get_option( self::OPTION, array() ); $marker = is_array( $marker ) ? $marker : array();
        foreach ( self::definitions() as $type => $def ) {
            $group = acf_get_field_group( $def['key'] ); $obj = get_post_type_object( $type );
            echo '<tr><td><strong>' . esc_html( $group ? $group['title'] : $def['title'] ) . '</strong><br><code>' . esc_html( $def['key'] ) . '</code></td><td>' . esc_html( $obj ? $obj->label : $type . ' (not installed)' ) . '</td><td>';
            if ( $group ) { $labels = array(); foreach ( (array) acf_get_fields( $group ) as $field ) $labels[] = $field['label']; echo esc_html( implode( ', ', $labels ) ); }
            else echo esc_html( array_key_exists( $def['key'], $marker ) ? 'Removed or managed externally; not recreated automatically.' : 'Created when its content type and ACF are available.' );
            echo '</td><td>';
            if ( $group && ! empty( $group['ID'] ) ) echo '<a class="button" href="' . esc_url( get_edit_post_link( $group['ID'], 'raw' ) ) . '">Edit fields</a>';
            echo '</td></tr>';
        }
        echo '</tbody></table><p><a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=acf-field-group' ) ) . '">All ACF groups</a> <a class="button" href="' . esc_url( admin_url( 'post-new.php?post_type=acf-field-group' ) ) . '">Add a custom group</a> <a class="button" href="' . esc_url( admin_url( 'admin.php?page=grid-labels' ) ) . '">Display names</a></p><p>Select the groups to expose in each front-end section under <a href="' . esc_url( admin_url( 'admin.php?page=grid-modules' ) ) . '">Modules &amp; workspace</a>. These fields do not automatically alter public Beaver Themer templates; connect fields to a template where needed.</p></div>';
    }
}
