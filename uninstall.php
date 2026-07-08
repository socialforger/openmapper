<?php
/**
 * Fired when the plugin is uninstalled.
 * Ensures no orphan options are left in the wp_options table.
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete global settings option
delete_option( 'opm_global_settings' );

// Note: We intentionally do NOT delete the opm_user_map or opm_spatial_layer 
// Custom Post Types here, to prevent accidental catastrophic data loss for civic associations. 
// If they reinstall the plugin, their mapped data will still be safe in the database.
