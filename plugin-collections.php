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

// Load the requirements class.
require_once DWSPC_INC_DIR . 'class-plugin-requirements.php';

function dwspc_check_plugin_requirements() {

	// Check plugin requirements.
	$requirements = new Plugin_Requirements( [
		'plugin_name'        => 'Plugin Collections',
		'php_version'        => '5.6',
		'wp_version'         => '4.9',
		'plugin_file'        => __FILE__,
		'php_server_version' => phpversion(),
		'wp_server_version'  => get_bloginfo( 'version' ),
	] );

	if ( $requirements->plugin_requirements_met() ) {

		add_action( 'plugins_loaded', function () {
			require_once DWSPC_INC_DIR . 'functions.php';
			require_once DWSPC_INC_DIR . 'class-plugin-collections-base.php';
			require_once DWSPC_INC_DIR . 'class-data-structures.php';
			require_once DWSPC_INC_DIR . 'class-post-types.php';
			require_once DWSPC_INC_DIR . 'class-bulk-plugin-actions.php';

			/**
			 * Get things started.
			 */
			( new Plugin_Collections_Base() )->init();

		}, 99 );

	}
}

dwspc_check_plugin_requirements();
