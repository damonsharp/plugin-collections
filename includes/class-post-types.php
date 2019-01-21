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

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_filter( "manage_edit-{$this->plugin_slug}_columns", [ $this, 'plugins_collections_table_columns' ] );
		add_action( "manage_{$this->plugin_slug}_posts_custom_column", [
			$this,
			'plugins_collections_table_column_data',
		], 10, 2 );
		add_action( 'admin_menu', [ $this, 'add_plugins_collections_sub_pages' ] );
		add_action( 'save_post', [ $this, 'save_plugin_collection_meta'] );
	}

	/**
	 * Add post meta boxes.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'dws_plugin_collections',
			__( 'Available Plugins', 'dwspc' ),
			[ $this, 'plugins_meta_box_html' ],
			$this->plugin_slug,
			'normal',
			'default'
		);

		add_meta_box(
			'dws_collections_theme',
			__( 'Available Themes', 'dwspc' ),
			[ $this, 'themes_meta_box_html' ],
			$this->plugin_slug,
			'normal',
			'default'
		);
	}

	/**
	 * Callback method for adding meta box for theme options.
	 *
	 * @since 1.0.0
	 */
	public function themes_meta_box_html() {
		global $post;
		if ( empty( $post ) ) {
			return;
		}
		$current_theme = get_template();
		$current_collection_theme = get_post_meta( $post->ID, 'dws_collection_theme', true );
		$comparison = ( ! empty( $current_collection_theme ) ) ? $current_collection_theme : $current_theme;
		if ( ! empty( $this->themes_list ) ) {
			foreach ( $this->themes_list as $key => $theme ) {
				?>
				<label class="<?php echo esc_attr( "{$this->plugin_slug}-item" ); ?>" for="<?php echo esc_attr( $key ); ?>">
					<input id="<?php echo esc_attr( $key ); ?>" type="radio" name="dws_collection_theme" value="<?php echo esc_attr( $key ); ?>" <?php checked( $comparison === $key ); ?>>
					<?php echo esc_html( $theme ); ?>
				</label>
				<?php
			}
		}
	}

	/**
	 * Callback method for adding meta box for plugin options.
	 *
	 * @since 1.0.0
	 */
	public function plugins_meta_box_html() {
		global $post;
		if ( empty( $post ) ) {
			return;
		}
		$current_plugin_collection = (array) get_post_meta( $post->ID, 'dws_plugin_collections', true );
		$this->prepare_plugins_datasource();
		if ( ! empty( $this->plugins_list ) ) {
			foreach ( $this->plugins_list as $key => $plugin ) {
				?>
				<label class="<?php echo esc_attr( "{$this->plugin_slug}-item" ); ?>" for="<?php echo esc_attr( $key ); ?>">
					<input id="<?php echo esc_attr( $key ); ?>" type="checkbox" name="dws_plugin_collections[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $current_plugin_collection, true ) ); ?>>
					<?php echo esc_html( $plugin ); ?>
				</label>
				<?php
			}
		}
	}

	/**
	 * Callback to add new Plugin Collections CPT table columns.
	 *
	 * Also rearranging to keep the date as the last column.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns The column items.
	 *
	 * @return array $columns
	 */
	public function plugins_collections_table_columns( $columns ) {
		$date_column = $columns['date'];
		unset( $columns['date'] );
		$columns["{$this->plugin_slug}_plugins"] = __( 'Plugins in Collection', 'dwspc' );
		$columns["{$this->plugin_slug}_theme"]   = __( 'Theme', 'dwspc' );
		$columns['date']                         = $date_column;

		return $columns;
	}

	/**
	 * Handle data for the new tables columns.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $col_name The column name.
	 * @param integer $id       The post id.
	 */
	public function plugins_collections_table_column_data( $col_name, $id ) {
		switch ( $col_name ) {
			case "{$this->plugin_slug}_plugins":
				$post_meta = get_post_meta( $id, 'dws_plugin_collections', true );
				if ( is_array( $post_meta ) && ! empty( $post_meta ) ) {
					foreach ( $post_meta as $plugin_file ) {
						if ( ! empty( $this->plugins_list[ $plugin_file ] ) ) {
							$output = $this->plugins_list[ $plugin_file ];
							echo wp_kses_post( sprintf( '<p>%s</p>', $output ) );

						}
					}
				}
				break;

			case "{$this->plugin_slug}_theme":
				$post_meta = get_post_meta( $id, 'dws_collection_theme', true );
				if ( ! empty( $this->themes_list[ $post_meta ] ) ) {
					$output = $this->themes_list[ $post_meta ];
					echo wp_kses_post( sprintf( '<p>%s</p>', $output ) );

				}
				break;
		}
	}

	/**
	 * Add an about submenu page.
	 */
	public function add_plugins_collections_sub_pages() {
		add_submenu_page(
			"edit.php?post_type={$this->plugin_slug}",
			__( 'About Collections', 'dwspc' ),
			__( 'About Collections', 'dwspc' ),
			'manage_options',
			"{$this->plugin_slug}_about",
			[ $this, 'about_page_content' ]
		);
	}

	/**
	 * Callback to output the about page content.
	 */
	public function about_page_content() {
		?>
		<div class="wrap">
			<div class="dwspc-inner">
				<h2><?php esc_html_e( 'About Plugin Collections', 'dwspc' ); ?></h2>
				<p><?php esc_html_e( "Plugin collections allows you to easily create saved collections of plugins to activate along with a theme. Once created, these collections can be selected from the plugin admin screen's Bulk Action menu. Once selected and applied the plugins in the selected collection will be activated along with your the collection's chosen theme and all other plugins except this one will be deactivated. Here's a quick walkthrough video...", 'dwspc' ); ?></p>
				<iframe width="853" height="480" src="https://www.youtube.com/embed/HkbIPCUtY0U?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
				<p><?php esc_html_e( "NOTE: Each plugin and the theme contained in the applied collection will only be activated if it is currently installed.", 'dwspc' ); ?></p>
				<p><?php esc_html_e( "Plugin collections can be useful if there's a need for the following:", 'dwspc' ); ?></p>
				<ol>
					<li><?php esc_html_e( "Test a specific plugin's compatibility with different activated plugins.", 'dwspc' ); ?></li>
					<li><?php esc_html_e( 'Handle support requests and have the need to be able to swap collections of plugins on a regular basis.', 'dwspc' ); ?></li>
					<li><?php esc_html_e( 'Other use cases not thought of.', 'dwspc' ); ?></li>
				</ol>
				<h2><?php esc_html_e( 'Support', 'dwspc' ); ?></h2>
				<p><?php esc_html_e( "Although I will do my best to fix bugs within the plugin's code, this is a free plugin and carries with it no promise of support, including, but not limited to reponses to emails, wordpress.org forum posts, etc.", 'dwspc' ); ?></p>
				<p><?php echo wp_kses_post( __( 'Pull requests are welcome here: <a href="https://github.com/damonsharp/plugin-collections">https://github.com/damonsharp/plugin-collections</a>' ) ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Prepare datasouce for plugins.
	 *
	 * @since 1.0.0
	 */
	private function prepare_plugins_datasource() {
		// Remove Plugin Collections from list as it will always stay activated.
		if ( isset( $this->plugins_list['plugin-collections/plugin-collections.php'] ) ) {
			unset( $this->plugins_list['plugin-collections/plugin-collections.php'] );
		}
	}

	public function save_plugin_collection_meta( $post_id ) {
		if ( ! isset( $_POST['dws_plugin_collections'] ) && ! isset( $_POST['dws_collection_theme'] ) ) {
			return;
		}
		$dws_plugin_collections = array_map( 'sanitize_text_field', $_POST['dws_plugin_collections'] );
		$dws_collection_theme = sanitize_text_field( $_POST['dws_collection_theme'] );

		update_post_meta( $post_id, 'dws_plugin_collections', $dws_plugin_collections );
		update_post_meta( $post_id, 'dws_collection_theme', $dws_collection_theme );
	}

}
