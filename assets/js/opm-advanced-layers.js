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
