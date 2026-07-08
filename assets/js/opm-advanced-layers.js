/**
 * Uses Leaflet L.divIcon to generate font markers without images.
 */
function opmCreateFontMarker(iconClass, hexColor) {
    return L.divIcon({
        className: 'opm-custom-marker-wrapper',
        html: `<div style="color:${hexColor||'#2563eb'};"><i class="${iconClass||'fas fa-map-marker-alt'}"></i></div>`,
        iconSize: [30, 30], iconAnchor: [15, 30], popupAnchor: [0, -30]
    });
}

/**
 * Used by Extensions for Leaflet Map to stylize existing DB markers.
 */
function opmStyleExistingMarker(layer, props) {
    if(props.icon_class && props.icon_color && typeof layer.setIcon === 'function') {
        layer.setIcon(opmCreateFontMarker(props.icon_class, props.icon_color));
    }
}

/**
 * Editor Leaflet/Geoman + geocoder Nominatim usati dal canvas di disegno
 * (shortcode [openmapper_draw] e wizard di creazione mappa).
 *
 * Queste funzioni erano prima definite in un <script> inline dentro
 * class-opm-crowd-canvas.php: quando quel markup viene iniettato via
 * .innerHTML (flusso wizard AJAX) lo script non veniva mai eseguito dal
 * browser, quindi la mappa non si inizializzava mai in quel flusso.
 * Vivendo qui, in un file caricato con wp_enqueue_script, sono sempre
 * disponibili globalmente, sia che il markup arrivi via rendering
 * normale della pagina sia che arrivi via AJAX.
 */
let opmMap = null;

function opmTriggerLeafletInit() {
    if (opmMap !== null) return;

    const mapEl = document.getElementById('opm-leaflet-map');
    if (!mapEl) return;

    if (typeof L === 'undefined') {
        console.warn('OpenMapper: Leaflet (L) non è ancora disponibile in pagina, impossibile inizializzare la mappa.');
        return;
    }

    opmMap = L.map('opm-leaflet-map').setView([opmSettings.defaultLat, opmSettings.defaultLng], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(opmMap);

    if (opmMap.pm) {
        opmMap.pm.addControls({ position: 'topleft' });
        opmMap.on('pm:create', e => {
            const feat = e.layer.toGeoJSON();
            alert('Feature drawn! Ready to be saved to DB as CPT.');
            // In un ambiente di produzione, apri qui una modale per Titolo/Descrizione e invia via AJAX
        });
    }
}

function opmSearchAddress() {
    const input = document.getElementById('opm_geocoder_val');
    if (!input || !opmMap) return;

    const q = input.value;
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(res => {
            if (res.length > 0) {
                opmMap.setView([res[0].lat, res[0].lon], 15);
                L.marker([res[0].lat, res[0].lon]).addTo(opmMap).bindPopup(res[0].display_name).openPopup();
            }
        });
}

// Auto-init per l'uso diretto dello shortcode [openmapper_draw] (markup già
// presente al caricamento della pagina). Nel flusso wizard via AJAX l'init
// viene invocato esplicitamente da OPM_Frontend_Wizard::opmLoadCanvas()
// subito dopo l'inserimento del markup nel DOM.
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('opm-leaflet-map')) opmTriggerLeafletInit();
});
