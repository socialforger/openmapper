<?php
/**
 * CLASS: OPM_Crowd_Canvas
 * Outputs the Leaflet/Geoman editor and the Nominatim Geocoder.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class OPM_Crowd_Canvas {
    public function __construct() {
        add_shortcode( 'openmapper_draw', array( $this, 'opm_draw_shortcode' ) );
        add_action( 'wp_ajax_opm_get_draw_canvas_html', array( $this, 'opm_ajax_canvas' ) );
    }

    public function opm_draw_shortcode( $atts ) {
        $a = shortcode_atts( array( 'map_id' => 0 ), $atts );
        ob_start(); $this->opm_build_html( $a['map_id'] ); return ob_get_clean();
    }
    public function opm_ajax_canvas() {
        $this->opm_build_html( intval( $_GET['map_id'] ?? 0 ) );
        wp_die();
    }

    /**
     * Nota di debugging: questo markup viene usato in due modi diversi:
     *  1) direttamente dallo shortcode [openmapper_draw], quindi fa parte
     *     dell'HTML caricato normalmente dal browser;
     *  2) iniettato via AJAX in .innerHTML da OPM_Frontend_Wizard::opmLoadCanvas().
     *
     * Nel caso (2) qualunque <script> incluso qui NON verrebbe mai eseguito
     * (i browser non eseguono gli script inseriti via innerHTML). Per questo
     * la logica JS (opmTriggerLeafletInit, opmSearchAddress, l'auto-init al
     * DOMContentLoaded) è stata spostata nel file già enqueued
     * assets/js/opm-advanced-layers.js, che è sempre caricato in pagina a
     * prescindere da come questo markup viene inserito nel DOM. Qui restano
     * solo markup e attributi (onclick) che richiamano quelle funzioni globali.
     */
    private function opm_build_html( $map_id ) {
        ?>
        <div class="opm-wrapper" data-opm-map-id="<?php echo esc_attr( $map_id ); ?>">
            <div style="margin-bottom:10px; display:flex; gap:10px;">
                <input type="text" id="opm_geocoder_val" placeholder="Search address..." style="flex:1; padding:8px;">
                <button type="button" onclick="opmSearchAddress()">🔍</button>
            </div>
            <div id="opm-leaflet-map" style="height:400px; width:100%; border:1px solid #ccc; z-index:1;"></div>
        </div>
        <?php
    }
}
new OPM_Crowd_Canvas();
