<?php
if ( ! defined( 'ABSPATH' ) ) exit;
final class GRID_Arendal_Editor_Extras {
    public static function boot() {
        foreach ( array( 'grid_csv_preview' => 'csv_preview', 'grid_csv_import' => 'csv_import', 'grid_private_upload' => 'upload', 'grid_private_download' => 'download' ) as $action => $method ) {
            add_action( 'admin_post_' . $action, array( __CLASS__, $method ) );
            add_action( 'admin_post_nopriv_' . $action, array( 'GRID_Arendal_Workspace', 'deny' ) );
        }
    }
    public static function notify( $id ) {
        if ( ! GRID_Arendal_Core::ready( 'notifications' ) ) return;
        $p = get_post( $id ); if ( ! $p ) return; $o = get_post_type_object( $p->post_type ); $recipients = array();
        foreach ( GRID_Arendal_Core::settings()['reviewers'] as $uid ) {
            $user = get_userdata( $uid );
            if ( $user && user_can( $user, 'edit_post', $id ) && user_can( $user, $o->cap->publish_posts ) && is_email( $user->user_email ) ) $recipients[] = $user->user_email;
        }
        if ( ! $recipients ) return;
        $url = GRID_Arendal_Workspace::url( array( 'grid_section' => $p->post_type, 'grid_action' => 'edit', 'grid_item' => $id ) );
        // Send separately: reviewers' email addresses are not disclosed to each other.
        foreach ( array_unique( $recipients ) as $to ) wp_mail( $to, '[' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . '] Content awaiting review', "An authorised editor submitted changes for review.\n\n" . $url . "\n\nSign in to review the submission. No published changes are applied until approval." );
    }
    public static function csv_form( $type ) {
        $token = GRID_Arendal_Workspace::input( 'grid_csv' ); $preview = $token ? get_transient( 'grid_csv_' . get_current_user_id() . '_' . $token ) : false;
        echo '<details class="grid-import"><summary>Import new drafts from CSV</summary><p>UTF-8 CSV, up to 200 rows and 1 MB. Required column: <code>title</code>. Optional: <code>content</code>, <code>excerpt</code> and configured ACF field keys. Complex fields may use JSON values. Imports always create new drafts.</p>';
        if ( $preview && $preview['type'] === $type ) {
            echo '<h4>Review import: ' . count( $preview['rows'] ) . ' new drafts</h4><ul>';
            foreach ( $preview['rows'] as $row ) echo '<li>' . esc_html( $row['title'] ) . '</li>';
            echo '</ul><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="grid_csv_import"><input type="hidden" name="grid_token" value="' . esc_attr( $token ) . '">'; wp_nonce_field( 'grid_csv_import_' . $token ); echo '<button>Import these new drafts</button></form>';
        } else {
            echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="grid_csv_preview"><input type="hidden" name="grid_type" value="' . esc_attr( $type ) . '">'; wp_nonce_field( 'grid_csv_preview_' . $type ); echo '<label>CSV file <input type="file" name="grid_csv_file" accept=".csv,text/csv" required></label><button>Validate and preview</button></form>';
        }
        echo '</details>';
    }
    private static function csv_access() {
        if ( ! GRID_Arendal_Core::ready( 'csv' ) || ! current_user_can( 'manage_options' ) || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) GRID_Arendal_Workspace::deny();
        GRID_Arendal_Workspace::private_headers();
    }
    public static function parse_csv( $stream, $type ) {
        $header = fgetcsv( $stream, 0, ',', '"', '\\' );
        if ( ! is_array( $header ) || count( $header ) > 100 ) throw new InvalidArgumentException( 'Invalid CSV header.' );
        $header[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $header[0] ); $header = array_map( 'trim', $header );
        if ( count( array_unique( $header ) ) !== count( $header ) || ! in_array( 'title', $header, true ) ) throw new InvalidArgumentException( 'Use unique column names and a title column.' );
        $fields = GRID_Arendal_Workspace::fields( $type );
        foreach ( $header as $h ) if ( ! in_array( $h, array( 'title', 'content', 'excerpt' ), true ) && ! isset( $fields[$h] ) ) throw new InvalidArgumentException( 'Unknown or unconfigured column: ' . $h );
        $rows = array();
        while ( false !== ( $row = fgetcsv( $stream, 0, ',', '"', '\\' ) ) ) {
            if ( array( null ) === $row ) continue;
            if ( count( $row ) !== count( $header ) || count( $rows ) >= 200 ) throw new InvalidArgumentException( 'Use consistent columns and at most 200 rows.' );
            foreach ( $row as $v ) if ( wp_check_invalid_utf8( $v ) !== $v ) throw new InvalidArgumentException( 'Save the CSV as UTF-8.' );
            $values = array_combine( $header, $row ); $title = GRID_Arendal_Core::text( $values['title'], 250 ); if ( '' === $title ) throw new InvalidArgumentException( 'Every row needs a title.' );
            $acf = array();
            foreach ( $fields as $key => $field ) if ( isset( $values[$key] ) ) {
                $v = $values[$key];
                if ( in_array( $field['type'], array( 'repeater', 'group', 'flexible_content', 'link', 'gallery', 'relationship', 'checkbox' ), true ) || ! empty( $field['multiple'] ) ) { $v = json_decode( $v, true ); if ( JSON_ERROR_NONE !== json_last_error() ) throw new InvalidArgumentException( 'Complex fields must contain valid JSON.' ); }
                $acf[$key] = $v;
            }
            $rows[] = array( 'title' => $title, 'content' => wp_kses_post( $values['content'] ?? '' ), 'excerpt' => wp_kses_post( $values['excerpt'] ?? '' ), 'fields' => GRID_Arendal_Workspace::validate_fields( $type, $acf ) );
        }
        if ( ! $rows ) throw new InvalidArgumentException( 'The CSV contains no records.' );
        return $rows;
    }
    public static function csv_preview() {
        self::csv_access(); $type = isset( $_POST['grid_type'] ) && is_string( $_POST['grid_type'] ) ? sanitize_key( $_POST['grid_type'] ) : '';
        if ( ! GRID_Arendal_Workspace::can_create( $type ) ) GRID_Arendal_Workspace::deny();
        check_admin_referer( 'grid_csv_preview_' . $type );
        $file = $_FILES['grid_csv_file'] ?? array();
        if ( ! isset( $file['tmp_name'], $file['size'], $file['error'] ) || ! is_string( $file['tmp_name'] ) || UPLOAD_ERR_OK !== $file['error'] || $file['size'] > 1048576 || ! is_uploaded_file( $file['tmp_name'] ) ) GRID_Arendal_Workspace::deny( 'Upload a CSV no larger than 1 MB.', 400 );
        $stream = fopen( $file['tmp_name'], 'r' );
        try { $rows = self::parse_csv( $stream, $type ); } catch ( Throwable $e ) { fclose( $stream ); GRID_Arendal_Workspace::deny( $e->getMessage(), 400 ); return; }
        fclose( $stream ); $token = wp_generate_uuid4(); set_transient( 'grid_csv_' . get_current_user_id() . '_' . $token, array( 'type' => $type, 'rows' => $rows ), 900 );
        wp_safe_redirect( GRID_Arendal_Workspace::url( array( 'grid_section' => $type, 'grid_csv' => $token ) ) ); exit;
    }
    public static function csv_import() {
        self::csv_access(); $token = isset( $_POST['grid_token'] ) && is_string( $_POST['grid_token'] ) ? $_POST['grid_token'] : '';
        if ( ! preg_match( '/^[a-f0-9-]{36}$/', $token ) ) GRID_Arendal_Workspace::deny( 'Invalid import token.', 400 );
        check_admin_referer( 'grid_csv_import_' . $token ); $key = 'grid_csv_' . get_current_user_id() . '_' . $token;
        $lock = ''; $ids = array(); $transaction = false;
        try {
            $lock = GRID_Arendal_Workspace::lock( 'csv:' . get_current_user_id() . ':' . $token );
            $data = get_transient( $key ); if ( ! $data || ! GRID_Arendal_Workspace::can_create( $data['type'] ) ) throw new RuntimeException( 'The preview expired or permissions changed. Preview the file again.' );
            foreach ( $data['rows'] as &$row ) $row['fields'] = GRID_Arendal_Workspace::validate_fields( $data['type'], $row['fields'] ); unset( $row );
            global $wpdb; $wpdb->query( 'START TRANSACTION' ); $transaction = true;
            foreach ( $data['rows'] as $row ) {
                $id = wp_insert_post( wp_slash( array( 'post_type' => $data['type'], 'post_status' => 'draft', 'post_author' => get_current_user_id(), 'post_title' => $row['title'], 'post_content' => $row['content'], 'post_excerpt' => $row['excerpt'] ) ), true );
                if ( is_wp_error( $id ) ) throw new RuntimeException( 'Import failed; drafts from this batch were rolled back.' );
                $ids[] = $id; foreach ( $row['fields'] as $field => $value ) update_field( $field, $value, $id ); GRID_Arendal_Workspace::log( $id, 'Imported as draft' );
            }
            $wpdb->query( 'COMMIT' ); $transaction = false; delete_transient( $key );
        } catch ( Throwable $e ) {
            if ( $transaction ) { global $wpdb; $wpdb->query( 'ROLLBACK' ); foreach ( $ids as $id ) clean_post_cache( $id ); }
            if ( $lock ) GRID_Arendal_Workspace::unlock( $lock ); GRID_Arendal_Workspace::deny( $e->getMessage(), 409 ); return;
        }
        GRID_Arendal_Workspace::unlock( $lock ); wp_safe_redirect( GRID_Arendal_Workspace::url( array( 'grid_section' => $data['type'], 'grid_notice' => 'imported' ) ) ); exit;
    }
    public static function private_root() {
        $root = GRID_Arendal_Core::settings()['private_root']; $real = $root ? realpath( $root ) : false; $public = realpath( ABSPATH );
        return $real && $public && ! is_link( $root ) && is_dir( $real ) && ! GRID_Arendal_Core::within( $real, $public ) && ! GRID_Arendal_Core::within( $public, $real ) ? $real : false;
    }
    public static function file_records( $id ) { $files = get_post_meta( $id, '_grid_private_files', true ); return is_array( $files ) ? $files : array(); }
    public static function files_form( $id ) {
        if ( ! self::private_root() ) { if ( current_user_can( 'manage_options' ) ) echo '<p>Configure private storage outside the web root to use private files.</p>'; return; }
        echo '<details><summary>Private resource files</summary><p>These files are served only to accounts that can edit this item; they are not added to the public media library.</p><ul>';
        foreach ( self::file_records( $id ) as $token => $file ) {
            $url = wp_nonce_url( add_query_arg( array( 'action' => 'grid_private_download', 'grid_id' => $id, 'grid_file' => $token ), admin_url( 'admin-post.php' ) ), 'grid_private_download_' . $id . '_' . $token );
            echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $file['name'] ) . '</a> (' . esc_html( size_format( $file['size'] ) ) . ')</li>';
        }
        echo '</ul><form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="grid_private_upload"><input type="hidden" name="grid_id" value="' . esc_attr( $id ) . '">'; wp_nonce_field( 'grid_private_upload_' . $id );
        echo '<label>Private file (maximum 10 MB) <input type="file" name="grid_private_file" required></label><button>Upload private file</button></form></details>';
    }
    public static function upload() {
        if ( ! GRID_Arendal_Core::ready( 'private_files' ) || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) GRID_Arendal_Workspace::deny();
        GRID_Arendal_Workspace::private_headers(); $id = isset( $_POST['grid_id'] ) && is_scalar( $_POST['grid_id'] ) ? absint( $_POST['grid_id'] ) : 0;
        if ( ! GRID_Arendal_Workspace::can_edit( $id ) || ! current_user_can( 'upload_files' ) ) GRID_Arendal_Workspace::deny();
        check_admin_referer( 'grid_private_upload_' . $id ); $root = self::private_root(); if ( ! $root || ! is_writable( $root ) ) GRID_Arendal_Workspace::deny( 'Private storage is not configured.', 409 );
        $file = $_FILES['grid_private_file'] ?? array();
        if ( ! isset( $file['tmp_name'], $file['name'], $file['size'], $file['error'] ) || ! is_string( $file['tmp_name'] ) || ! is_string( $file['name'] ) || UPLOAD_ERR_OK !== $file['error'] || $file['size'] > 10485760 || ! is_uploaded_file( $file['tmp_name'] ) ) GRID_Arendal_Workspace::deny( 'Choose a file no larger than 10 MB.', 400 );
        $allowed = array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'txt' => 'text/plain', 'csv' => 'text/csv', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'zip' => 'application/zip' );
        $name = sanitize_file_name( $file['name'] ); $check = wp_check_filetype_and_ext( $file['tmp_name'], $name, $allowed );
        if ( empty( $check['ext'] ) || empty( $check['type'] ) ) GRID_Arendal_Workspace::deny( 'This file format is not allowed.', 400 );
        $token = wp_generate_uuid4(); $base = $token . '.' . $check['ext']; $target = $root . DIRECTORY_SEPARATOR . $base; $lock = '';
        try {
            $lock = GRID_Arendal_Workspace::lock( 'post:' . $id ); $files = self::file_records( $id );
            if ( count( $files ) >= 100 || file_exists( $target ) || ! move_uploaded_file( $file['tmp_name'], $target ) ) throw new RuntimeException( 'The private file could not be stored.' );
            chmod( $target, 0600 );
            $files[$token] = array( 'name' => $name, 'path' => $base, 'size' => filesize( $target ), 'sha256' => hash_file( 'sha256', $target ) );
            if ( ! update_post_meta( $id, '_grid_private_files', $files ) ) { unlink( $target ); throw new RuntimeException( 'The private file record could not be saved.' ); }
            GRID_Arendal_Workspace::log( $id, 'Private file uploaded' );
        } catch ( Throwable $e ) { if ( $lock ) GRID_Arendal_Workspace::unlock( $lock ); GRID_Arendal_Workspace::deny( $e->getMessage(), 409 ); return; }
        GRID_Arendal_Workspace::unlock( $lock ); wp_safe_redirect( GRID_Arendal_Workspace::url( array( 'grid_section' => get_post_type( $id ), 'grid_action' => 'edit', 'grid_item' => $id, 'grid_notice' => 'file' ) ) ); exit;
    }
    public static function download() {
        GRID_Arendal_Workspace::private_headers(); $id = absint( GRID_Arendal_Workspace::input( 'grid_id' ) ); $token = GRID_Arendal_Workspace::input( 'grid_file' );
        if ( ! GRID_Arendal_Core::ready( 'private_files' ) || ! GRID_Arendal_Workspace::can_edit( $id ) ) GRID_Arendal_Workspace::deny();
        check_admin_referer( 'grid_private_download_' . $id . '_' . $token ); $root = self::private_root(); $files = self::file_records( $id ); $file = $files[$token] ?? null;
        $path = $root && $file ? realpath( $root . DIRECTORY_SEPARATOR . $file['path'] ) : false;
        if ( ! $path || ! GRID_Arendal_Core::within( $path, $root ) || ! is_file( $path ) || is_link( $root . DIRECTORY_SEPARATOR . $file['path'] ) ) GRID_Arendal_Workspace::deny( 'File unavailable.', 404 );
        header( 'Content-Type: application/octet-stream' ); header( 'X-Content-Type-Options: nosniff' ); header( 'Content-Length: ' . filesize( $path ) );
        header( "Content-Disposition: attachment; filename=\"" . str_replace( array( '"', "\r", "\n" ), '', sanitize_file_name( $file['name'] ) ) . "\"; filename*=UTF-8''" . rawurlencode( $file['name'] ) );
        while ( ob_get_level() ) ob_end_clean(); readfile( $path ); exit;
    }
    public static function map_assets() {
        wp_enqueue_style( 'grid-leaflet', plugins_url( '../../assets/leaflet/leaflet.css', __FILE__ ), array(), '1.9.4' );
        wp_enqueue_script( 'grid-leaflet', plugins_url( '../../assets/leaflet/leaflet.js', __FILE__ ), array(), '1.9.4', true );
    }
    public static function map_form( $type ) {
        $section = GRID_Arendal_Workspace::section( $type ); $fields = GRID_Arendal_Workspace::fields( $type ); $lat = $section['latitude']; $lng = $section['longitude'];
        if ( ! isset( $fields[$lat], $fields[$lng] ) || ! in_array( $fields[$lat]['type'], array( 'number', 'text' ), true ) || ! in_array( $fields[$lng]['type'], array( 'number', 'text' ), true ) ) return;
        echo '<details class="grid-map-editor"><summary>Choose coordinates on a map</summary><p>Click the map or drag the marker. Coordinates update the configured fields; no location is assumed for a blank record.</p><div class="grid-coordinate-map" data-latitude="' . esc_attr( $lat ) . '" data-longitude="' . esc_attr( $lng ) . '" role="region" aria-label="Coordinate picker map"></div><p>Map tiles are provided by OpenStreetMap and contact its servers when opened. You can also enter coordinates in the fields above.</p></details>';
    }
}
