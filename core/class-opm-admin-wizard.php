<?php
/**
 * CLASS: OPM_Admin_Wizard
 * Handles the backend settings page for global configurations (API Keys, Default Coordinates).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class OPM_Admin_Wizard {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'opm_add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'opm_register_settings' ) );
    }

    public function opm_add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=opm_user_map',
            __( 'OpenMapper Settings', 'openmapper' ),
            __( 'Settings', 'openmapper' ),
            'manage_options',
            'opm-settings',
            array( $this, 'opm_render_settings_page' )
        );
    }

    public function opm_register_settings() {
        register_setting( 'opm_settings_group', 'opm_global_settings' );
    }

    public function opm_render_settings_page() {
        $options = get_option( 'opm_global_settings' );
        $zornade_token = $options['zornade_token'] ?? '';
        $default_lat = $options['default_lat'] ?? '41.902';
        $default_lng = $options['default_lng'] ?? '12.496';
        ?>
        <div class="wrap">
            <h1>⚙️ <?php esc_html_e( 'OpenMapper Engine Settings', 'openmapper' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'opm_settings_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Zornade API Token', 'openmapper' ); ?></th>
                        <td>
                            <input type="text" name="opm_global_settings[zornade_token]" value="<?php echo esc_attr( $zornade_token ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( 'Required for automatic background extraction of cadastral and environmental risk data (ISPRA/ISTAT).', 'openmapper' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Default Map Latitude', 'openmapper' ); ?></th>
                        <td><input type="text" name="opm_global_settings[default_lat]" value="<?php echo esc_attr( $default_lat ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Default Map Longitude', 'openmapper' ); ?></th>
                        <td><input type="text" name="opm_global_settings[default_lng]" value="<?php echo esc_attr( $default_lng ); ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
new OPM_Admin_Wizard();
