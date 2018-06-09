<?php

namespace Sharp_Plugin_Collections;

class Post_Types extends Plugin_Collections_Base {

	/**
	 * Data structures object.
	 *
	 * @var Data_Structures
	 *
	 * @since 1.0.0
	 */
	public $data_structures;

	/**
	 * List of installed plugins.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public $plugins_list;

	/**
	 * List of installed themes.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public $themes_list;

	/**
	 * Post_Types constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->data_structures = new Data_Structures();
		$this->plugins_list    = get_plugins_array();
		$this->themes_list     = get_themes_array();
		$this->init();
	}

	/**
	 * Set up custom post type for Plugin Collections
	 * and fire off callbacks for post types & table columns.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->data_structures->add_post_type(
			$this->plugin_slug,
			[
				'singular'           => 'Plugin Collection',
				'supports'           => [ 'title' ],
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
			]
		);

		add_action( "fm_post_{$this->plugin_slug}", [ $this, 'add_plugins_meta_box' ] );
		add_action( "fm_post_{$this->plugin_slug}", [ $this, 'add_themes_meta_box' ] );
		add_filter( "manage_edit-{$this->plugin_slug}_columns", [ $this, 'plugins_collections_table_columns' ] );
		add_action( "manage_{$this->plugin_slug}_posts_custom_column", [ $this, 'plugins_collections_table_column_data' ], 10, 2 );
	}

	/**
	 * Fieldmanager callback method for adding Fieldmanager Fields to
	 * custom post type.
	 *
	 * @since 1.0.0
	 */
	public function add_plugins_meta_box() {
		$fm = new \Fieldmanager_Checkboxes(
			[
				'name'                      => 'dws_plugin_collections',
				'datasource'                => $this->get_plugins_datasource(),
				'description'               => __( 'Plugins checked below will be activated when applying this collection on the plugins admin page. All others will be deactivated.', $this->plugin_slug ),
				'description_after_element' => false,
			]
		);

		$fm->add_meta_box( __( 'Available Plugins', $this->plugin_slug ), $this->plugin_slug );
	}

	/**
	 * Fieldmanager callback method for adding Fieldmanager metabox
	 * for theme options.
	 *
	 * @since 1.0.0
	 */
	public function add_themes_meta_box() {
		$current_theme = wp_get_theme();
		$fm            = new \Fieldmanager_Radios(
			[
				'name'                      => 'dws_collection_theme',
				'description'               => __( 'Custom post type used to create plugin collections.', $this->plugin_slug ),
				'datasource'                => $this->get_themes_datasource(),
				'description'               => __( 'The theme selected below will be activated when applying this collection. Current active theme shown on page load.', $this->plugin_slug ),
				'description_after_element' => false,
				'default_value'             => $current_theme->get_template(),
			]
		);

		$fm->add_meta_box( __( 'Available Themes', $this->plugin_slug ), $this->plugin_slug );
	}

	/**
	 * Get datasouce for plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return \Fieldmanager_Datasource $plugins_data_source
	 */
	private function get_plugins_datasource() {
		// Remove Plugin Collectsions from list as it will always stay activated.
		if ( isset( $this->plugins_list['plugin-collections/plugin-collections.php'] ) ) {
			unset( $this->plugins_list['plugin-collections/plugin-collections.php'] );
		}
		$plugins_data_source = new \Fieldmanager_Datasource(
			[
				'options' => $this->plugins_list,
			]
		);

		return $plugins_data_source;
	}

	/**
	 * Get datasouce for themes.
	 *
	 * @since 1.0.0
	 *
	 * @return \Fieldmanager_Datasource $themes_data_source
	 */
	private function get_themes_datasource() {
		$current_theme      = wp_get_theme();
		$themes_data_source = new \Fieldmanager_Datasource(
			[
				'options' => $this->themes_list,
			]
		);

		return $themes_data_source;
	}

	/**
	 * Callback to add new Plugin Collections CPT table columns.
	 *
	 * Also rearranging to keep the date as the last column.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns The column items.
	 * @return array $columns
	 */
	public function plugins_collections_table_columns( $columns ) {
		$date_column = $columns['date'];
		unset( $columns['date'] );
		$columns[ "{$this->plugin_slug}_plugins" ] = __( 'Collection Plugins', $this->plugin_slug );
		$columns[ "{$this->plugin_slug}_theme" ]   = __( 'Theme', $this->plugin_slug );
		$columns['date']                           = $date_column;

		return $columns;
	}

	/**
	 * Handle data for the new tables columns.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $col_name The column name.
	 * @param integer $id The post id.
	 */
	public function plugins_collections_table_column_data( $col_name, $id ) {
		switch ( $col_name ) {
			case "{$this->plugin_slug}_plugins":
				$post_meta = get_post_meta( $id, 'dws_plugin_collections', true );
				if ( is_array( $post_meta ) && ! empty( $post_meta ) ) {
					foreach ( $post_meta as $plugin_file ) {
						if ( ! empty( $this->plugins_list[ $plugin_file ] ) ) {
							$output = $this->plugins_list[ $plugin_file ];
						}
					}
				}
				break;

			case "{$this->plugin_slug}_theme":
				$post_meta = get_post_meta( $id, 'dws_collection_theme', true );
				if ( ! empty( $this->themes_list[ $post_meta ] ) ) {
					$output = wp_kses_post( sprintf( '<p>%s</p>', $this->themes_list[ $post_meta ] ) );
				}
				break;
		}

		echo wp_kses_post( sprintf( '<p>%s</p>', $output ) );

	}

}
