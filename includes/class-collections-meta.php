<?php

namespace DWS_Plugin_Collections;

class Collections_Meta extends Plugin_Collections_Base {

	/**
	 * Get an array of plugins, keyed by "Name".
	 *
	 * @return array
	 */
	public function get_plugins_array() : array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();
		if ( ! empty( $plugins ) ) {
			$plugins = wp_list_pluck( $plugins, 'Name' );
		}

		return ! empty( $plugins ) ? $plugins : [];
	}

	/**
	 * Get an array of installed themes, keyed by Name.
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function get_themes_array() : array {
		if ( ! function_exists( 'wp_get_themes' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$themes = wp_get_themes();
		if ( ! empty( $themes ) ) {
			$themes = wp_list_pluck( $themes, 'Name' );
		}

		return ! empty( $themes ) ? $themes : [];
	}

	/**
	 * Get collections meta by key or return the entire array.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $key      The key to get out of the returned metadata.
	 *
	 * @return string|array
	 */
	public function get_collections_meta( int $post_id, string $key = '' ) {
		$collections_post_meta = get_post_meta( $post_id, $this->plugin_slug, true );
		if ( empty( $key ) ) {
			return $collections_post_meta;
		}

		return $collections_post_meta[ $key ] ?? '';
	}

}
