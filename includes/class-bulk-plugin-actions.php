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

	private Collections_Meta $collections_meta;

	/**
	 * Constructor.
	 *
	 * Filter and do things.
	 *
	 * @param Collections_Meta $collections_meta *
	 *
	 * @since 1.0.0
	 */
	public function __construct( Collections_Meta $collections_meta) {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts_and_styles' ] );
		add_filter( 'bulk_actions-plugins', [ $this, 'modify_bulk_plugin_actions' ] );
		add_filter( 'handle_bulk_actions-plugins', [ $this, 'process_bulk_plugin_collection' ], 10, 2 );

		// Cleanup collections post type meta when a plugin or theme is deleted.
		add_action( 'delete_plugin', [ $this, 'cleanup_collections_plugin_meta' ] );
		add_action( 'delete_theme', [ $this, 'cleanup_collections_theme_meta' ] );

		$this->collections_meta = $collections_meta;
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
	public function modify_bulk_plugin_actions( array $actions ) : array {
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

		return array_replace( $actions, $plugin_collections );
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
	public function modify_plugin_collection_name( array $plugin_collections ) : array {
		asort( $plugin_collections );
		foreach ( $plugin_collections as $id => $name ) {
			// translators: %s represents the name of the plugin collection.
			$plugin_collections[ $id ] = sprintf( __( 'Apply Plugin Collection: %s', 'dwspc' ), $name );
		}

		return $plugin_collections;
	}

	/**
	 * Process items selected from the bulk plugin collection menu.
	 *
	 * @since 1.0.0
	 *
	 * @param string|boolean $redirect_url Either the redirect URL or false.
	 * @param string         $post_id     The post id for the collection to process.
	 *
	 * @return string|boolean $redirect_url
	 */
	public function process_bulk_plugin_collection( string $redirect_url, string $post_id ) {
		if ( empty( $post_id ) ) {
			return $redirect_url;
		}

		$plugin_collections = $this->collections_meta->get_collections_meta( $post_id, 'plugins' );
		if ( empty( $plugin_collections ) ) {
			return $redirect_url;
		}

		$all_plugins = $this->collections_meta->get_plugins_array();
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
		$collection_theme  = get_post_meta( $post_id, 'theme' );
		try {
			if ( ! empty( $collection_theme ) && ! empty( $current_theme ) && $collection_theme !== $$this->get_current_theme_name() ) {
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
		wp_enqueue_style( "{$this->plugin_slug}-admin-styles", DWSPC_DIR_URL . 'dist/css/admin.css' );
		wp_enqueue_script( "{$this->plugin_slug}-admin-scripts", DWSPC_DIR_URL . 'dist/js/admin-scripts.js', [ 'jquery' ], null, true );
	}

	/**
	 * Process the collection plugin meta cleanup if it contains the item being deleted.
	 *
	 * If a plugin is deleted that is contained withing plugin collection
	 * metadata, that item should be removed from the collection.
	 *
	 * @param string $filename The plugin filename from the bulk action.
	 */
	public function cleanup_collections_plugin_meta( string $filename ) {
		$plugin_collection_meta = $this->get_plugin_collections_data();
		if ( empty( $plugin_collection_meta ) ) {
			return;
		}

		foreach ( $plugin_collection_meta as $collection_id => $collections ) {
			foreach( $collections['plugins'] as $id => $collection_value ) {
				if ( $collection_value === $filename ) {
					unset( $collections['plugins'][ $id ] );
				}
				$plugin_collection_meta[$collection_id]['plugins'] = $collections['plugins'];
			}
			update_post_meta( $collection_id, $this->plugin_slug, $plugin_collection_meta[ $collection_id ] );
		}

	}
	/**
	 * Process the collection theme meta cleanup if it contains the item being deleted.
	 *
	 * If a theme is deleted that is contained withing plugin collection
	 * metadata, that item should be removed from the collection.
	 *
	 * @param string $filename The theme filename from the bulk action.
	 */
	public function cleanup_collections_theme_meta( string $filename ) {
		$plugin_collection_meta = $this->get_plugin_collections_data();
		if ( empty( $plugin_collection_meta ) ) {
			return;
		}

		foreach ( $plugin_collection_meta as $collection_id => $collections ) {
			if ( $filename === $collections['theme'] ) {
				unset( $plugin_collection_meta[ $collection_id ]['theme'] );
				// Reset any collections set with the deleted theme to the current active theme.
				$plugin_collection_meta[ $collection_id ]['theme'] = $this->get_current_theme_name();
			}

			update_post_meta( $collection_id, $this->plugin_slug, $plugin_collection_meta[ $collection_id ] );
		}

	}

	/**
	 * Query the collections post type for those items that have the key/filename
	 * match so we know which ones need cleanup when a theme or plugin is deleted.
	 *
	 * Since this is expensive we'll store the data in a transient, then prime
	 * it when a collection is saved so we always have the most up to date data.
	 *
	 * @return array
	 */
	protected function get_plugin_collections_data() {
		$transient_name = "{$this->plugin_slug}_collections";

		//if ( false === ( $data = get_transient( $transient_name ) ) ) {
			// Data for transient.
			$collections = new \WP_Query(
				[
					'post_type' => $this->plugin_slug,
					'posts_per_page' => 100, // it'd be crazy if there were more than this :).
					'no_found_rows' => true,
					'update_post_term_cache' => false,
					'fields' => 'ids'
				]
			);

			if ( ! empty( $collections->posts ) ) {
				foreach ( $collections->posts as $post_id ) {
					$data[ $post_id ]['plugins'] = $this->collections_meta->get_collections_meta( $post_id, 'plugins' );
					$data[ $post_id ]['theme'] = $this->collections_meta->get_collections_meta( $post_id, 'theme' );
				}
			}

			// Store in transient for next time.
			//set_transient( $transient_name, $data, MONTH_IN_SECONDS );

		//}

		return $data;
	}

	/**
	 * Get current theme name.
	 */
	protected function get_current_theme_name() {
		$current_theme_obj = wp_get_theme();
		return $current_theme_obj->get_stylesheet();
	}

}
