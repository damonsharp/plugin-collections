<?php

namespace DWS_Plugin_Collections;

/**
 * Plugin Collections Post Type class.
 *
 * Set up post types and related data structures.
 */
class Post_Types extends Plugin_Collections_Base {

	/**
	 * Collections Meta object.
	 *
	 * @var Collections_Meta
	 *
	 * @since 1.0.0
	 */
	public Collections_Meta $collections_meta;

	/**
	 * Data structures object.
	 *
	 * @var Data_Structures
	 *
	 * @since 1.0.0
	 */
	public Data_Structures $data_structures;

	/**
	 * List of installed plugins.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public array $plugins_list;

	/**
	 * List of installed themes.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public array $themes_list;

	/**
	 * Post_Types constructor.
	 *
	 * @param \DWS_Plugin_Collections\Data_Structures  $data_structures
	 * @param \DWS_Plugin_Collections\Collections_Meta $collections_meta
	 *
	 * @since 1.0.0
	 */
	public function __construct( Data_Structures $data_structures, Collections_Meta $collections_meta ) {
		$this->collections_meta = $collections_meta;
		$this->data_structures  = $data_structures;
		$this->plugins_list     = $this->collections_meta->get_plugins_array();
		$this->themes_list      = $this->collections_meta->get_themes_array();
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
		],          10, 2 );
		add_action( 'admin_menu', [ $this, 'add_plugins_collections_sub_pages' ] );
		add_action( 'save_post', [ $this, 'save_plugin_collection_meta' ] );
	}

	/**
	 * Add post meta boxes.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
			"{$this->plugin_slug}",
			__( 'Plugin Collections', 'dwspc' ),
			[ $this, 'meta_box_html' ],
			$this->plugin_slug,
			'normal'
		);
	}

	/**
	 * Callback method for adding meta box for collections.
	 *
	 * @param \WP_Post $post The collection post object.
	 *
	 * @since 1.0.0
	 */
	public function meta_box_html( \WP_Post $post ) {
		$plugin_collections_plugin_meta = $this->collections_meta->get_collections_meta( $post->ID, 'plugins' );
		$this->prepare_plugins_datasource();
		if ( ! empty( $this->plugins_list ) ) : ?>
			<h4>Available Plugins</h4>
			<?php
			foreach ( $this->plugins_list as $key => $plugin ) :
				?>
				<label class="<?php
				echo esc_attr( "{$this->plugin_slug}-item" ); ?>" for="<?php
				echo esc_attr( $key ); ?>">
					<input id="<?php
					echo esc_attr( $key ); ?>" type="checkbox" name="dwspc_plugin_collections[]" value="<?php
					echo esc_attr( $key ); ?>" <?php
					checked( in_array( $key, (array) $plugin_collections_plugin_meta, true ) ); ?>>
					<?php
					echo esc_html( $plugin ); ?>
				</label>
			<?php
			endforeach;
		endif;

		$current_theme                  = get_template();
		$plugin_collections_plugin_meta = $this->collections_meta->get_collections_meta( $post->ID, 'theme' );
		$comparison                     = ( ! empty( $plugin_collections_plugin_meta ) ) ? $plugin_collections_plugin_meta : $current_theme;
		if ( ! empty( $this->themes_list ) ) : ?>
			<h4>Available Themes</h4>
			<?php
			foreach ( $this->themes_list as $key => $theme ) :
				?>
				<label
					class="<?php echo esc_attr( "{$this->plugin_slug}-item" ); ?>"
					for="<?php echo esc_attr( $key ); ?>"
				>
					<input id="<?php echo esc_attr( $key ); ?>"
						type="radio"
						name="<?php echo esc_attr( "{$this->plugin_slug}_collection_theme" ); ?>"
						value="<?php echo esc_attr( $key ); ?>"
						<?php checked( $comparison === $key ); ?>
					>
					<?php echo esc_html( $theme ); ?>
				</label>
			<?php
			endforeach;
		endif;

	}

	/**
	 * Callback to add new Plugin Collections CPT table columns.
	 *
	 * Also rearranging to keep the date as the last column.
	 *
	 * @param array $columns The column items.
	 *
	 * @return array $columns
	 * @since 1.0.0
	 *
	 */
	public function plugins_collections_table_columns( array $columns ) : array {
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
	 * @param string $col_name The column name.
	 * @param int    $post_id  The post id.
	 *
	 * @since 1.0.0
	 *
	 */
	public function plugins_collections_table_column_data( string $col_name, int $post_id ) {
		switch ( $col_name ) {
			case "{$this->plugin_slug}_plugins":
				$post_meta = $this->collections_meta->get_collections_meta( $post_id, 'plugins' );
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
				$post_meta = $this->collections_meta->get_collections_meta( $post_id, 'theme' );
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
				<h2>
					<?php esc_html_e( 'About Plugin Collections', 'dwspc' ); ?>
				</h2>
				<p>
					<?php esc_html_e( "Plugin collections allows you to easily create saved collections of plugins to activate along with a theme. Once created, these collections can be selected from the plugin admin screen's Bulk Action menu. Once selected and applied the plugins in the selected collection will be activated along with your the collection's chosen theme and all other plugins except this one will be deactivated. Here's a quick walkthrough video...",
					            'dwspc' ); ?>
				</p>
				<iframe width="853" height="480" src="https://www.youtube.com/embed/HkbIPCUtY0U?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
				<p>
					<?php esc_html_e( "NOTE: Each plugin and the theme contained in the applied collection will only be activated if it is currently installed.",
					            'dwspc' ); ?>
				</p>
				<p>
					<?php esc_html_e( "Plugin collections can be useful if there's a need for the following:",
					            'dwspc' ); ?>
				</p>
				<ol>
					<li>
						<?php
						esc_html_e( "Test a specific plugin's compatibility with different activated plugins.",
						            'dwspc' ); ?></li>
					<li>
						<?php esc_html_e( 'Handle support requests and have the need to be able to swap collections of plugins on a regular basis.',
						            'dwspc' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Other use cases not thought of.', 'dwspc' ); ?>
					</li>
				</ol>
				<h2><?php esc_html_e( 'Support', 'dwspc' ); ?></h2>
				<p>
					<?php esc_html_e( "Although I will do my best to fix bugs within the plugin's code, this is a free plugin and carries with it no promise of support, including, but not limited to reponses to emails, wordpress.org forum posts, etc.",
					            'dwspc' ); ?>
				</p>
				<p>
					<?php echo wp_kses_post( __( 'Pull requests are welcome here: <a href="https://github.com/damonsharp/plugin-collections">https://github.com/damonsharp/plugin-collections</a>' ) ); ?>
				</p>
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

	/**
	 * Update the plugin's collection metadata.
	 *
	 * @param int $post_id The collection ID.
	 *
	 * @return bool
	 */
	public function save_plugin_collection_meta( int $post_id ) : bool {
		if (
			! isset( $_POST["{$this->plugin_slug}_plugin_collections"] )
			&& ! isset( $_POST["{$this->plugin_slug}_collection_theme"] )
		) {
			return false;
		}

		$data = [
			'plugins' => $_POST["{$this->plugin_slug}_plugin_collections"],
			'theme'   => $_POST["{$this->plugin_slug}_collection_theme"],
		];

		return update_post_meta( $post_id, $this->plugin_slug, $data );
	}

}
