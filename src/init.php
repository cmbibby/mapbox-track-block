<?php

/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package Chrisbibby/MapBoxTrackBlock
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @uses {mapbox-style} Styles for Mapbox Block.
 * @uses {mapbox-gl-js} MapBox GL JS Library/
 * @since 1.0.0
 */
function mapbox_track_block_block_assets() { // phpcs:ignore
	// Register block styles for both frontend + backend.
	wp_enqueue_style(
		'mapbox_track_block-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		array( 'wp-editor' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);

	wp_enqueue_style(
		'mapbox_track_block-mapbox-style', // Handle.
		'https://api.tiles.mapbox.com/mapbox-gl-js/v0.48.0/mapbox-gl.css',
		array( 'wp-editor', 'mapbox_track_block-style-css' ) // Dependency to include the CSS after it.
		// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: filemtime — Gets file modification time.
	);

	// Mapbox script.
	wp_enqueue_script(
		'mapbox_track_block-mapbox-gl-js',
		'https://api.tiles.mapbox.com/mapbox-gl-js/v0.48.0/mapbox-gl.js',
		array(),  // Dependencies
		'0.48.0',
		true // Enqueue the script in the footer.
	);
}

function mapbox_track_block_backend_assets() {
  // Register block editor script for backend.
	wp_enqueue_script(
		'mapbox_track_block-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
		null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);
	wp_localize_script(
		'mapbox_track_block-block-js',
		'mbtbAdminOptions',
		array(
			'accessToken' => get_option( 'mbtb_option_name' )['access_token'],
			'optionsPage' => admin_url( 'options-general.php?page=mbtb-setting-admin' ),
		)
	);

	// Register block editor styles for backend.
	wp_enqueue_style(
		'mapbox_track_block-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-editor' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	);
}

function mapbox_track_block_frontend_assets() {
	wp_enqueue_script(
		'mapbox_track_block-frontend',
		plugins_url( 'dist/mapbox-track-block.js', dirname( __FILE__ ) ),
		array( 'mapbox_track_block-mapbox-gl-js' ),  // Dependencies
		'1.0.1',
		true // Enqueue the script in the footer.
	);

	wp_localize_script(
		'mapbox_track_block-frontend',
		'mbtbOptions',
		array(
			'accessToken' => get_option( 'mbtb_option_name' )['access_token'],
		)
	);
}
function mapbox_track_block_register() {
	/**
	 * Register Gutenberg block on server-side.
	 *
	 * Register the block on server-side to ensure that the block
	 * scripts and styles for both frontend and backend are
	 * enqueued when the editor loads.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @since 1.16.0
	 */
	register_block_type(
		'chrisbibby/block-mapbox-track-block',
		array(
			'attributes'      => array(
				'trackColour'    => array(
					'type'    => 'string',
					'default' => '#888888',
				),
				'trackThickness' => array(
					'type'    => 'number',
					'default' => '5',
				),
				'mapStyle'       => array(
					'type'    => 'string',
					'default' => 'mapbox/streets-v11',
				),
				'trackName'      => array(
					'type'    => 'string',
					'default' => 'My Awesome Track',
				),
			),
			'render_callback' => 'mapbox_track_block_render_cb',
		)
	);
}

// Hook: Block assets.
add_action( 'init', 'mapbox_track_block_register' );
add_action( 'enqueue_block_assets', 'mapbox_track_block_block_assets' );
add_action( 'enqueue_block_editor_assets', 'mapbox_track_block_backend_assets' );
add_action( 'wp_enqueue_scripts', 'mapbox_track_block_frontend_assets' );



function mapbox_track_block_render_cb( $attributes ) {
	if ( get_option( 'mbtb_option_name' )['access_token'] && $attributes['trackCoords'] ) {
		$markup  = '<script type="text/javascript">var trackCoords = {geojson : ' . $attributes['trackCoords'] . '};</script>';
		$markup .= '<div id="mapbox-map" style="width:100%;height:' . get_option( 'mbtb_option_name' )['map_height'] . ';"';
		$markup .= 'data-lng="' . esc_attr( $attributes['lng'] ) . '" ';
		$markup .= 'data-lat="' . esc_attr( $attributes['lat'] ) . '" ';
		$markup .= 'data-zoom="' . esc_attr( $attributes['zoom'] ) . ' "';
		$markup .= 'data-track-colour="' . esc_attr( $attributes['trackColour'] ) . '" ';
		$markup .= 'data-track-thickness="' . esc_attr( $attributes['trackThickness'] ) . '" ';
		$markup .= 'data-map-style="' . esc_attr( $attributes['mapStyle'] ) . '" ';
		$markup .= '/>';
		$markup .= '<p><b>' . esc_html( $attributes['trackName'] ) . '</b></p>';
	} else {
		$markup = '<div><h6>Can\'t display mapbox track block</h6></div>';
	}
	return $markup;
}

function add_gpx_mime() {
   $mime_types['geojson'] = 'text/plain'; // Adding .geojson extension
	$mime_types['gpx']    = 'text/xml';
	return $mime_types;
}

add_filter( 'upload_mimes', 'add_gpx_mime', 1, 1 );
