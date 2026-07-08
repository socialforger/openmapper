<?php
/**
 * CLASS: OPM_Ajax_Receiver
 * Processes asynchronous requests from the client-side engine (Saving geometries, CPT linking).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class OPM_Ajax_Receiver {

    public function __construct() {
        add_action( 'wp_ajax_opm_create_map_container_ajax', array( $this, 'opm_create_map_container' ) );
        add_action( 'wp_ajax_opm_save_geometry', array( $this, 'opm_save_geometry_feature' ) );
    }

    public function opm_create_map_container() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : 'Untitled Map';
        $privacy = isset( $_POST['privacy'] ) && $_POST['privacy'] === 'publish' ? 'publish' : 'private';
        $drive_url = isset( $_POST['drive_url'] ) ? sanitize_url( wp_unslash( $_POST['drive_url'] ) ) : '';

        $map_id = wp_insert_post( array(
            'post_type'   => 'opm_user_map',
            'post_title'  => $title,
            'post_status' => $privacy,
            'post_author' => get_current_user_id()
        ) );

        if ( $map_id && ! is_wp_error( $map_id ) ) {
            if ( ! empty( $drive_url ) ) {
                update_post_meta( $map_id, 'opm_choropleth_csv_url', $drive_url );
            }
            wp_send_json_success( array( 'map_id' => $map_id ) );
        }

        wp_send_json_error();
    }

    public function opm_save_geometry_feature() {
        if ( ! is_user_logged_in() ) wp_send_json_error();

        $map_id = isset( $_POST['belongs_to_map'] ) ? intval( $_POST['belongs_to_map'] ) : 0;
        $title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : 'Feature';
        $geojson = isset( $_POST['geojson'] ) ? wp_unslash( $_POST['geojson'] ) : ''; // Validated by JS
        
        $feature_id = wp_insert_post( array(
            'post_type'   => 'opm_spatial_layer',
            'post_title'  => $title,
            'post_content'=> isset( $_POST['desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['desc'] ) ) : '',
            'post_status' => 'publish',
            'post_parent' => $map_id
        ) );

        if ( $feature_id && ! is_wp_error( $feature_id ) ) {
            update_post_meta( $feature_id, 'opm_geojson_data', $geojson );
            update_post_meta( $feature_id, 'opm_lat', sanitize_text_field( $_POST['lat'] ) );
            update_post_meta( $feature_id, 'opm_lng', sanitize_text_field( $_POST['lng'] ) );
            wp_send_json_success( array( 'feature_id' => $feature_id ) );
        }

        wp_send_json_error();
    }
}
new OPM_Ajax_Receiver();
