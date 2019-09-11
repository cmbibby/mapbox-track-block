import { gpx } from "@tmcw/togeojson";
export default class Map extends React.Component {
  constructor() {
    super();
    this.state = {
      myTrackCoords: []
    };
  }
  render() {
    return <div id="mapbox-map" />;
  }

  componentDidMount() {
    mapboxgl.accessToken = mbtbAdminOptions.accessToken;

    let mapPoint = [this.props.lng, this.props.lat];

    let trackUrl = this.props.trackUrl;
    this.map = new mapboxgl.Map({
      container: "mapbox-map",
      style: "mapbox://styles/" + this.props.mapStyle,
      center: mapPoint,
      zoom: this.props.zoom
    });

    if (trackUrl) {
      this.map.on("style.load", () => {
        this.getGeoJSONfromGPX(trackUrl);
      });
    }
    this.addMapEvents();
  }

  componentDidUpdate(prevProps) {
    if (this.props.zoom !== prevProps.zoom) {
      this.map.flyTo({
        zoom: this.props.zoom,
        speed: 2
      });
    }
    if (this.props.trackUrl !== prevProps.trackUrl) {
      if (typeof prevProps.trackUrl !== "undefined") {
        this.map.removeLayer("route");
        this.map.removeSource("track");
      }
      this.getGeoJSONfromGPX(this.props.trackUrl);
    }

    if (this.props.mapStyle !== prevProps.mapStyle) {
      this.map.setStyle("mapbox://styles/" + this.props.mapStyle);
      this.map.on("style.load", () => {
        this.getGeoJSONfromGPX(this.props.trackUrl);
      });
    }

    if (this.props.trackColour !== prevProps.trackColour) {
      this.map.setPaintProperty("route", "line-color", this.props.trackColour);
    }

    if (this.props.trackThickness !== prevProps.trackThickness) {
      this.map.setPaintProperty(
        "route",
        "line-width",
        this.props.trackThickness
      );
    }
  }

  fitToBounds() {
    let geojson = this.state.myTrackCoords;
    let coordinates = geojson.features[0].geometry.coordinates;
    let newCoordinates = [];
    coordinates.forEach(item => {
      newCoordinates.push([item[0], item[1]]);
    });
    let bounds = newCoordinates.reduce((bounds, coord) => {
      return bounds.extend(coord);
    }, new mapboxgl.LngLatBounds(newCoordinates[0], newCoordinates[0]));
    this.trackCoords = newCoordinates;
    this.map.fitBounds(bounds, {
      padding: 40
    });
  }

  getGeoJSONfromGPX(url) {
    fetch(url)
      .then(res => res.text())
      .then(str => new DOMParser().parseFromString(str, "text/xml"))
      .then(data => gpx(data))
      .then(geojson => {
        this.setState({ myTrackCoords: geojson });
        let coordinates = geojson.features[0].geometry.coordinates;
        let newCoordinates = [];
        coordinates.forEach(item => {
          newCoordinates.push([item[0], item[1]]);
        });
        let bounds = newCoordinates.reduce((bounds, coord) => {
          return bounds.extend(coord);
        }, new mapboxgl.LngLatBounds(newCoordinates[0], newCoordinates[0]));
        this.trackCoords = newCoordinates;
        this.map.fitBounds(bounds, {
          padding: 40
        });

        this.map.addSource("track", {
          type: "geojson",
          data: geojson
        });
        this.map.addLayer({
          id: "route",
          type: "line",
          source: "track",
          layout: {
            "line-join": "round",
            "line-cap": "round"
          },
          paint: {
            "line-color": this.props.trackColour,
            "line-width": this.props.trackThickness
          }
        });
      })
      .catch(err => console.error(err));
  }

  addTrackSource(geoJSON) {
    this.map.addSource("track", {
      type: "geojson",
      data: geoJSON
    });
  }

  addTrack() {
    this.map.addLayer({
      id: "route",
      type: "line",
      source: "track",
      layout: {
        "line-join": "round",
        "line-cap": "round"
      },
      paint: {
        "line-color": this.props.trackColour,
        "line-width": this.props.trackThickness
      }
    });
  }

  addMapEvents() {
    this.map.on("dragend", () => {
      this.props.onChange({
        lng: this.map.getCenter().lng,
        lat: this.map.getCenter().lat,
        zoom: this.map.getZoom(),
        trackCoords: this.state.myTrackCoords
      });
    });
    this.map.on("zoomend", () => {
      this.props.onChange({
        lng: this.map.getCenter().lng,
        lat: this.map.getCenter().lat,
        zoom: this.map.getZoom(),
        trackCoords: this.state.myTrackCoords
      });
    });
  }
}
