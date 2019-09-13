/**
 * BLOCK: mapbox-track-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import "./editor.scss";
import "./style.scss";
import Map from "./map";

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { InspectorControls, MediaUpload } = wp.editor;
const {
  RangeControl,
  Button,
  SelectControl,
  ColorPicker,
  TextControl
} = wp.components;
const { Fragment } = wp.element;
/**
 * Register: aa Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType("chrisbibby/block-mapbox-track-block", {
  // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
  title: __("Mapbox Track Block"), // Block title.
  icon: "location-alt", // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
  category: "common", // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
  keywords: [__("mapbox-track-block"), __("mapbox gpx track")],
  supports: { multiple: false },
  attributes: {
    lat: {
      type: "number",
      default: 0
    },
    lng: {
      type: "number",
      default: 0
    },
    zoom: {
      type: "number",
      default: 1
    },
    mapCenter: {
      type: "array"
    },
    trackUrl: {
      type: "string"
    },
    mapStyle: {
      type: "string",
      default: "mapbox/streets-v11"
    },
    trackColour: {
      type: "string",
      default: "#888888"
    },
    trackThickness: {
      type: "number",
      default: 5
    },
    trackName: {
      type: "String",
      default: "My Awesome Track"
    },
    trackCoords: {
      type: "string"
    }
  },

  /**
   * The edit function describes the structure of your block in the context of the editor.
   * This represents what the editor will render when the block is used.
   *
   * The "edit" property must be a valid function.
   *
   * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
   *
   * @param {Object} props Props.
   * @returns {Mixed} JSX Component.
   */
  edit: props => {
    const {
      className,
      attributes: {
        lat,
        lng,
        zoom,
        trackUrl,
        mapStyle,
        trackColour,
        trackThickness,
        trackName,
        trackCoords
      },
      setAttributes
    } = props;
    const updateAttributes = value => {
      setAttributes({
        lat: Number(value.lat),
        lng: Number(value.lng),
        zoom: Number(value.zoom),
        trackCoords: JSON.stringify(value.trackCoords)
      });
    };
    if (!mbtbAdminOptions.accessToken) {
      return (
        <div className="mapbox-block-token">
          <h2>Mapbox Track Block</h2>
          <p>
            You'll need to head over and sign up for an account at Mapbox to be
            able to use this block.
          </p>
          <a
            href={mbtbAdminOptions.optionsPage}
            className="mapbox-block-token-cta"
          >
            Connect to Mapbox
          </a>
        </div>
      );
    }
    if (mbtbAdminOptions.accessToken) {
      return (
        <Fragment>
          <InspectorControls>
            <TextControl
              label="Track Title"
              value={trackName}
              onChange={value => setAttributes({ trackName: value })}
            />
            <MediaUpload
              onSelect={media => setAttributes({ trackUrl: media.url })}
              render={({ open }) => (
                <Button isDefault onClick={open}>
                  Upload/Select Track File
                </Button>
              )}
            />
            <RangeControl
              label={__("Zoom Level")}
              value={zoom}
              onChange={value => setAttributes({ zoom: Number(value) })}
              min={1}
              max={22}
            />

            <SelectControl
              label="Map Style"
              value={mapStyle}
              options={[
                { value: "mapbox/streets-v11", label: "Streets" },
                { value: "mapbox/light-v10", label: "Light" },
                { value: "mapbox/dark-v10", label: "Dark" },
                { value: "mapbox/outdoors-v11", label: "Outdoors" },
                { value: "mapbox/satellite-v9", label: "Satellite" }
              ]}
              onChange={value => setAttributes({ mapStyle: value })}
            />
            <ColorPicker
              disableAlpha
              color={trackColour}
              onChangeComplete={value =>
                setAttributes({ trackColour: value.hex })
              }
            />
            <RangeControl
              label={__("Track Thickness")}
              value={trackThickness}
              onChange={value =>
                setAttributes({ trackThickness: Number(value) })
              }
              min={1}
              max={30}
            />
          </InspectorControls>
          <div className={className}>
            <Map
              lat={lat}
              lng={lng}
              zoom={zoom}
              trackUrl={trackUrl}
              mapStyle={mapStyle}
              trackColour={trackColour}
              trackThickness={trackThickness}
              trackCoords={trackCoords}
              onChange={value => updateAttributes(value)}
            />
            <p>
              <b>{trackName}</b>
            </p>
          </div>
        </Fragment>
      );
    }
  },

  /**
   * The save function defines the way in which the different attributes should be combined
   * into the final markup, which is then serialized by Gutenberg into post_content.
   *
   * The "save" property must be specified and must be a valid function.
   *
   * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
   *
   * @param {Object} props Props.
   * @returns {Mixed} JSX Frontend HTML.
   */
  save: () => {
    return null;
  }
});
