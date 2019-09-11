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
