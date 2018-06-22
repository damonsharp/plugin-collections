<?php

namespace Sharp_Plugin_Collections;

class Data_Structures extends Plugin_Collections_Base {

	/**
	 * The post types.
	 *
	 * @since 1.0.0
	 *
	 * @var array the post types.
	 */
	private $post_types = [];

	/**
	 * The taxonomies.
	 *
	 * @since 1.0.0
	 *
	 * @var array the post types.
	 */
	private $taxonomies = [];

	public function __construct() {
		$this->setup();
	}

	/**
	 * Setup data structures.
	 *
	 * @since 1.0.0
	 */
	public function setup() {
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Register the post type and its taxonomies.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		foreach ( $this->post_types as $post_type_slug => $args ) {
			$singular = ! empty( $args['singular'] ) ? $args['singular'] : titleize_slug( $post_type_slug );
			$plural   = ! empty( $args['plural'] ) ? $args['plural'] : $singular . 's';
			$supports = ! empty( $args['supports'] ) ? $args['supports'] : [];

			register_post_type(
				$post_type_slug, wp_parse_args(
					[
						'description'           => __( 'Custom post type.', 'dwspc' ),
						'public'                => true,
						'exclude_from_search'   => true,
						'publicly_queryable'    => false,
						'show_ui'               => true,
						'show_in_nav_menus'     => true,
						'show_in_menu'          => true,
						'show_in_admin_bar'     => true,
						'menu_position'         => 6,
						'menu_icon'             => null,
						'capability_type'       => 'post',
						'capabilities'          => [],
						'map_meta_cap'          => null,
						'hierarchical'          => false,
						'supports'              => $supports,
						'register_meta_box_cb'  => '',
						'taxonomies'            => [],
						'has_archive'           => false,
						'rewrite'               => true,
						'query_var'             => true,
						'can_export'            => true,
						'delete_with_user'      => false,
						'show_in_rest'          => false,
						'rest_base'             => '',
						'rest_controller_class' => '',
						'labels'                => [
							'name'                   => $plural,
							'singular_name'          => $singular,
							'add_new'                => "Add New $singular",
							'add_new_item'           => "Add New $singular",
							'edit_item'              => "Edit $singular",
							'new_item'               => "New $singular",
							'view_item'              => "View $singular",
							'view_items'             => "View $plural",
							'search_items'           => "Search $plural",
							'not_found'              => "No $plural found",
							'not_found_in_trash'     => "No $plural found in Trash",
							'parent_item_colon'      => null,
							'all_items'              => $plural,
							'archives'               => $singular,
							'attributes'             => $singular,
							'insert_into_item'       => $singular,
							'uploaded_to_this_ item' => $singular,
							'featured_image'         => "$singular's Featured Image",
							'set_featured_image'     => "Add $singular's Featured Image",
							'remove_featured_image'  => "Remove $singular's Featured Image",
							'use_featured_image'     => "Use as $singular's Featured Image",
							'menu_name'              => $plural,
							'filter_items_list'      => null,
							'items_list_navigation'  => null,
							'items_list'             => null,
							'name_admin_bar'         => null,
						],
					], $args
				)
			);
		}

		foreach ( $this->taxonomies as $taxonomy => $args ) {
			$singular = ( ! empty( $args['singular'] ) ) ? $args['singular'] : Helpers::titleize_slug( $taxonomy );
			$plural   = ( ! empty( $args['plural'] ) ) ? $args['plural'] : $singular . 's';

			register_taxonomy(
				$taxonomy, $args['post_type'], wp_parse_args(
					$args, [
						'labels' => [
							'name'                       => $plural,
							'singular_name'              => $singular,
							'search_items'               => 'Search ' . $plural,
							'popular_items'              => 'Popular ' . $plural,
							'all_items'                  => 'All ' . $plural,
							'parent_item'                => 'Parent ' . $singular,
							'parent_item_colon'          => "Parent {$singular}:",
							'edit_item'                  => 'Edit ' . $singular,
							'update_item'                => 'Update ' . $singular,
							'add_new_item'               => 'Add New ' . $singular,
							'new_item_name'              => "New {$singular} Name",
							'separate_items_with_commas' => "Separate {$plural} with commas",
							'add_or_remove_items'        => "Add or remove {$plural}",
							'choose_from_most_used'      => "Choose from the most used {$plural}",
							'not_found'                  => "No {$plural} found.",
							'menu_name'                  => $plural,
						],
					]
				)
			);
		}
	}

	/**
	 * Add the post type and its args to the array.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type the post type type.
	 * @param array  $args array of post type args.
	 */
	public function add_post_type( $type, $args ) {
		$this->post_types[ $type ] = $args;
	}

	/**
	 * Add the taxonomy to the array.
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy the taxonomy type.
	 * @param array  $args     array of taxonomy args.
	 */
	public function add_taxonomy( $taxonomy, $args ) {
		$this->taxonomies[ $taxonomy ] = $args;
	}

}
