<?php
/**
 * Plugin Name: OpenMapper Core
 * Description: Piattaforma GIS unificata e collaborativa decentralizzata. Include onboarding automatico via TGMPA, deployment relazionale programmatico di Pods, importatore client-side multiformato (7 formati) e plancia My Maps indipendente dal tema.
 * Version: 5.5.0
 * Author: Socialforger
 * License: GPL2
 * Text Domain: openmapper
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Definizione costanti di percorso e URL
define( 'OPM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Inclusione dei componenti logici strutturati
 */
require_once OPM_PLUGIN_DIR . 'class-tgm-plugin-activation.php';
require_once OPM_PLUGIN_DIR . 'core/class-opm-pods-deployer.php';
require_once OPM_PLUGIN_DIR . 'core/class-opm-admin-wizard.php';
require_once OPM_PLUGIN_DIR . 'core/class-opm-ajax-receiver.php';
require_once OPM_PLUGIN_DIR . 'frontend/class-opm-user-dashboard.php';
require_once OPM_PLUGIN_DIR . 'frontend/class-opm-frontend-wizard.php';
require_once OPM_PLUGIN_DIR . 'frontend/class-opm-crowd-canvas.php';

/**
 * Caricamento internazionalizzazione (ex wpmapper.pot adattato)[cite: 1]
 */
add_action( 'plugins_loaded', 'opm_core_load_textdomain' );
function opm_core_load_textdomain() {
    load_plugin_textdomain( 'openmapper', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Registrazione controllata dei requisiti software del bundle (TGMPA)
 */
add_action( 'tgmpa_register', 'opm_core_register_bundle' );
function opm_core_register_bundle() {
    $plugins = array(
        array( 'name' => 'Pods - Custom Content Types and Fields', 'slug' => 'pods', 'required' => true ),
        array( 'name' => 'Leaflet Map', 'slug' => 'leaflet-map', 'required' => true ),
        array( 'name' => 'Extensions for Leaflet Map', 'slug' => 'extensions-for-leaflet-map', 'required' => true ),
        array( 'name' => 'Filter Everything — WordPress Filters', 'slug' => 'filter-everything', 'required' => true ),
    );
    $config = array(
        'id'           => 'openmapper-tgmpa',
        'menu'         => 'opm-install-plugins',
        'has_notices'  => true,
        'dismissable'  => false,
        'is_automatic' => true,
    );
    if ( function_exists( 'tgmpa' ) ) tgmpa( $plugins, $config );
}

/**
 * Enqueue isolato di librerie GIS e asset grafici ereditati da WPMapper[cite: 1]
 */
add_action( 'wp_enqueue_scripts', 'opm_core_enqueue_assets' );
function opm_core_enqueue_assets() {
    global $post;
    if ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'openmapper_dashboard' ) || has_shortcode( $post->post_content, 'openmapper_draw' ) ) ) {
        // CDN Cartografiche standard di base
        wp_enqueue_style( 'opm-leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4' );
        wp_enqueue_script( 'opm-leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true );
        wp_enqueue_style( 'opm-geoman-css', 'https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css', array(), 'latest' );
        wp_enqueue_script( 'opm-geoman-js', 'https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js', array('opm-leaflet-js'), 'latest', true );
        wp_enqueue_script( 'opm-togeojson', 'https://unpkg.com/@mapbox/togeojson@0.16.0/togeojson.js', array(), '0.16.0', true );
        
        // Asset custom unificati e stili isolati per evitare interferenze con i temi[cite: 1]
        wp_enqueue_style( 'opm-wpm-style', OPM_PLUGIN_URL . 'assets/css/wpm-style.css', array(), '1.0.0' );[cite: 1]
        wp_enqueue_script( 'opm-wpm-advanced-layers', OPM_PLUGIN_URL . 'assets/js/wpm-advanced-layers.js', array('opm-leaflet-js'), '1.0.0', true );[cite: 1]
        wp_enqueue_script( 'opm-multiformat-parser', OPM_PLUGIN_URL . 'assets/js/opm-multiformat-parser.js', array(), '1.0.0', true );

        // Localizzazione delle impostazioni globali e dei token per i file JS (Client-Side)
        $settings = get_option('opm_global_settings', array('zornadeToken' => '', 'default_lat' => '41.902', 'default_lng' => '12.496'));
        wp_localize_script( 'opm-wpm-advanced-layers', 'opmSettings', array([cite: 1]
            'zornadeToken' => $settings['zornadeToken'],
            'defaultLat'   => $settings['default_lat'],
            'defaultLng'   => $settings['default_lng'],
            'ajaxUrl'      => admin_url('admin-ajax.php')
        ) );
    }
}

/**
 * Hook di attivazione per il reindirizzamento automatico al Wizard amministrativo
 */
register_activation_hook( __FILE__, 'opm_core_activation_redirect' );
function opm_core_activation_redirect() {
    set_transient( 'opm_setup_wizard_redirect', true, 30 );
}
