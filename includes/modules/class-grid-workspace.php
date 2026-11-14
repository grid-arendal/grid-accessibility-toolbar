<?php
/** Shared content workspace, inspired by GRID's project editors; no site-specific IDs. */
if ( ! defined( 'ABSPATH' ) ) exit;
final class GRID_Arendal_Workspace {
    const PENDING = '_grid_editor_pending';
    const LOG = '_grid_editor_activity';
    private static $error = '';
    public static function boot() {
        add_shortcode( 'grid_workspace', array( __CLASS__, 'screen' ) );
        add_action( 'template_redirect', array( __CLASS__, 'prepare' ), 1 );
        add_action( 'admin_post_grid_workspace_save', array( __CLASS__, 'save' ) );
        add_action( 'admin_post_nopriv_grid_workspace_save', array( __CLASS__, 'deny' ) );
        add_filter( 'the_content', array( __CLASS__, 'edit_link' ), 99 );
        add_filter( 'wp_nav_menu_objects', array( __CLASS__, 'menu_items' ), 99 );
        require_once __DIR__ . '/class-grid-editor-extras.php';
        GRID_Arendal_Editor_Extras::boot();
    }
    public static function deny( $message = 'Sign in with an authorised account to use this workspace.', $status = 403 ) { wp_die( esc_html( $message ), 'GRID-Arendal workspace', array( 'response' => $status ) ); }
    public static function page() { return (int) GRID_Arendal_Core::settings()['workspace_page']; }
    public static function url( $args = array() ) { $base = self::page() ? get_permalink( self::page() ) : home_url( '/' ); return add_query_arg( $args, $base ); }
    public static function section( $type ) { $s = GRID_Arendal_Core::settings(); return isset( $s['sections'][$type] ) && isset( GRID_Arendal_Core::permitted_types()[$type] ) ? $s['sections'][$type] : false; }
    public static function label( $type ) { $s = self::section( $type ); $o = get_post_type_object( $type ); return ! empty( $s['label'] ) ? $s['label'] : ( $o ? $o->label : $type ); }
    public static function can_create( $type ) {
        $o = get_post_type_object( $type );
        return is_user_logged_in() && self::section( $type ) && $o && current_user_can( $o->cap->create_posts ?? $o->cap->edit_posts );
    }
    public static function can_edit( $id ) { $p = get_post( $id ); return is_user_logged_in() && $p && ! in_array( (int) $id, self::excluded_pages(), true ) && self::section( $p->post_type ) && current_user_can( 'edit_post', $id ); }
    public static function excluded_pages() { return array_values( array_unique( array_merge( array( self::page() ), array_map( 'intval', (array) GRID_Arendal_Core::settings()['excluded_pages'] ) ) ) ); }
    public static function can_access() {
        if ( ! GRID_Arendal_Core::ready( 'workspace' ) || ! is_user_logged_in() || ! self::page() || ! current_user_can( 'read_post', self::page() ) ) return false;
        foreach ( array_keys( GRID_Arendal_Core::settings()['sections'] ) as $type ) if ( self::can_create( $type ) ) return true;
        return false;
    }
    public static function matches_url( $url ) {
        if ( ! self::page() || ! is_string( $url ) || '' === $url ) return false;
        $target = wp_parse_url( get_permalink( self::page() ) ); $link = wp_parse_url( html_entity_decode( $url, ENT_QUOTES, 'UTF-8' ) );
        if ( ! is_array( $target ) || ! is_array( $link ) ) return false;
        if ( isset( $link['scheme'] ) && ! in_array( strtolower( $link['scheme'] ), array( 'http', 'https' ), true ) ) return false;
        if ( isset( $link['host'] ) && strtolower( $link['host'] ) !== strtolower( $target['host'] ?? '' ) ) return false;
        if ( isset( $link['host'] ) && (int) ( $link['port'] ?? ( 'https' === ( $link['scheme'] ?? '' ) ? 443 : 80 ) ) !== (int) ( $target['port'] ?? ( 'https' === ( $target['scheme'] ?? '' ) ? 443 : 80 ) ) ) return false;
        if ( rtrim( $link['path'] ?? '/', '/' ) !== rtrim( $target['path'] ?? '/', '/' ) ) return false;
        $q = array(); parse_str( $target['query'] ?? '', $q );
        if ( isset( $q['page_id'] ) ) { $other = array(); parse_str( $link['query'] ?? '', $other ); return isset( $other['page_id'] ) && is_scalar( $other['page_id'] ) && (int) $other['page_id'] === (int) $q['page_id']; }
        return true;
    }
    public static function menu_items( $items ) {
        if ( self::can_access() ) return $items;
        $removed = array();
        foreach ( $items as $i ) if ( in_array( 'grid-workspace-link', (array) ( $i->classes ?? array() ), true ) || ( 'page' === ( $i->object ?? '' ) && self::page() && (int) ( $i->object_id ?? 0 ) === self::page() ) || self::matches_url( $i->url ?? '' ) ) $removed[(int) $i->ID] = true;
        do { $changed = false; foreach ( $items as $i ) if ( isset( $removed[(int) ( $i->menu_item_parent ?? 0 )] ) && ! isset( $removed[(int) $i->ID] ) ) { $removed[(int) $i->ID] = true; $changed = true; } } while ( $changed );
        return array_values( array_filter( $items, function( $i ) use ( $removed ) { return ! isset( $removed[(int) $i->ID] ); } ) );
    }
    public static function can_review( $id ) {
        $p = get_post( $id ); $o = $p ? get_post_type_object( $p->post_type ) : null;
        return $o && self::can_edit( $id ) && current_user_can( $o->cap->publish_posts );
    }
    public static function private_headers() {
        if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true );
        nocache_headers(); header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0' );
        header( 'Vary: Cookie', false ); header( 'X-Robots-Tag: noindex, nofollow', true );
        do_action( 'litespeed_control_set_nocache', 'GRID-Arendal editing workspace' );
    }
    public static function prepare() {
        if ( GRID_Accessibility_Toolbar::editor_active() ) return;
        if ( self::can_access() || ( self::page() && self::matches_url( $_SERVER['REQUEST_URI'] ?? '' ) ) ) self::private_headers();
        if ( ! self::page() || ! is_page( self::page() ) ) return;
        self::private_headers();
        add_filter( 'body_class', function( $classes ) { $classes[] = 'grid-workspace-screen'; return $classes; } );
        wp_enqueue_style( 'grid-workspace', plugins_url( '../../assets/workspace.css', __FILE__ ), array(), GRID_Accessibility_Toolbar::VERSION );
        if ( is_user_logged_in() ) { acf_enqueue_scripts(); wp_enqueue_media(); }
        wp_enqueue_script( 'grid-workspace', plugins_url( '../../assets/workspace.js', __FILE__ ), array(), GRID_Accessibility_Toolbar::VERSION, true );
        if ( GRID_Arendal_Core::ready( 'maps' ) ) GRID_Arendal_Editor_Extras::map_assets();
    }
    public static function fields( $type ) {
        $s = self::section( $type ); $out = array();
        foreach ( (array) ( $s['groups'] ?? array() ) as $key ) {
            $g = acf_get_field_group( $key ); if ( ! $g || ( isset( $g['active'] ) && ! $g['active'] ) ) continue;
            foreach ( (array) acf_get_fields( $g ) as $f ) if ( is_array( $f ) && isset( $f['key'] ) ) $out[$f['key']] = $f;
        }
        return $out;
    }
    public static function base_version( $id ) {
        $p = get_post( $id ); if ( ! $p ) return '';
        $fields = array(); foreach ( self::fields( $p->post_type ) as $key => $field ) $fields[$key] = get_field( $key, $id, false );
        return hash( 'sha256', wp_json_encode( array( $p->post_title, $p->post_content, $p->post_excerpt, $p->post_status, $p->post_modified_gmt, $fields ) ) );
    }
    public static function version( $id ) { return hash( 'sha256', self::base_version( $id ) . wp_json_encode( get_post_meta( $id, self::PENDING, true ) ) ); }
    public static function input( $key, $default = '' ) { return isset( $_GET[$key] ) && is_scalar( $_GET[$key] ) ? sanitize_text_field( wp_unslash( $_GET[$key] ) ) : $default; }
    public static function screen() {
        if ( GRID_Accessibility_Toolbar::editor_active() ) return '<p>The private GRID-Arendal workspace appears here when you leave the layout editor.</p>';
        if ( ! self::page() || get_queried_object_id() !== self::page() ) return current_user_can( 'manage_options' ) ? '<p>Select this page under GRID-Arendal → Modules &amp; workspace before using the editor.</p>' : '';
        if ( ! is_user_logged_in() ) return '<section class="grid-workspace"><h2>Content workspace</h2><p>Please <a href="' . esc_url( wp_login_url( self::url() ) ) . '">sign in</a> with an authorised account.</p></section>';
        if ( ! self::can_access() ) return '<p>This workspace is not available to this account.</p>';
        $types = array_filter( array_keys( GRID_Arendal_Core::settings()['sections'] ), array( __CLASS__, 'can_create' ) );
        if ( ! $types ) return '<p>No editable sections are available to this account. Contact your site administrator.</p>';
        $type = self::input( 'grid_section', reset( $types ) ); if ( ! in_array( $type, $types, true ) ) return '<p>This section is not available to this account.</p>';
        ob_start(); echo '<section class="grid-workspace" aria-label="Content workspace"><header class="grid-workspace-header"><div><p class="grid-eyebrow">GRID-Arendal</p><h2>Content workspace</h2></div><a href="' . esc_url( home_url( '/' ) ) . '">View website</a></header><nav class="grid-workspace-nav" aria-label="Workspace sections">';
        foreach ( $types as $t ) echo '<a ' . ( $t === $type ? 'aria-current="page" ' : '' ) . 'href="' . esc_url( self::url( array( 'grid_section' => $t ) ) ) . '">' . esc_html( self::label( $t ) ) . '</a>';
        echo '</nav>';
        $notice = self::input( 'grid_notice' ); $messages = array( 'saved' => 'Draft saved.', 'updated' => 'Changes saved.', 'submitted' => 'Submitted for review. Published content remains unchanged until approval.', 'approved' => 'Changes approved and published.', 'rejected' => 'Submission returned to its author.', 'imported' => 'New drafts imported.', 'file' => 'Private file uploaded.' );
        if ( isset( $messages[$notice] ) ) echo '<p class="grid-workspace-notice" role="status">' . esc_html( $messages[$notice] ) . '</p>';
        if ( 'edit' === self::input( 'grid_action' ) ) self::form( $type, absint( self::input( 'grid_item', 0 ) ) ); else self::listing( $type );
        echo '</section>'; return ob_get_clean();
    }
    private static function listing( $type ) {
        $sort = self::input( 'grid_sort', 'modified' ); if ( ! in_array( $sort, array( 'title', 'modified', 'date' ), true ) ) $sort = 'modified';
        $order = 'ASC' === self::input( 'grid_order', 'DESC' ) ? 'ASC' : 'DESC'; $search = self::input( 'grid_search' ); $page = max( 1, min( 10000, absint( self::input( 'grid_page', 1 ) ) ) );
        $o = get_post_type_object( $type ); $args = array( 'post_type' => $type, 'post_status' => array( 'publish', 'private', 'draft', 'pending' ), 'posts_per_page' => 20, 'paged' => $page, 's' => $search, 'orderby' => $sort, 'order' => $order );
        if ( ! current_user_can( $o->cap->edit_others_posts ) ) $args['author'] = get_current_user_id();
        if ( 'page' === $type ) $args['post__not_in'] = self::excluded_pages();
        $query = new WP_Query( $args );
        echo '<div class="grid-workspace-actions"><h3>' . esc_html( self::label( $type ) ) . '</h3><a class="grid-button" href="' . esc_url( self::url( array( 'grid_section' => $type, 'grid_action' => 'edit' ) ) ) . '">Add new draft</a></div><form method="get" action="' . esc_url( get_permalink( self::page() ) ) . '"><input type="hidden" name="grid_section" value="' . esc_attr( $type ) . '"><label>Search this section <input type="search" name="grid_search" value="' . esc_attr( $search ) . '"></label><button type="submit">Search</button></form>';
        echo '<div class="grid-table-scroll"><table><caption class="screen-reader-text">' . esc_html( self::label( $type ) ) . '</caption><thead><tr>';
        foreach ( array( 'title' => 'Title', 'date' => 'Published', 'modified' => 'Updated' ) as $k => $label ) {
            $u = self::url( array( 'grid_section' => $type, 'grid_search' => $search, 'grid_sort' => $k, 'grid_order' => $sort === $k && 'ASC' === $order ? 'DESC' : 'ASC' ) );
            echo '<th scope="col" ' . ( $sort === $k ? 'aria-sort="' . ( 'ASC' === $order ? 'ascending' : 'descending' ) . '"' : '' ) . '><a href="' . esc_url( $u ) . '">' . esc_html( $label ) . '</a></th>';
        }
        echo '<th scope="col">Status</th><th scope="col">Actions</th></tr></thead><tbody>';
        foreach ( $query->posts as $p ) {
            if ( ! self::can_edit( $p->ID ) ) continue;
            $pending = get_post_meta( $p->ID, self::PENDING, true );
            echo '<tr><th scope="row">' . esc_html( get_the_title( $p ) ?: '(Untitled)' ) . '</th><td>' . esc_html( 'publish' === $p->post_status ? get_the_date( '', $p ) : '—' ) . '</td><td>' . esc_html( get_the_modified_date( '', $p ) ) . '</td><td>' . esc_html( $pending ? 'Changes awaiting review' : $p->post_status ) . '</td><td><a href="' . esc_url( self::url( array( 'grid_section' => $type, 'grid_action' => 'edit', 'grid_item' => $p->ID ) ) ) . '">Edit<span class="screen-reader-text"> ' . esc_html( get_the_title( $p ) ) . '</span></a>';
            if ( 'publish' === $p->post_status ) echo ' · <a href="' . esc_url( get_permalink( $p ) ) . '">View<span class="screen-reader-text"> ' . esc_html( get_the_title( $p ) ) . '</span></a>';
            echo '</td></tr>';
        }
        if ( ! $query->posts ) echo '<tr><td colspan="5">No matching items.</td></tr>';
        echo '</tbody></table></div><nav aria-label="Content pages">' . wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'grid_page', '%#%', self::url( array( 'grid_section' => $type, 'grid_search' => $search, 'grid_sort' => $sort, 'grid_order' => $order ) ) ), 'current' => $page, 'total' => max( 1, $query->max_num_pages ) ) ) ) . '</nav>';
        if ( GRID_Arendal_Core::ready( 'csv' ) && current_user_can( 'manage_options' ) ) GRID_Arendal_Editor_Extras::csv_form( $type );
    }
    private static function form( $type, $id ) {
        if ( ( $id && ( ! self::can_edit( $id ) || get_post_type( $id ) !== $type ) ) || ( ! $id && ! self::can_create( $type ) ) ) { echo '<p>You cannot edit this item.</p>'; return; }
        $p = $id ? get_post( $id ) : null; $pending = $id ? get_post_meta( $id, self::PENDING, true ) : array();
        echo '<p><a href="' . esc_url( self::url( array( 'grid_section' => $type ) ) ) . '">← Back to ' . esc_html( self::label( $type ) ) . '</a></p><h3>' . esc_html( $p ? 'Edit: ' . get_the_title( $p ) : 'New draft' ) . '</h3>';
        if ( $pending ) {
            echo '<div class="grid-review-box"><h4>Changes awaiting review</h4><p>Current published content is retained. A fresh review action is required before it changes.</p><dl><dt>Proposed title</dt><dd>' . esc_html( $pending['data']['title'] ?? '' ) . '</dd><dt>Proposed summary</dt><dd>' . esc_html( wp_strip_all_tags( $pending['data']['excerpt'] ?? '' ) ) . '</dd></dl><details><summary>Proposed description</summary>' . wp_kses_post( $pending['data']['content'] ?? '' ) . '</details>';
            if ( ! empty( $pending['data']['fields'] ) ) echo '<p>The submission also includes the ACF fields shown below.</p>';
            if ( self::can_review( $id ) ) {
                echo '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post">'; self::form_hidden( $type, $id ); echo '<label>Review note <textarea name="grid_note" rows="2"></textarea></label><button name="grid_command" value="approve">Approve and publish</button> <button name="grid_command" value="reject">Return to author</button></form>';
            }
            echo '</div>';
        }
        $builder = $id && get_post_meta( $id, '_fl_builder_enabled', true );
        echo '<form class="grid-editor-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post">'; self::form_hidden( $type, $id );
        echo '<label>Title <input name="grid_title" required maxlength="250" value="' . esc_attr( $p ? $p->post_title : '' ) . '"></label><label>Summary <textarea name="grid_excerpt" rows="3">' . esc_textarea( $p ? $p->post_excerpt : '' ) . '</textarea></label>';
        if ( $builder ) echo '<p>This item uses Beaver Builder. <a href="' . esc_url( add_query_arg( 'fl_builder', '', get_permalink( $id ) ) ) . '">Edit its layout in Beaver Builder</a>. The workspace preserves that layout and its main content.</p>';
        else {
            echo '<label for="grid_editor_description">Description</label>';
            echo '<div class="grid-format-toolbar" role="group" aria-label="Description formatting">';
            foreach ( array( 'Bold' => 'Bold', 'Italic' => 'Italic', 'InsertUnorderedList' => 'Bullet list', 'InsertOrderedList' => 'Numbered list', 'Link' => 'Add link', 'Undo' => 'Undo', 'Redo' => 'Redo' ) as $command => $label ) echo '<button type="button" data-grid-format="' . esc_attr( $command ) . '" disabled>' . esc_html( $label ) . '</button>';
            echo '</div>';
            wp_editor( $p ? $p->post_content : '', 'grid_editor_description', array( 'textarea_name' => 'grid_content', 'textarea_rows' => 12, 'media_buttons' => false, 'teeny' => true, 'quicktags' => false, 'editor_height' => 280, 'tinymce' => array( 'toolbar' => false, 'menubar' => false, 'statusbar' => false, 'content_style' => 'body{font-family:Arial,sans-serif;color:#33103c;background:#fff;font-size:16px;line-height:1.6}' ) ) );
            echo '<p>Basic formatting and links are supported. Scripts and embedded editor code are not accepted.</p>';
        }
        foreach ( (array) self::section( $type )['groups'] as $key ) {
            $group = acf_get_field_group( $key ); if ( ! $group || ( isset( $group['active'] ) && ! $group['active'] ) ) continue;
            echo '<section class="grid-acf-group"><h4>' . esc_html( $group['title'] ) . '</h4><div class="acf-fields">';
            foreach ( (array) acf_get_fields( $group ) as $f ) { $f['value'] = $id ? get_field( $f['key'], $id, false ) : ( $f['default_value'] ?? null ); if ( $pending && array_key_exists( $f['key'], $pending['data']['fields'] ?? array() ) ) $f['value'] = $pending['data']['fields'][$f['key']]; acf_render_fields( array( $f ), $id ); }
            echo '</div></section>';
        }
        if ( GRID_Arendal_Core::ready( 'maps' ) ) GRID_Arendal_Editor_Extras::map_form( $type );
        echo '<p>' . esc_html( $p && 'publish' === $p->post_status ? ( GRID_Arendal_Core::ready( 'review' ) ? 'Submit proposed changes for review. The live version stays unchanged until approval.' : 'Saving updates the published item immediately. Publishing permission is required.' ) : 'New items are saved as drafts. Use your normal publishing workflow.' ) . '</p><div class="grid-save-actions">';
        if ( ! $pending ) {
            if ( ! $p || 'publish' !== $p->post_status || ! GRID_Arendal_Core::ready( 'review' ) ) echo '<button type="submit" name="grid_command" value="save">' . ( $p && 'publish' === $p->post_status ? 'Save published changes' : 'Save draft' ) . '</button> ';
            if ( GRID_Arendal_Core::ready( 'review' ) ) echo '<button type="submit" name="grid_command" value="submit">Submit for review</button>';
        } else echo '<p>Return the submission before editing it again. This prevents late saves from replacing a submitted version.</p>';
        echo '</div><p class="grid-saving-status" role="status"></p></form>';
        if ( $id && GRID_Arendal_Core::ready( 'private_files' ) ) GRID_Arendal_Editor_Extras::files_form( $id );
        if ( $id && GRID_Arendal_Core::ready( 'review' ) ) {
            echo '<details><summary>Recent editorial activity</summary><ul>';
            foreach ( array_reverse( is_array( get_post_meta( $id, self::LOG, true ) ) ? get_post_meta( $id, self::LOG, true ) : array() ) as $event ) echo '<li>' . esc_html( ( $event['time'] ?? '' ) . ' · ' . ( $event['event'] ?? '' ) . ' · ' . ( $event['note'] ?? '' ) ) . '</li>';
            echo '</ul></details>';
        }
    }
    private static function form_hidden( $type, $id ) {
        echo '<input type="hidden" name="action" value="grid_workspace_save"><input type="hidden" name="grid_type" value="' . esc_attr( $type ) . '"><input type="hidden" name="grid_id" value="' . esc_attr( $id ) . '"><input type="hidden" name="grid_version" value="' . esc_attr( $id ? self::version( $id ) : 'new' ) . '"><input type="hidden" name="grid_submission" value="' . esc_attr( wp_generate_uuid4() ) . '">';
        wp_nonce_field( 'grid_workspace_' . $type . '_' . $id );
    }
    public static function validate_fields( $type, $values ) {
        if ( ! is_array( $values ) || count( $values ) > 300 ) throw new InvalidArgumentException( 'Invalid custom fields.' );
        $allowed = self::fields( $type ); $out = array();
        acf_reset_validation_errors();
        foreach ( $values as $key => $value ) {
            if ( ! isset( $allowed[$key] ) ) throw new InvalidArgumentException( 'An unconfigured custom field was submitted. Reload the form.' );
            $section = self::section( $type );
            foreach ( array( 'latitude' => 90, 'longitude' => 180 ) as $coordinate => $limit ) if ( ! empty( $section[$coordinate] ) && $section[$coordinate] === $key && '' !== $value && null !== $value && ( ! is_scalar( $value ) || ! is_numeric( $value ) || ! is_finite( (float) $value ) || abs( (float) $value ) > $limit ) ) throw new InvalidArgumentException( 'Map coordinate is out of range.' );
            $out[$key] = self::clean_field( $allowed[$key], $value );
            acf_validate_value( $out[$key], $allowed[$key], 'acf[' . $key . ']' );
        }
        foreach ( $allowed as $key => $f ) if ( ! array_key_exists( $key, $values ) && ! empty( $f['required'] ) && empty( $f['conditional_logic'] ) ) acf_validate_value( '', $f, 'acf[' . $key . ']' );
        $errors = acf_get_validation_errors();
        if ( $errors ) throw new InvalidArgumentException( wp_strip_all_tags( $errors[0]['message'] ?? 'A required field is missing or invalid.' ) );
        return $out;
    }
    public static function clean_field( $f, $value, $depth = 0 ) {
        if ( $depth > 12 ) throw new InvalidArgumentException( 'Nested fields are too deep.' );
        $type = $f['type'];
        if ( in_array( $type, array( 'tab', 'accordion', 'message' ), true ) ) return null;
        if ( in_array( $type, array( 'repeater', 'group', 'flexible_content' ), true ) ) {
            if ( '' === $value || null === $value || false === $value ) return array();
            if ( ! is_array( $value ) || count( $value ) > 200 ) throw new InvalidArgumentException( 'Invalid field rows.' );
            if ( 'group' === $type ) return self::clean_row( $f['sub_fields'] ?? array(), $value, $depth );
            $out = array();
            foreach ( $value as $index => $row ) {
                if ( 'acfcloneindex' === $index ) continue;
                if ( ! is_array( $row ) ) throw new InvalidArgumentException( 'Invalid custom field row.' );
                $fields = $f['sub_fields'] ?? array(); $layout = null;
                if ( 'flexible_content' === $type ) {
                    $name = $row['acf_fc_layout'] ?? ''; unset( $row['acf_fc_layout'] );
                    foreach ( (array) ( $f['layouts'] ?? array() ) as $l ) if ( $l['name'] === $name ) { $layout = $name; $fields = $l['sub_fields']; break; }
                    if ( null === $layout ) throw new InvalidArgumentException( 'Unknown content layout.' );
                }
                $clean = self::clean_row( $fields, $row, $depth ); if ( null !== $layout ) $clean['acf_fc_layout'] = $layout; $out[] = $clean;
            }
            return $out;
        }
        if ( in_array( $type, array( 'image', 'file', 'gallery' ), true ) ) {
            $many = 'gallery' === $type; $values = $many ? ( is_array( $value ) ? $value : array_filter( array( $value ) ) ) : array( $value ); $ids = array();
            if ( count( $values ) > 300 ) throw new InvalidArgumentException( 'Too many media files.' );
            foreach ( $values as $id ) {
                if ( '' === $id || null === $id ) continue;
                if ( ! is_scalar( $id ) || ! ctype_digit( (string) $id ) || 'attachment' !== get_post_type( (int) $id ) || ! current_user_can( 'read_post', (int) $id ) ) throw new InvalidArgumentException( 'A media file is not accessible.' );
                $parent = wp_get_post_parent_id( (int) $id ); if ( $parent && 'private' === get_post_status( $parent ) && ! current_user_can( 'read_post', $parent ) ) throw new InvalidArgumentException( 'A private media file is not accessible.' );
                if ( 'image' === $type && ! wp_attachment_is_image( (int) $id ) ) throw new InvalidArgumentException( 'Choose an image.' );
                $ids[] = (int) $id;
            }
            return $many ? $ids : ( $ids[0] ?? '' );
        }
        if ( in_array( $type, array( 'relationship', 'post_object', 'page_link' ), true ) ) {
            $many = 'relationship' === $type || ! empty( $f['multiple'] ); $values = is_array( $value ) ? $value : array_filter( array( $value ) ); $ids = array();
            if ( count( $values ) > 300 ) throw new InvalidArgumentException( 'Too many relationships.' );
            foreach ( $values as $id ) {
                if ( ! is_scalar( $id ) || ! ctype_digit( (string) $id ) ) throw new InvalidArgumentException( 'Invalid related item.' );
                $p = get_post( (int) $id );
                if ( ! $p || ( ! empty( $f['post_type'] ) && ! in_array( $p->post_type, (array) $f['post_type'], true ) ) || ( 'publish' !== $p->post_status && ! current_user_can( 'read_post', $p->ID ) ) ) throw new InvalidArgumentException( 'A related item is unavailable.' );
                $ids[] = (int) $id;
            }
            return $many ? array_values( array_unique( $ids ) ) : ( $ids[0] ?? '' );
        }
        if ( in_array( $type, array( 'select', 'checkbox', 'radio', 'button_group' ), true ) ) {
            $many = 'checkbox' === $type || ! empty( $f['multiple'] ); $values = is_array( $value ) ? $value : array( $value ); $out = array();
            foreach ( $values as $v ) {
                if ( '' === $v && ! $many ) continue;
                if ( ! is_scalar( $v ) || ! array_key_exists( (string) $v, (array) ( $f['choices'] ?? array() ) ) ) throw new InvalidArgumentException( 'Invalid field choice.' );
                $out[] = (string) $v;
            }
            return $many ? $out : ( $out[0] ?? '' );
        }
        if ( 'taxonomy' === $type ) {
            $tax = get_taxonomy( $f['taxonomy'] ?? '' );
            if ( ! $tax || ! current_user_can( $tax->cap->assign_terms ) ) throw new InvalidArgumentException( 'You cannot assign these terms.' );
            $values = is_array( $value ) ? $value : array_filter( array( $value ) ); $ids = array();
            if ( count( $values ) > 300 ) throw new InvalidArgumentException( 'Too many terms.' );
            foreach ( $values as $id ) { if ( ! is_scalar( $id ) || ! ctype_digit( (string) $id ) || ! term_exists( (int) $id, $tax->name ) ) throw new InvalidArgumentException( 'Invalid term.' ); $ids[] = (int) $id; }
            return in_array( $f['field_type'] ?? '', array( 'radio', 'select' ), true ) && empty( $f['multiple'] ) ? ( $ids[0] ?? '' ) : $ids;
        }
        if ( 'user' === $type ) {
            $values = is_array( $value ) ? $value : array_filter( array( $value ) ); $ids = array();
            if ( count( $values ) > 100 ) throw new InvalidArgumentException( 'Too many users.' );
            foreach ( $values as $id ) { if ( ! is_scalar( $id ) || ! ctype_digit( (string) $id ) || ! get_userdata( (int) $id ) || ( (int) $id !== get_current_user_id() && ! current_user_can( 'list_users' ) ) ) throw new InvalidArgumentException( 'User selection is not permitted.' ); $ids[] = (int) $id; }
            return ! empty( $f['multiple'] ) ? $ids : ( $ids[0] ?? '' );
        }
        if ( 'google_map' === $type ) {
            if ( ! is_array( $value ) ) throw new InvalidArgumentException( 'Invalid map value.' );
            $out = array();
            foreach ( $value as $k => $v ) {
                if ( ! in_array( $k, array( 'address', 'lat', 'lng', 'zoom', 'place_id', 'name', 'street_number', 'street_name', 'street_name_short', 'city', 'state', 'state_short', 'post_code', 'country', 'country_short' ), true ) || ! is_scalar( $v ) ) throw new InvalidArgumentException( 'Invalid map property.' );
                if ( in_array( $k, array( 'lat', 'lng', 'zoom' ), true ) ) { if ( ! is_numeric( $v ) || ! is_finite( (float) $v ) || ( 'lat' === $k && abs( (float) $v ) > 90 ) || ( 'lng' === $k && abs( (float) $v ) > 180 ) || ( 'zoom' === $k && ( $v < 0 || $v > 22 ) ) ) throw new InvalidArgumentException( 'Map coordinates are out of range.' ); $out[$k] = (float) $v; }
                else $out[$k] = GRID_Arendal_Core::text( $v, 500 );
            }
            return $out;
        }
        if ( 'link' === $type ) {
            if ( ! is_array( $value ) || array_diff( array_keys( $value ), array( 'url', 'title', 'target' ) ) ) throw new InvalidArgumentException( 'Invalid link.' );
            return array( 'url' => esc_url_raw( is_scalar( $value['url'] ?? '' ) ? $value['url'] : '', array( 'http', 'https', 'mailto', 'tel' ) ), 'title' => GRID_Arendal_Core::text( $value['title'] ?? '' ), 'target' => '_blank' === ( $value['target'] ?? '' ) ? '_blank' : '' );
        }
        if ( ! is_scalar( $value ) && null !== $value ) throw new InvalidArgumentException( 'Invalid field value.' );
        $v = (string) $value; if ( strlen( $v ) > 500000 ) throw new InvalidArgumentException( 'Field content is too large.' );
        if ( in_array( $type, array( 'number', 'range' ), true ) ) {
            if ( '' === $v ) return '';
            if ( ! is_numeric( $v ) || ! is_finite( (float) $v ) || ( isset( $f['min'] ) && '' !== $f['min'] && (float) $v < $f['min'] ) || ( isset( $f['max'] ) && '' !== $f['max'] && (float) $v > $f['max'] ) ) throw new InvalidArgumentException( 'Numeric field is out of range.' );
            return $v;
        }
        if ( 'true_false' === $type ) return '1' === $v ? 1 : 0;
        if ( 'url' === $type || 'oembed' === $type ) return esc_url_raw( $v, array( 'http', 'https' ) );
        if ( 'email' === $type ) { if ( $v && ! is_email( $v ) ) throw new InvalidArgumentException( 'Enter a valid email address.' ); return sanitize_email( $v ); }
        if ( 'wysiwyg' === $type ) return wp_kses_post( $v );
        if ( 'textarea' === $type ) return sanitize_textarea_field( $v );
        if ( in_array( $type, array( 'text', 'date_picker', 'date_time_picker', 'time_picker', 'color_picker' ), true ) ) return sanitize_text_field( $v );
        throw new InvalidArgumentException( 'This custom field type needs its native editor: ' . $type );
    }
    private static function clean_row( $fields, $row, $depth ) {
        $known = array(); foreach ( (array) $fields as $f ) $known[$f['key']] = $f;
        $out = array(); foreach ( $row as $key => $value ) { if ( ! isset( $known[$key] ) ) throw new InvalidArgumentException( 'Unknown nested custom field.' ); $out[$key] = self::clean_field( $known[$key], $value, $depth + 1 ); }
        return $out;
    }
    public static function lock( $key ) {
        global $wpdb; $name = 'grid_editor_' . substr( hash( 'sha256', get_current_blog_id() . ':' . $key ), 0, 40 );
        if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $name ) ) ) throw new RuntimeException( 'This item is being saved elsewhere. Wait a moment, then reload.' );
        return $name;
    }
    public static function unlock( $name ) { global $wpdb; $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $name ) ); }
    public static function log( $id, $event, $note = '' ) {
        if ( ! GRID_Arendal_Core::ready( 'review' ) ) return;
        $stored = get_post_meta( $id, self::LOG, true ); $log = is_array( $stored ) ? $stored : array(); $log[] = array( 'time' => current_time( 'mysql', true ), 'user' => get_current_user_id(), 'event' => $event, 'note' => GRID_Arendal_Core::text( $note, 500 ) );
        update_post_meta( $id, self::LOG, array_slice( $log, -30 ) );
    }
    public static function save() {
        if ( ! is_user_logged_in() || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) self::deny();
        self::private_headers(); $type = isset( $_POST['grid_type'] ) && is_string( $_POST['grid_type'] ) ? sanitize_key( $_POST['grid_type'] ) : ''; $id = isset( $_POST['grid_id'] ) && is_scalar( $_POST['grid_id'] ) ? absint( $_POST['grid_id'] ) : 0;
        if ( ! self::section( $type ) || ( $id ? ( ! self::can_edit( $id ) || get_post_type( $id ) !== $type ) : ! self::can_create( $type ) ) ) self::deny( 'You cannot edit this item.' );
        check_admin_referer( 'grid_workspace_' . $type . '_' . $id );
        $command = isset( $_POST['grid_command'] ) && is_string( $_POST['grid_command'] ) ? $_POST['grid_command'] : '';
        if ( ! in_array( $command, array( 'save', 'submit', 'approve', 'reject' ), true ) ) self::deny( 'Unknown editorial action.', 400 );
        $lock = ''; $transaction = false; $notice = ''; $notify = false;
        try {
            $lock = self::lock( $id ? 'post:' . $id : 'new:' . $type . ':' . get_current_user_id() );
            if ( $id && ( ! isset( $_POST['grid_version'] ) || ! is_string( $_POST['grid_version'] ) || ! hash_equals( self::version( $id ), $_POST['grid_version'] ) ) ) throw new RuntimeException( 'This item changed after you opened it. Reload before saving. Your submitted changes have not been applied.' );
            $p = $id ? get_post( $id ) : null; $pending = $id ? get_post_meta( $id, self::PENDING, true ) : array();
            if ( in_array( $command, array( 'approve', 'reject' ), true ) ) {
                if ( ! GRID_Arendal_Core::ready( 'review' ) || ! $id || ! $pending || ! self::can_review( $id ) ) throw new RuntimeException( 'This review action is not permitted.' );
                if ( 'approve' === $command && ! hash_equals( self::base_version( $id ), $pending['base'] ) ) throw new RuntimeException( 'The live item changed since submission. Return the submission and prepare a fresh version.' );
                $data = $pending['data'];
            } else {
                if ( $pending ) throw new RuntimeException( 'This item is already awaiting review. Return the submission before editing it.' );
                if ( 'submit' === $command && ! GRID_Arendal_Core::ready( 'review' ) ) throw new RuntimeException( 'Review is not enabled.' );
                if ( $p && 'publish' === $p->post_status && 'save' === $command && ( GRID_Arendal_Core::ready( 'review' ) || ! self::can_review( $id ) ) ) throw new RuntimeException( 'Published changes require the configured review workflow or publishing permission.' );
                $title = GRID_Arendal_Core::text( wp_unslash( $_POST['grid_title'] ?? '' ), 250 ); if ( '' === $title ) throw new InvalidArgumentException( 'A title is required.' );
                $builder = $p && get_post_meta( $id, '_fl_builder_enabled', true );
                $data = array( 'title' => $title, 'content' => $builder ? $p->post_content : wp_kses_post( is_string( $_POST['grid_content'] ?? '' ) ? wp_unslash( $_POST['grid_content'] ) : '' ), 'excerpt' => wp_kses_post( is_string( $_POST['grid_excerpt'] ?? '' ) ? wp_unslash( $_POST['grid_excerpt'] ) : '' ), 'fields' => self::validate_fields( $type, wp_unslash( $_POST['acf'] ?? array() ) ) );
            }
            global $wpdb; $wpdb->query( 'START TRANSACTION' ); $transaction = true;
            if ( ! $id ) {
                $token = isset( $_POST['grid_submission'] ) && is_string( $_POST['grid_submission'] ) ? $_POST['grid_submission'] : '';
                if ( ! preg_match( '/^[a-f0-9-]{36}$/', $token ) ) throw new InvalidArgumentException( 'Invalid submission token.' );
                $previous = get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'author' => get_current_user_id(), 'meta_key' => '_grid_submission_token', 'meta_value' => $token, 'fields' => 'ids', 'numberposts' => 1 ) );
                if ( $previous ) { $id = (int) $previous[0]; $wpdb->query( 'ROLLBACK' ); $transaction = false; self::unlock( $lock ); wp_safe_redirect( self::url( array( 'grid_section' => $type, 'grid_notice' => 'saved' ) ) ); exit; }
                $id = wp_insert_post( array( 'post_type' => $type, 'post_status' => 'draft', 'post_author' => get_current_user_id(), 'post_title' => $data['title'] ), true );
                if ( is_wp_error( $id ) ) throw new RuntimeException( 'The draft could not be created.' );
                update_post_meta( $id, '_grid_submission_token', $token ); $p = get_post( $id );
            }
            if ( 'reject' === $command ) { delete_post_meta( $id, self::PENDING ); self::log( $id, 'Returned to author', wp_unslash( $_POST['grid_note'] ?? '' ) ); $notice = 'rejected'; }
            elseif ( 'submit' === $command ) {
                update_post_meta( $id, self::PENDING, array( 'base' => self::base_version( $id ), 'by' => get_current_user_id(), 'at' => time(), 'data' => $data ) );
                self::log( $id, 'Submitted for review' ); $notice = 'submitted'; $notify = true;
            } else {
                // Revalidate stored pending fields against the current configuration.
                if ( 'approve' === $command ) $data['fields'] = self::validate_fields( $type, $data['fields'] );
                $update = array( 'ID' => $id, 'post_title' => $data['title'], 'post_content' => $data['content'], 'post_excerpt' => $data['excerpt'] );
                if ( 'approve' === $command ) $update['post_status'] = 'publish';
                $result = wp_update_post( wp_slash( $update ), true ); if ( is_wp_error( $result ) ) throw new RuntimeException( 'The item could not be saved.' );
                foreach ( $data['fields'] as $key => $value ) update_field( $key, $value, $id );
                if ( 'approve' === $command ) delete_post_meta( $id, self::PENDING );
                self::log( $id, 'approve' === $command ? 'Approved' : 'Saved', wp_unslash( $_POST['grid_note'] ?? '' ) ); $notice = 'approve' === $command ? 'approved' : ( 'publish' === $p->post_status ? 'updated' : 'saved' );
            }
            $wpdb->query( 'COMMIT' ); $transaction = false;
        } catch ( Throwable $e ) {
            if ( $transaction ) { global $wpdb; $wpdb->query( 'ROLLBACK' ); if ( $id ) clean_post_cache( $id ); }
            if ( $lock ) self::unlock( $lock ); self::deny( $e->getMessage() . ' Use your browser’s Back button to recover the form.', $e instanceof InvalidArgumentException ? 400 : 409 ); return;
        }
        self::unlock( $lock );
        if ( $notify ) GRID_Arendal_Editor_Extras::notify( $id );
        if ( 'publish' === get_post_status( $id ) ) do_action( 'litespeed_purge_url', get_permalink( $id ) );
        wp_safe_redirect( self::url( array( 'grid_section' => $type, 'grid_notice' => $notice ) ) ); exit;
    }
    public static function edit_link( $content ) {
        if ( GRID_Accessibility_Toolbar::editor_active() || ! is_singular() || ! in_the_loop() || ! is_main_query() || ! self::page() || ! self::can_edit( get_the_ID() ) ) return $content;
        // Personalised links must not enter any public page cache.
        if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true );
        do_action( 'litespeed_control_set_nocache', 'GRID editor shortcut' );
        return '<p class="grid-context-edit"><a href="' . esc_url( self::url( array( 'grid_section' => get_post_type(), 'grid_action' => 'edit', 'grid_item' => get_the_ID() ) ) ) . '">Edit this ' . esc_html( get_post_type_object( get_post_type() )->labels->singular_name ) . '</a></p>' . $content;
    }
}
