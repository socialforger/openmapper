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

    private function opm_build_html( $map_id ) {
        ?>
        <div class="opm-wrapper">
            <div style="margin-bottom:10px; display:flex; gap:10px;">
                <input type="text" id="opm_geocoder_val" placeholder="Search address..." style="flex:1; padding:8px;">
                <button type="button" onclick="opmSearchAddress()">🔍</button>
            </div>
            <div id="opm-leaflet-map" style="height:400px; width:100%; border:1px solid #ccc; z-index:1;"></div>
        </div>
        <script>
        let opmMap = null;
        function opmTriggerLeafletInit() {
            if(opmMap !== null) return;
            opmMap = L.map('opm-leaflet-map').setView([opmSettings.defaultLat, opmSettings.defaultLng], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(opmMap);
            if(opmMap.pm) {
                opmMap.pm.addControls({ position: 'topleft' });
                opmMap.on('pm:create', e => {
                    const feat = e.layer.toGeoJSON();
                    alert('Feature drawn! Ready to be saved to DB as CPT.');
                    // In a production environment, open a modal to input Title/Desc and send via AJAX
                });
            }
        }
        function opmSearchAddress() {
            const q = document.getElementById('opm_geocoder_val').value;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}`)
            .then(r=>r.json()).then(res=>{
                if(res.length > 0) {
                    opmMap.setView([res[0].lat, res[0].lon], 15);
                    L.marker([res[0].lat, res[0].lon]).addTo(opmMap).bindPopup(res[0].display_name).openPopup();
                }
            });
        }
        document.addEventListener('DOMContentLoaded', () => { if(document.getElementById('opm-leaflet-map')) opmTriggerLeafletInit(); });
        </script>
        <?php
    }
}
new OPM_Crowd_Canvas();
