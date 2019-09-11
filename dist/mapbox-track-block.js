if (typeof trackCoords !== "undefined") {
  var mapboxMap = document.getElementById("mapbox-map"),
    mapLng = mapboxMap.getAttribute("data-lng"),
    mapLat = mapboxMap.getAttribute("data-lat"),
    mapZoom = mapboxMap.getAttribute("data-zoom"),
    trackCoords = trackCoords.geojson,
    //mapTrackUrl = mapboxMap.getAttribute("data-maptrackurl"),

    trackColour = mapboxMap.getAttribute("data-track-colour"),
    trackThickness = mapboxMap.getAttribute("data-track-thickness"),
    mapStyle = mapboxMap.getAttribute("data-map-style");

  mapboxgl.accessToken = mbtbOptions.accessToken;
  var mapCenter = [mapLng, mapLat];

  var mapCenter = [mapLng, mapLat];

  var map = new mapboxgl.Map({
    container: "mapbox-map",
    style: "mapbox://styles/" + mapStyle,
    center: mapCenter,
    zoom: mapZoom
  });

  map.on("load", function() {
    console.log("map loaded");
    map.addSource("track", {
      type: "geojson",
      data: trackCoords
    });
    map.addLayer({
      id: "route",
      type: "line",
      source: "track",
      layout: {
        "line-join": "round",
        "line-cap": "round"
      },
      paint: {
        "line-color": trackColour,
        "line-width": Number(trackThickness)
      }
    });
  });
}
//var marker = new mapboxgl.Marker().setLngLat(mapCenter).addTo(map);
