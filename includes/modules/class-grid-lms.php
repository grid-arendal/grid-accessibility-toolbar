<?php
if ( ! defined( 'ABSPATH' ) ) exit;
final class GRID_Arendal_LMS {
    public static function boot() { add_shortcode( 'grid_courses', array( __CLASS__, 'courses' ) ); add_action( 'wp_enqueue_scripts', function() { if ( is_singular() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'grid_courses' ) ) wp_enqueue_style( 'grid-course-cards', plugins_url( '../../assets/workspace.css', __FILE__ ), array(), GRID_Accessibility_Toolbar::VERSION ); } ); }
    public static function courses( $atts ) {
        $a = shortcode_atts( array( 'ids' => '', 'limit' => 6 ), $atts, 'grid_courses' ); $ids = array_filter( array_map( 'absint', explode( ',', is_string( $a['ids'] ) ? $a['ids'] : '' ) ) );
        $q = array( 'post_type' => 'lp_course', 'post_status' => 'publish', 'posts_per_page' => max( 1, min( 24, absint( $a['limit'] ) ) ), 'no_found_rows' => true ); if ( $ids ) { $q['post__in'] = array_slice( $ids, 0, 100 ); $q['orderby'] = 'post__in'; }
        $posts = get_posts( $q ); $out = '<div class="grid-course-cards">';
        foreach ( $posts as $p ) {
            $url = get_permalink( $p ); $out .= '<article><h3><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $p ) ) . '</a></h3>' . get_the_post_thumbnail( $p, 'medium', array( 'loading' => 'lazy' ) ) . '<p>' . esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $p ) ), 35 ) ) . '</p><a href="' . esc_url( $url ) . '">View course<span class="screen-reader-text">: ' . esc_html( get_the_title( $p ) ) . '</span></a>';
            if ( is_user_logged_in() && current_user_can( 'edit_post', $p->ID ) ) $out .= '<p><a href="' . esc_url( get_edit_post_link( $p->ID, 'raw' ) ) . '">Edit in the native course editor</a></p>';
            $out .= '</article>';
        }
        return $out . ( $posts ? '' : '<p>No published courses are available.</p>' ) . '</div>';
    }
}
