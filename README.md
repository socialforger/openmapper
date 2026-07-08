# OpenMapper

**Version:** 1.0.0  
**Author:** Socialforger  
**License:** GPLv2 or later  

OpenMapper is a decentralized GIS mapping engine designed for civic crowdsourcing, grassroots committees, and territorial intelligence on WordPress.

## Core Features
* **Theme Independent:** Works seamlessly on any theme (Gutenberg, Elementor, Divi) via universal shortcodes.
* **Client-Side Processing:** Parses KML, GPX, CSV, and GeoJSON files directly in the user's browser, preventing server memory overloads.
* **Live Choropleths:** Generates dynamic heat maps by reading real-time data from public Google Sheets.
* **Graphic Font Markers:** Leverages vector icons (FontAwesome/Dashicons) via `L.divIcon` to create ultra-lightweight, dynamic pins.
* **Auto-Configuration:** Automatically deploys Taxonomies, Relationships, and Custom Post Types (CPTs) through a silent integration with Pods.

## Required Dependencies
OpenMapper relies on a specific bundle of standard WordPress plugins to operate. The system will prompt you to install them automatically upon activation (via the TGMPA standard):

* **Pods - Custom Content Types and Fields:** The database engine. Handles the creation of CPTs (Maps, Layers) and Taxonomies (Regions, Cities, Categories) seamlessly.
* **Leaflet Map:** The core mapping library foundation for rendering the geographical interface.
* **Extensions for Leaflet Map:** Unlocks native vector (GeoJSON) parsing and built-in marker clustering logic, preventing map overload (Overplotting).
* **Filter Everything — WordPress Filters:** The AJAX-powered engine used to search the public map archive and seamlessly toggle specific pin categories on the active map without reloading the page.

## Installation
1. Upload the `openmapper` folder to the `/wp-content/plugins/` directory on your server, or upload the zipped package directly from the WordPress dashboard (Plugins -> Add New).
2. Activate the plugin.
3. Follow the prompt at the top of your WordPress dashboard to **Begin installing plugins** and complete the bulk installation of the required dependencies.

## Available Shortcodes

* `[openmapper_dashboard]`
  Generates the private dashboard for registered users. From here, citizens can create new maps, launch the 3-step initialization wizard, and manage their territorial features.
  
* `[openmapper_draw map_id="123"]`
  Renders the GIS drawing canvas using Leaflet and Geoman, including the Nominatim geocoding search bar. If placed on a public page, it ensures that users are authenticated before allowing data persistence to the database.

## Initial Configuration
After activation, navigate to **OpenMapper -> Settings** in the WordPress admin menu to:
1. Input your **Zornade API Token** (required for background extraction of cadastral and environmental risk data, such as ISPRA/ISTAT).
2. Set the default geographical coordinates for the map's center (e.g., Latitude and Longitude of your city or country).
