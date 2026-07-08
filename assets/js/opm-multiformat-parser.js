function opmExecuteClientFileParser(file, mapId, logEl) {
    const ext = file.name.split('.').pop().toLowerCase();
    const r = new FileReader();
    r.onload = e => {
        let geojson = null;
        try {
            if(ext==='json'||ext==='geojson') geojson = JSON.parse(e.target.result);
            if(ext==='csv') geojson = opmParseBasicCSV(e.target.result);
            // GPX/KML logic relies on toGeoJSON library
            
            if(geojson && geojson.features) {
                logEl.innerText = `Found ${geojson.features.length} points. Saving to database...`;
                opmInjectFeatures(geojson.features, mapId, logEl);
            }
        } catch(err) { logEl.innerText = 'Parse error: ' + err.message; }
    };
    r.readAsText(file);
}

function opmParseBasicCSV(csv) {
    const lines = csv.split('\n');
    const h = lines[0].toLowerCase().split(',');
    const latI = h.findIndex(x=>x.includes('lat')), lngI = h.findIndex(x=>x.includes('lon')||x.includes('lng'));
    let f = [];
    for(let i=1; i<lines.length; i++) {
        if(!lines[i].trim()) continue;
        const col = lines[i].split(',');
        f.push({ type: 'Feature', geometry: { type: 'Point', coordinates: [parseFloat(col[lngI]), parseFloat(col[latI])] }, properties:{} });
    }
    return { type: 'FeatureCollection', features: f };
}

function opmInjectFeatures(features, mapId, logEl) {
    let done = 0;
    features.forEach((feat, i) => {
        setTimeout(() => {
            const p = new URLSearchParams({ action: 'opm_save_geometry', nonce: opmSettings.nonce, title: `Imported #${i+1}`, geojson: JSON.stringify(feat), belongs_to_map: mapId });
            fetch(opmSettings.ajaxUrl, { method: 'POST', body: p }).then(() => {
                done++;
                if(done === features.length) {
                    logEl.innerText = 'All imported! Loading map...';
                    if(typeof opmLoadCanvas === 'function') opmLoadCanvas();
                }
            });
        }, i * 50);
    });
}
