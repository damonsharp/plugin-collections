<?php
/**
 * Plugin Name:     Plugin Collections
 * Plugin URI:      https://plugincollections.com
 * Description:     WordPress plugin that allows you to create collections of plugins to activate together, allowing
 * you to easily switch between groups of activated plugins for testing, etc.
 * Author:          Damon Sharp
 * Author URI:      https://damonsharp.me
 * Text Domain:     dwspc
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Plugin_Collections
 */

namespace DWS_Plugin_Collections;

/**
 * Setup constants.
 */
if ( ! defined( 'DWSPC_DIR' ) ) {
	define( 'DWSPC_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

if ( ! defined( 'DWSPC_DIR_URL' ) ) {
	define( 'DWSPC_DIR_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if ( ! defined( 'DWSPC_INC_DIR' ) ) {
	define( 'DWSPC_INC_DIR', trailingslashit( plugin_dir_path( __FILE__ ) . 'includes' ) );
}

if ( ! defined( 'DWSPC_PHP_REQUIREMENT' ) ) {
	define( 'DWSPC_PHP_REQUIREMENT', '5.6' );
}

if ( ! defined( 'DWSPC_WP_REQUIREMENT' ) ) {
	define( 'DWSPC_WP_REQUIREMENT', '4.9' );
}

// Autoload the plugin's classes.
require_once 'vendor/autoload.php';

function check_plugin_requirements() {

	// Check plugin requirements.
	$requirements = new Plugin_Requirements( [
		'plugin_name'        => 'Plugin Collections',
		'php_version'        => '7.0',
		'wp_version'         => '4.9',
		'plugin_file'        => __FILE__,
		'php_server_version' => phpversion(),
		'wp_server_version'  => get_bloginfo( 'version' ),
	] );

	if ( $requirements->plugin_requirements_met() ) {

		add_action( 'after_setup_theme', function () {
			/**
			 * Get things started.
			 */
			( new Plugin_Collections_Base() )->init();
		} );

	}
}

// Let's kick it.
check_plugin_requirements();
