<?php
/**
 * CLASS: OPM_Frontend_Wizard
 * Renders the 3-step project creation wizard.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class OPM_Frontend_Wizard {
    public static function opm_render_wizard_markup() {
        ?>
        <div class="opm-wizard-box" style="border:1px solid #e2e8f0; padding:20px; border-radius:6px; background:#f8fafc;">
            <div id="opm-step-1">
                <h3>1. <?php esc_html_e( 'Project Settings', 'openmapper' ); ?></h3>
                <input type="text" id="opm_wiz_title" placeholder="Map Title" style="width:100%; margin-bottom:10px; padding:8px;">
                <select id="opm_wiz_privacy" style="width:100%; padding:8px; margin-bottom:10px;">
                    <option value="private"><?php esc_html_e( 'Private', 'openmapper' ); ?></option>
                    <option value="publish"><?php esc_html_e( 'Public', 'openmapper' ); ?></option>
                </select>
                <button onclick="opmSwitchStep(2)" style="padding:8px 15px;"><?php esc_html_e( 'Next', 'openmapper' ); ?></button>
            </div>

            <div id="opm-step-2" style="display:none;">
                <h3>2. <?php esc_html_e( 'Import Data (Client-Side)', 'openmapper' ); ?></h3>
                <p style="font-size:12px;"><?php esc_html_e( 'Upload KML, GPX, GeoJSON, CSV. The browser parses it.', 'openmapper' ); ?></p>
                <input type="file" id="opm_wiz_file" style="margin-bottom:15px; display:block;">
                <input type="text" id="opm_wiz_drive" placeholder="Google Sheets CSV URL (Optional)" style="width:100%; padding:8px; margin-bottom:15px;">
                <button onclick="opmProcessWizard()" style="background:#16a34a; color:#fff; padding:8px 15px; border:none; border-radius:4px;"><?php esc_html_e( 'Deploy Map', 'openmapper' ); ?></button>
                <p id="opm_wiz_log" style="font-size:12px; margin-top:10px; font-weight:bold;"></p>
            </div>

            <div id="opm-step-3" style="display:none;">
                <h3>3. <?php esc_html_e( 'Draw Features', 'openmapper' ); ?></h3>
                <div id="opm-canvas-injector"></div>
            </div>
        </div>

        <script>
        let opmCurrentMapId = 0;
        function opmSwitchStep(step) {
            document.getElementById('opm-step-1').style.display = 'none';
            document.getElementById('opm-step-2').style.display = 'none';
            document.getElementById('opm-step-3').style.display = 'none';
            document.getElementById('opm-step-' + step).style.display = 'block';
        }
        function opmProcessWizard() {
            const title = document.getElementById('opm_wiz_title').value;
            const log = document.getElementById('opm_wiz_log');
            if(!title) { alert('Title required'); return; }
            log.innerText = 'Creating database containers...';
            
            const p = new URLSearchParams({
                action: 'opm_create_map_container_ajax',
                nonce: opmSettings.nonce,
                title: title,
                privacy: document.getElementById('opm_wiz_privacy').value,
                drive_url: document.getElementById('opm_wiz_drive').value
            });

            fetch(opmSettings.ajaxUrl, { method: 'POST', body: p })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    opmCurrentMapId = res.data.map_id;
                    const file = document.getElementById('opm_wiz_file').files[0];
                    if(file && typeof opmExecuteClientFileParser === 'function') {
                        opmExecuteClientFileParser(file, opmCurrentMapId, log);
                    } else {
                        opmLoadCanvas();
                    }
                }
            });
        }
        function opmLoadCanvas() {
            document.getElementById('opm-canvas-injector').innerHTML = '<p>Loading engine...</p>';
            fetch(opmSettings.ajaxUrl + '?action=opm_get_draw_canvas_html&map_id=' + opmCurrentMapId)
            .then(r => r.text())
            .then(html => {
                document.getElementById('opm-canvas-injector').innerHTML = html;
                if(typeof opmTriggerLeafletInit === 'function') opmTriggerLeafletInit();
                opmSwitchStep(3);
            });
        }
        </script>
        <?php
    }
}
