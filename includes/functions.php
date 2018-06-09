<?php
/**
 * Group of helper functions.
 *
 * @package Sharp_Plugin_Collections
 */

namespace Sharp_Plugin_Collections;

/**
 * Convert a slug into a title string.
 *
 * @since 1.0.0
 *
 * @param string $slug The slug.
 * @param bool   $capitalize Should the titme be capitalized.
 *
 * @return string Title created from slug.
 */
function titleize_slug( string $slug, $capitalize = true ) {
	$title = str_replace( [ '_', '-' ], ' ', $slug );
	if ( $capitalize ) {
		$title = ucfirst( $title );
	}

	return $title;
}

/**
 * Get an array of plugins, keyed by "Name".
 *
 * @since 1.0.0
 *
 * @return array
 */
function get_plugins_array() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$plugins = get_plugins();
	if ( ! empty( $plugins ) ) {
		$plugins = wp_list_pluck( $plugins, 'Name' );
	}

	return $plugins;
}

/**
 * Get an array of installed themes, keyed by Name.
 *
 * @since 1.0.0
 *
 * @return array
 */
function get_themes_array() {
	if ( ! function_exists( 'wp_get_themes' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$themes = wp_get_themes();
	if ( ! empty( $themes ) ) {
		$themes = wp_list_pluck( $themes, 'Name' );
	}

	return $themes;
}
