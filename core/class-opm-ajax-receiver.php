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
        // check_ajax_referer() interrompe l'esecuzione (die) se il nonce manca o non è valido.
        check_ajax_referer( 'opm_ajax_nonce', 'nonce' );

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
        // check_ajax_referer() interrompe l'esecuzione (die) se il nonce manca o non è valido.
        check_ajax_referer( 'opm_ajax_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) wp_send_json_error();

        $map_id  = isset( $_POST['belongs_to_map'] ) ? intval( $_POST['belongs_to_map'] ) : 0;
        $title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : 'Feature';
        $geojson_raw = isset( $_POST['geojson'] ) ? wp_unslash( $_POST['geojson'] ) : '';

        // Valida che il payload sia JSON valido prima di salvarlo.
        $decoded = json_decode( $geojson_raw, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( array( 'message' => 'Invalid GeoJSON payload' ) );
        }

        // Il client (opm-multiformat-parser.js) invia solo il campo "geojson": lat/lng
        // NON arrivano piu' come campi POST separati, quindi li estraiamo qui dalla
        // geometria stessa. Manteniamo comunque un fallback su $_POST['lat']/['lng']
        // per compatibilita' con eventuali chiamate future che li inviino esplicitamente.
        $lat = '';
        $lng = '';

        if ( isset( $decoded['geometry']['coordinates'] ) && is_array( $decoded['geometry']['coordinates'] ) ) {
            $coords = $decoded['geometry']['coordinates'];
            // GeoJSON usa l'ordine [lng, lat].
            if ( isset( $coords[0] ) && is_numeric( $coords[0] ) ) {
                $lng = sanitize_text_field( (string) $coords[0] );
            }
            if ( isset( $coords[1] ) && is_numeric( $coords[1] ) ) {
                $lat = sanitize_text_field( (string) $coords[1] );
            }
        }

        if ( isset( $_POST['lat'] ) && $_POST['lat'] !== '' ) {
            $lat = sanitize_text_field( wp_unslash( $_POST['lat'] ) );
        }
        if ( isset( $_POST['lng'] ) && $_POST['lng'] !== '' ) {
            $lng = sanitize_text_field( wp_unslash( $_POST['lng'] ) );
        }

        $feature_id = wp_insert_post( array(
            'post_type'    => 'opm_spatial_layer',
            'post_title'   => $title,
            'post_content' => isset( $_POST['desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['desc'] ) ) : '',
            'post_status'  => 'publish',
            'post_parent'  => $map_id
        ) );

        if ( $feature_id && ! is_wp_error( $feature_id ) ) {
            update_post_meta( $feature_id, 'opm_geojson_data', $geojson_raw );
            update_post_meta( $feature_id, 'opm_lat', $lat );
            update_post_meta( $feature_id, 'opm_lng', $lng );
            wp_send_json_success( array( 'feature_id' => $feature_id ) );
        }

        wp_send_json_error();
    }
}
new OPM_Ajax_Receiver();
