<?php
/**
 * CLASS: OPM_User_Dashboard
 * Generates the [openmapper_dashboard] shortcode for private user management.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class OPM_User_Dashboard {

    public function __construct() {
        add_shortcode( 'openmapper_dashboard', array( $this, 'opm_render_dashboard' ) );
    }

    public function opm_render_dashboard() {
        if ( ! is_user_logged_in() ) return '<p>' . esc_html__( 'Please log in.', 'openmapper' ) . '</p>';

        ob_start();
        ?>
        <div class="opm-wrapper" style="max-width:1100px; margin:0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ccc; padding-bottom:10px; margin-bottom:20px;">
                <h2><?php esc_html_e( 'My Cartographic Projects', 'openmapper' ); ?></h2>
                <button onclick="document.getElementById('opm-wizard-container').style.display='block'" style="background:#2563eb; color:#fff; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">+ <?php esc_html_e( 'New Map', 'openmapper' ); ?></button>
            </div>
            
            <div id="opm-wizard-container" style="display:none; margin-bottom:30px;">
                <?php if ( class_exists( 'OPM_Frontend_Wizard' ) ) OPM_Frontend_Wizard::opm_render_wizard_markup(); ?>
            </div>

            <div class="opm-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">
                <?php
                $maps = new WP_Query( array( 'post_type' => 'opm_user_map', 'author' => get_current_user_id(), 'post_status' => array('publish','private') ) );
                if ( $maps->have_posts() ) {
                    while ( $maps->have_posts() ) { $maps->the_post(); ?>
                        <div style="border:1px solid #eee; padding:15px; border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                            <h4 style="margin:0 0 10px 0;"><?php the_title(); ?></h4>
                            <p style="font-size:12px; color:#666;"><?php echo get_post_status() === 'publish' ? '🌐 Public' : '🔒 Private'; ?></p>
                            <a href="<?php the_permalink(); ?>" style="display:block; text-align:center; background:#f1f5f9; padding:6px; text-decoration:none; color:#333; border-radius:4px; margin-top:10px; font-size:13px;"><?php esc_html_e( 'View', 'openmapper' ); ?></a>
                        </div>
                    <?php } wp_reset_postdata();
                } else {
                    echo '<p>' . esc_html__( 'No projects found.', 'openmapper' ) . '</p>';
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
new OPM_User_Dashboard();
