<?php
if ( ! defined( 'ABSPATH' ) ) exit;
final class GRID_Arendal_Labels {
    private static $discovering = false;
    public static function boot() {
        add_filter( 'acf/load_field_group', array( __CLASS__, 'group' ), 99 );
        add_filter( 'acf/load_field', array( __CLASS__, 'field' ), 99 );
        add_filter( 'register_post_type_args', array( __CLASS__, 'post_type' ), 99, 2 );
    }
    public static function settings() { $v = get_option( GRID_Arendal_Core::LABELS, array() ); return is_array( $v ) ? $v : array(); }
    public static function group( $group ) {
        if ( self::$discovering || ! GRID_Arendal_Core::enabled( 'labels' ) || ! is_array( $group ) ) return $group;
        $s = self::settings(); $key = $group['key'] ?? '';
        if ( ! empty( $s['groups'][$key] ) ) $group['title'] = $s['groups'][$key];
        return $group;
    }
    public static function field( $field ) {
        if ( self::$discovering || ! GRID_Arendal_Core::enabled( 'labels' ) || ! is_array( $field ) ) return $field;
        $s = self::settings(); $key = $field['key'] ?? '';
        if ( ! empty( $s['fields'][$key] ) ) $field['label'] = $s['fields'][$key];
        return $field;
    }
    public static function post_type( $args, $type ) {
        if ( self::$discovering || ! GRID_Arendal_Core::enabled( 'labels' ) ) return $args;
        $s = self::settings(); $v = $s['types'][$type] ?? array();
        if ( empty( $v['plural'] ) || empty( $v['singular'] ) ) return $args;
        $p = $v['plural']; $one = $v['singular']; $l = isset( $args['labels'] ) ? (array) $args['labels'] : array();
        $args['labels'] = array_merge( $l, array( 'name' => $p, 'singular_name' => $one, 'menu_name' => $p, 'all_items' => 'All ' . $p, 'add_new_item' => 'Add ' . $one, 'edit_item' => 'Edit ' . $one, 'new_item' => 'New ' . $one, 'view_item' => 'View ' . $one, 'search_items' => 'Search ' . $p ) );
        $args['label'] = $p; return $args;
    }
    public static function discover() {
        self::$discovering = true; $out = array( 'groups' => array(), 'fields' => array() );
        try {
            if ( function_exists( 'acf_get_field_groups' ) ) foreach ( acf_get_field_groups() as $g ) {
                $out['groups'][$g['key']] = $g['title']; self::walk( acf_get_fields( $g ), $out['fields'] );
            }
        } finally { self::$discovering = false; }
        return $out;
    }
    private static function walk( $fields, &$out, $depth = 0 ) {
        if ( $depth > 12 ) return;
        foreach ( (array) $fields as $f ) {
            if ( ! is_array( $f ) ) continue;
            if ( isset( $f['key'], $f['label'] ) ) $out[$f['key']] = $f['label'];
            if ( ! empty( $f['sub_fields'] ) ) self::walk( $f['sub_fields'], $out, $depth + 1 );
            foreach ( (array) ( $f['layouts'] ?? array() ) as $layout ) if ( isset( $layout['sub_fields'] ) ) self::walk( $layout['sub_fields'], $out, $depth + 1 );
        }
    }
    public static function sanitize( $input ) {
        $input = is_array( $input ) ? $input : array(); $known = self::discover(); $out = array( 'groups' => array(), 'fields' => array(), 'types' => array() );
        foreach ( array( 'groups', 'fields' ) as $kind ) foreach ( (array) ( $input[$kind] ?? array() ) as $key => $value ) {
            if ( ! isset( $known[$kind][$key] ) ) continue;
            $v = GRID_Arendal_Core::text( $value, 120 ); if ( '' !== $v ) $out[$kind][$key] = $v;
        }
        foreach ( GRID_Arendal_Core::permitted_types() as $type => $obj ) {
            $v = $input['types'][$type] ?? array(); if ( ! is_array( $v ) ) continue;
            $plural = GRID_Arendal_Core::text( $v['plural'] ?? '', 100 ); $singular = GRID_Arendal_Core::text( $v['singular'] ?? '', 100 );
            if ( $plural && $singular ) $out['types'][$type] = array( 'plural' => $plural, 'singular' => $singular );
        }
        return $out;
    }
    public static function page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $s = self::settings(); $known = self::discover(); $option = GRID_Arendal_Core::LABELS;
        echo '<div class="wrap"><h1>Section and field names</h1><p>These are display-name overrides. ACF keys, field names, saved values, content-type keys, relationships and URL slugs are unchanged. Leave a box empty to use the original label. Disabling this module restores original labels.</p>';
        if ( ! GRID_Arendal_Core::enabled( 'labels' ) ) echo '<p><strong>This module is off.</strong> Your names are retained but not applied.</p>';
        settings_errors(); echo '<form action="options.php" method="post">'; settings_fields( 'grid_arendal_labels' );
        echo '<h2>Content section names</h2><table class="widefat striped"><thead><tr><th>Existing section</th><th>Plural name</th><th>Singular name</th></tr></thead><tbody>';
        foreach ( GRID_Arendal_Core::permitted_types() as $type => $obj ) echo '<tr><th>' . esc_html( $obj->label ) . ' <code>' . esc_html( $type ) . '</code></th><td><input name="' . esc_attr( $option . '[types][' . $type . '][plural]' ) . '" value="' . esc_attr( $s['types'][$type]['plural'] ?? '' ) . '"></td><td><input name="' . esc_attr( $option . '[types][' . $type . '][singular]' ) . '" value="' . esc_attr( $s['types'][$type]['singular'] ?? '' ) . '"></td></tr>';
        echo '</tbody></table>';
        foreach ( array( 'groups' => 'ACF section groups', 'fields' => 'ACF fields and tabs' ) as $kind => $title ) {
            echo '<h2>' . esc_html( $title ) . '</h2>';
            if ( ! $known[$kind] ) echo '<p>No ACF definitions found. Install ACF and configure field groups first.</p>';
            foreach ( $known[$kind] as $key => $label ) echo '<p><label>' . esc_html( $label ?: $key ) . ' <code>' . esc_html( $key ) . '</code><br><input class="regular-text" name="' . esc_attr( $option . '[' . $kind . '][' . $key . ']' ) . '" value="' . esc_attr( $s[$kind][$key] ?? '' ) . '" placeholder="' . esc_attr( $label ) . '"></label></p>';
        }
        submit_button( 'Save display names' ); echo '</form></div>';
    }
}
