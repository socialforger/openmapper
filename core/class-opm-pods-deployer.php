<?php
/**
 * CLASS: OPM_Pods_Deployer
 * Automates the deployment of the relational database (Custom Post Types & Taxonomies).
 * Uses native WordPress architecture for maximum stability, allowing Pods to extend them seamlessly.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OPM_Pods_Deployer {

    public function __construct() {
        // Hook into 'init' to register CPTs and Taxonomies early
        add_action( 'init', array( $this, 'opm_register_database_structures' ), 10 );
    }

    /**
     * Registers all the foundational data structures for OpenMapper.
     */
    public function opm_register_database_structures() {
        
        // ====================================================================
        // 1. TAXONOMIES (Registered first so they can be attached to CPTs)
        // ====================================================================

        // A. Regions (Regioni)
        register_taxonomy( 'opm_region', array( 'opm_spatial_layer' ), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'          => __( 'Regions', 'openmapper' ),
                'singular_name' => __( 'Region', 'openmapper' ),
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'opm-region' ),
        ));

        // B. Cities (Comuni)
        register_taxonomy( 'opm_city', array( 'opm_spatial_layer' ), array(
            'hierarchical'      => false,
            'labels'            => array(
                'name'          => __( 'Cities', 'openmapper' ),
                'singular_name' => __( 'City', 'openmapper' ),
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'opm-city' ),
        ));

        // C. Categories (Categorie Vertenze - Pods will add Icon Class & Color here)
        register_taxonomy( 'opm_category', array( 'opm_spatial_layer', 'opm_user_map' ), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'          => __( 'Map Categories', 'openmapper' ),
                'singular_name' => __( 'Category', 'openmapper' ),
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'opm-category' ),
        ));


        // ====================================================================
        // 2. CUSTOM POST TYPES (The core data containers)
        // ====================================================================

        // A. User Maps (The project containers)
        register_post_type( 'opm_user_map', array(
            'labels'              => array(
                'name'               => __( 'Cartographic Maps', 'openmapper' ),
                'singular_name'      => __( 'Map', 'openmapper' ),
                'menu_name'          => __( 'OpenMapper', 'openmapper' ),
                'add_new'            => __( 'Add New Map', 'openmapper' ),
                'add_new_item'       => __( 'Add New Cartographic Map', 'openmapper' ),
            ),
            'public'              => true,
            'has_archive'         => true,
            'menu_icon'           => 'dashicons-location-alt',
            'menu_position'       => 30,
            'supports'            => array( 'title', 'editor', 'author', 'thumbnail' ),
            'rewrite'             => array( 'slug' => 'civic-map' ),
            'show_in_rest'        => true, // Enables Gutenberg compatibility
        ));

        // B. Spatial Layers (The individual pins/polygons)
        register_post_type( 'opm_spatial_layer', array(
            'labels'              => array(
                'name'               => __( 'Spatial Features', 'openmapper' ),
                'singular_name'      => __( 'Feature', 'openmapper' ),
                'add_new'            => __( 'Add New Feature', 'openmapper' ),
            ),
            'public'              => true,
            'has_archive'         => false, // Features are explored via maps, not typical archives
            'show_in_menu'        => 'edit.php?post_type=opm_user_map', // Nested under Maps menu
            'supports'            => array( 'title', 'editor', 'author' ),
            'rewrite'             => array( 'slug' => 'spatial-feature' ),
            'show_in_rest'        => true,
        ));

    }
}

// Instantiate the database deployer
new OPM_Pods_Deployer();
