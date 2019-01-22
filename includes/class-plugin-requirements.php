<?php

namespace DWS_Plugin_Collections;

/**
 * Class Plugin_Requirements
 *
 * Check plugin requirements and either continue with initialization
 * or display admin notice.
 *
 * @package DWS_Plugin_Collections
 */
class Plugin_Requirements {

	/**
	 * Plugin name.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $plugin_name;

	/**
	 * Required WordPress Version
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $wp_version;

	/**
	 * Required PHP version.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $php_version;

	/**
	 * The plugin file.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $plugin_file;

	/**
	 * The installed WP verstion to compare against.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $wp_server_version;

	/**
	 * The installed PHP verstion to compare against.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $php_server_version;

	/**
	 * Construct -- Set properties, etc.
	 *
	 * @param array $args The properties array.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $args ) {
		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}
		require_once DWSPC_INC_DIR . 'class-admin-notice.php';
	}

	/**
	 * Check to see if the plugin's version requirements are met.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function plugin_requirements_met() {
		$plugin_requirements_met = $this->php_requirement_met() && $this->wp_requirement_met();
		if ( ! $plugin_requirements_met ) {
			add_action( 'admin_notices', [ $this, 'deactivate' ], 99 );
		}

		return $plugin_requirements_met;
	}

	/**
	 * Deactivate plugins.
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		if ( isset( $this->plugin_file ) ) {
			deactivate_plugins( $this->plugin_file );
		}
	}

	/**
	 * Check to see if PHP version requirement is met.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function php_requirement_met() {
		if ( $this->version_compare( $this->php_server_version, $this->php_version ) ) {
			return true;
		} else {
			add_action( 'admin_notices', [ $this, 'php_requirement_notice' ] );

			return false;
		}
	}

	/**
	 * Notice copy for when PHP versions requirmenet is not met.
	 *
	 * @since 1.0.0
	 */
	public function php_requirement_notice() {
		new Admin_Notice(
			// translators: PHP version number requirement message.
			sprintf( __( '%1$s requires PHP version %2$s+. You are running version %3$s. Please discuss upgrade options with your hosting provider. WordPress recommends PHP version 7+.', 'dwspc' ), $this->plugin_name, $this->php_version, $this->php_server_version )
		);
	}

	/**
	 * Check if the WP version requirement is met.
	 *
	 * @return boolean
	 */
	public function wp_requirement_met() {
		if ( $this->version_compare( $this->wp_server_version, $this->wp_version ) ) {
			return true;
		} else {
			add_action( 'admin_notices', [ $this, 'wp_requirement_notice' ] );

			return false;
		}
	}

	/**
	 * Copy for notice if the WP version doesn't meet the requirement.
	 *
	 * @return void
	 */
	public function wp_requirement_notice() {
		new Admin_Notice(
			// translators: WordPress version number requirement message.
			sprintf( __( '%1$s requires WP version %2$s+. You are running WordPress version %3$s. Please upgrade and reactivate.', 'dwspc' ), $this->plugin_name, $this->wp_version, $this->wp_server_version )
		);
	}

	/**
	 * Compare versions.
	 *
	 * @param string $running_version  The current version.
	 * @param string $required_version The required version.
	 *
	 * @return bool
	 */
	private function version_compare( $running_version, $required_version ) {
		return version_compare( $running_version, $required_version, '>=' );
	}

}
