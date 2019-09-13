<?php

/**
 * Plugin Name: Mapbox Track Block
 * Plugin URI: https://chrisbibby.com.au
 * Description: Display a GPX track on a MapBox Map
 * Author: Chris Bibby
 * Author URI: https://chrisbibby.com.au
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Chrisbibby/MapboxTrackBlock
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/options-page.php';
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';

/**
 * Register activation hook and only activate plugin if we have v5 or greater
 */

function mbtb_activate() {
  $wp_version = get_bloginfo( 'version' );
	if ( ! version_compare( $wp_version, '5.0', '>=' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'This plugin requires the block editor in WordPress v5.0 and above, please update to the latest WordPress version.' . version_compare( $wp_version, '5.0', '>=' ) );
	}
}

register_activation_hook( __FILE__, 'mbtb_activate' );

/**
 * Resgister uninstall hook and delete the options we created.
 */

function mbtb_uninstall() {
	 delete_option( 'mbtb_option_name' );
}

register_uninstall_hook( __FILE__, 'mbtb_deactivate' );
