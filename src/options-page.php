<?php

/**
 * Admin Options Page
 *
 * Set mapbox access token and map height
 *
 * @since   1.0.0
 * @package Chrisbibby/MapBoxTrackBlock
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class MapBoxTrackBlockSettingsPage {


	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		 add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		 // This page will be under "Settings"
		add_options_page(
			'Settings Admin',
			'MB Track Block',
			'manage_options',
			'mbtb-setting-admin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
	   // Set class property
		$this->options = get_option( 'mbtb_option_name' );
		?>
<div class="wrap">
	<h1>Mapbox Track Block</h1>
	<form method="post" action="options.php">
		<?php
						// This prints out all hidden setting fields
						settings_fields( 'mbtb_option_group' );
						do_settings_sections( 'mbtb-setting-admin' );
						submit_button();
						?>
	</form>
</div>
<?php
			}

			/**
			 * Register and add settings
			 */
			public function page_init() {
	register_setting(
	'mbtb_option_group', // Option group
	'mbtb_option_name', // Option name
	array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
	'mbtb_section_mapbox', // ID
	'Mapbox Map Settings', // Title
	array( $this, 'print_section_info' ), // Callback
	'mbtb-setting-admin' // Page
		);

		add_settings_field(
	'access_token', // ID
	'Mapbox Access Token', // Title
	array( $this, 'token_callback' ), // Callback
	'mbtb-setting-admin', // Page
	'mbtb_section_mapbox' // Section
		);

		add_settings_field(
	'map_height',
	'Map Height',
	array( $this, 'height_callback' ),
	'mbtb-setting-admin',
	'mbtb_section_mapbox'
		);
			}

			/**
			 * Sanitize each setting field as needed
			 *
			 * @param array $input Contains all settings fields as array keys
			 */
			public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['access_token'] ) ) {

			$new_input['access_token'] = sanitize_text_field( $input['access_token'] );
			// Get the Response Code from the MapBox API
			$mapbox_api_code = $this->check_mapbox_api( $new_input['access_token'] );
			// Generate error/success response from API Code
			switch ( $mapbox_api_code ) {
				case 'TokenValid':
					$type    = 'updated';
					$message = 'Your MapBox access token is valid and active';
					break;
				case 'TokenMalformed':
					$type                      = 'error';
					$message                   = __( 'Invalid MapBox access token' );
					$new_input['access_token'] = '';
					break;
				case 'TokenInvalid':
					$type                      = 'error';
					$message                   = __( 'The signature for the access token does not validate' );
					$new_input['access_token'] = '';
					break;
				case 'TokenExpired';
					$type                      = 'error';
					$message                   = __( 'Your access token was temporary and has expired.' );
					$new_input['access_token'] = '';
					break;
				case 'TokenRevoked':
					$type                      = 'error';
					$message                   = __( 'token\'s authorization has been deleted.' );
					$new_input['access_token'] = '';
					break;
				default:
					$type                      = 'error';
					$message                   = __( 'Please enter a valid Mapbox access token' );
					$new_input['access_token'] = '';
					}
			add_settings_error(
				'access_token_error',
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}

		if ( isset( $input['map_height'] ) ) {
			$new_input['map_height'] = sanitize_text_field( $input['map_height'] );
			if ( ! preg_match_all( '/[0-9]*\.?[0-9]+(px|%)$/i', $new_input['map_height'] ) ) {
				$type    = 'error';
				$message = __( 'Invalid height. Please use px or %' );
				add_settings_error( 'map_height_error', esc_attr( 'settings_updated' ), $message, $type );
			}
		}

		return $new_input;
			}

			public function check_mapbox_api( $access_token ) {
		$query    = urlencode( $access_token );
		$response = wp_remote_get( 'https://api.mapbox.com/tokens/v2?access_token=' . $query );
		$body     = json_decode( wp_remote_retrieve_body( $response ), true );
		return $body['code'];
			}
			/**
			 * Print the Section text
			 */
			public function print_section_info() {
	?>
<div style="width:50%;">
	<p>
		To get started, you'll need to sign up for an account at <a href="https://www.mapbox.com/"
			target="_blank">https://www.mapbox.com/</a>
		and grab an access token.
	</p>
	<p>
		Make sure to keep your token secure by restricting your public access token to your domain only.
		See <a href="https://docs.mapbox.com/help/account/tokens/#domain-restrictions" target="_blank">This section of
			the documentation</a> for more info.
	</p>
</div>
<?php
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function token_callback() {
	printf(
		'<input type="text" id="access_token" name="mbtb_option_name[access_token]" value="%s" />',
		isset( $this->options['access_token'] ) ? esc_attr( $this->options['access_token'] ) : ''
		);
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function height_callback() {
	printf(
		'<input type="text" id="map_height" name="mbtb_option_name[map_height]" value="%s" />',
		isset( $this->options['map_height'] ) ? esc_attr( $this->options['map_height'] ) : ''
		);
		}
	}

	if ( is_admin() ) {
	$mbtb_settings_page = new MapBoxTrackBlockSettingsPage();
	}
