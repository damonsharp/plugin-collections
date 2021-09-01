<?php

namespace DWS_Plugin_Collections;

use Exception;

/**
 * Bulk Plugin Actions Class.
 *
 * Makes modifications to the default plugins bulk actions list,
 * adding items to activate individual plugins collections.
 */
class Bulk_Plugin_Actions extends Plugin_Collections_Base {

	/**
	 * Constructor.
	 *
	 * Filter and do things.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts_and_styles' ] );
		add_filter( 'bulk_actions-plugins', [ $this, 'modify_bulk_plugin_actions' ] );
		add_filter( 'handle_bulk_actions-plugins', [ $this, 'process_bulk_plugin_collection' ], 10, 2 );
		// @TODO Work out the callbacks for these. We need to update each of the posts meta data when a theme or plugin is deleted.
		//add_action( 'delete_plugin', [ $this, 'clean_plugin_collection_plugin_list' ] );
		//add_action( 'delete_theme', [ $this, 'clean_plugin_collection_theme_list'] );
	}

	/**
	 * Filter callback to modify the default bulk plugins actions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions The plugin actions.
	 *
	 * @return array $actions
	 */
	public function modify_bulk_plugin_actions( $actions ) {
		$plugin_collections = get_posts(
			[
				'post_type'      => $this->plugin_slug,
				// @TODO Document filter.
				'posts_per_page' => apply_filters( 'dws_plugin_collections_per_page', 100 ),
			]
		);
		if ( empty( $plugin_collections ) ) {
			return $actions;
		}
		$plugin_collections = wp_list_pluck( $plugin_collections, 'post_title', 'ID' );
		$plugin_collections = $this->modify_plugin_collection_name( $plugin_collections );
		$actions            = array_replace( $actions, $plugin_collections );

		return $actions;
	}

	/**
	 * Modify Plugin Collection Name.
	 *
	 * Modifies the name visible in the bulk plugins actions
	 * select menu.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin_collections The plugin collections.
	 *
	 * @return array $plugin_collections
	 */
	public function modify_plugin_collection_name( $plugin_collections ) {
		asort( $plugin_collections );
		foreach ( $plugin_collections as $id => $name ) {
			// translators: %s represents the name of the plugin collection.
			$plugin_collections[ $id ] = sprintf( __( 'Activate %s Collection', 'dwspc' ), $name );
		}

		return $plugin_collections;
	}

	/**
	 * Process items selected from the bulk plugin collection menu.
	 *
	 * @since 1.0.0
	 *
	 * @param string|boolean $redirect_url Either the redirect URL or false.
	 * @param string         $doaction     The action/value from the selected menu item.
	 *
	 * @return string|boolean $redirect_url
	 */
	public function process_bulk_plugin_collection( $redirect_url, $doaction ) {
		if ( empty( $doaction ) ) {
			return $redirect_url;
		}

		$plugin_collections = get_post_meta( $doaction, 'dws_plugin_collections', true );
		if ( empty( $plugin_collections ) ) {
			return $redirect_url;
		}

		$all_plugins = get_plugins_array();
		// Exclude the Plugins Collection plugin from deactivation.
		unset( $all_plugins['plugin-collections/plugin-collections.php'] );
		if ( ! empty( $all_plugins ) ) {
			$all_plugins = array_keys( $all_plugins );
			deactivate_plugins( $all_plugins );
		}

		foreach ( $plugin_collections as $file ) {
			// Don't try to activate a plugin that isn't already installed.
			try {
				if ( in_array( $file, $all_plugins ) ) {
					activate_plugin( $file );
				} else {
					throw new Exception( "Cannot activate plugin {$file}." );
				}
			} catch ( Exception $exception ) {
				new Admin_Notice( $exception->getMessage() );
			}
		}

		// Possibly switch theme based on collection selection.
		$collection_theme = get_post_meta( $doaction, 'dws_collection_theme', true );
		$current_theme    = wp_get_theme();
		$current_theme    = $current_theme->get_template();
		try {
			if ( ! empty( $collection_theme ) && ! empty( $current_theme ) && $collection_theme !== $current_theme ) {
				switch_theme( $collection_theme );
			} else {
				throw new Exception( "Cannot switch theme to {$collection_theme}." );
			}
		} catch ( Exception $exception ) {
			new Admin_Notice( $exception->getMessage() );
		}

		return admin_url( 'plugins.php' );
	}

	/**
	 * Enqueue Script & Styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts_and_styles() {
		wp_enqueue_style( "{$this->plugin_slug}-admin-styles", DWSPC_DIR_URL . 'assets/css/admin.css' );
		wp_enqueue_script( "{$this->plugin_slug}-admin-scripts", DWSPC_DIR_URL . 'assets/js/admin-scripts.js', [ 'jquery' ], null, true );
	}

	public function clean_plugin_collection_plugin_list( $plugin_file ) {
		return $plugin_file;
	}

	public function clean_plugin_collection_theme_list( $stylesheet ) {
		$collection_meta = $this->get_plugin_collections_data();

		return $stylesheet;
	}

	protected function get_plugin_collections_data() {
		$transient_name = 'plugin_collection_data';

		//if ( false === ( $data = get_transient( $transient_name ) ) ) {
			// Data for transient.
			$collections = new \WP_Query(
				[
					'post_type' => $this->plugin_slug,
					'posts_per_page' => 100, // it'd be crazy if there were more than this :).
					'no_found_rows' => true,
					'update_post_term_cache' => false,
					'fields' => 'ids',
				]
			);

			if ( ! empty( $collections->posts ) ) {
				foreach ( $collections->posts as $collection_id ) {
					$data[ $collection_id ] = get_post_meta( $collection_id, 'dws_plugin_collections', true );
				}
			}

			// Store in transient for next time.
			//set_transient( $transient_name, $data, MONTH_IN_SECONDS );

		//}

		return $data;
	}

}
