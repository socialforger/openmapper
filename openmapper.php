<?php
/**
 * Plugin Name: OpenMapper
 * Plugin URI:  https://github.com/openmapper
 * Description: Decentralized GIS mapping engine for civic crowdsourcing.
 * Version:     1.0.0
 * Author:      Socialforger
 * Text Domain: openmapper
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Global Constants
 */
define( 'OPM_VERSION', '1.0.0' );
define( 'OPM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Class OPM_Core_Bootstrap
 * Main initialization class.
 */
class OPM_Core_Bootstrap {

    public function __construct() {
        $this->opm_load_dependencies();
        
        add_action( 'plugins_loaded', array( $this, 'opm_load_textdomain' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'opm_enqueue_frontend_assets' ) );
        add_action( 'tgmpa_register', array( $this, 'opm_register_required_plugins' ) );
    }

    /**
     * Loads the plugin architecture files.
     */
    private function opm_load_dependencies() {
        // Core Logic (Database & Setup)
        require_once OPM_PLUGIN_DIR . 'core/class-opm-pods-deployer.php';
        require_once OPM_PLUGIN_DIR . 'core/class-opm-admin-wizard.php';
        require_once OPM_PLUGIN_DIR . 'core/class-opm-ajax-receiver.php';

        // Frontend Logic (UI & Shortcodes)
        require_once OPM_PLUGIN_DIR . 'frontend/class-opm-user-dashboard.php';
        require_once OPM_PLUGIN_DIR . 'frontend/class-opm-frontend-wizard.php';
        require_once OPM_PLUGIN_DIR . 'frontend/class-opm-crowd-canvas.php';
    }

    /**
     * Initializes translations.
     */
    public function opm_load_textdomain() {
        load_plugin_textdomain( 'openmapper', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Registers and enqueues client-side assets (CSS & JS).
     */
    public function opm_enqueue_frontend_assets() {
        // Isolated UI CSS
        wp_enqueue_style( 
            'opm-core-style', 
            OPM_PLUGIN_URL . 'assets/css/opm-style.css', 
            array(), 
            OPM_VERSION 
        );

        // Client-side parser for 7 GIS formats
        wp_enqueue_script( 
            'opm-multiformat-parser', 
            OPM_PLUGIN_URL . 'assets/js/opm-multiformat-parser.js', 
            array(), 
            OPM_VERSION, 
            true 
        );

        // Advanced layers (Live CSV, Font markers)
        wp_enqueue_script( 
            'opm-advanced-layers', 
            OPM_PLUGIN_URL . 'assets/js/opm-advanced-layers.js', 
            array(), 
            OPM_VERSION, 
            true 
        );

        // Pass PHP variables to JS context
        $opm_settings = array(
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'opm_ajax_nonce' ),
            'defaultLat' => get_option( 'opm_global_settings' )['default_lat'] ?? '41.902',
            'defaultLng' => get_option( 'opm_global_settings' )['default_lng'] ?? '12.496',
        );

        wp_localize_script( 'opm-multiformat-parser', 'opmSettings', $opm_settings );
    }

    /**
     * Registers the required plugin bundle using the global TGMPA fallback.
     */
    public function opm_register_required_plugins() {
        // Check if TGMPA is active globally. If not, fail gracefully without crashing.
        if ( ! function_exists( 'tgmpa' ) ) {
            return;
        }

        $plugins = array(
            array(
                'name'     => 'Pods - Custom Content Types and Fields',
                'slug'     => 'pods',
                'required' => true,
            ),
            array(
                'name'     => 'Leaflet Map',
                'slug'     => 'leaflet-map',
                'required' => true,
            ),
            array(
                'name'     => 'Extensions for Leaflet Map',
                'slug'     => 'extensions-for-leaflet-map',
                'required' => true,
            ),
            array(
                'name'     => 'Filter Everything — WordPress Filters',
                'slug'     => 'filter-everything',
                'required' => true,
            ),
        );

        $config = array(
            'id'           => 'openmapper-tgmpa',
            'menu'         => 'opm-install-plugins',
            'has_notices'  => true,
            'dismissable'  => false,
            'is_automatic' => true,
        );
        
        tgmpa( $plugins, $config );
    }
}

// Boot up the engine.
new OPM_Core_Bootstrap();
