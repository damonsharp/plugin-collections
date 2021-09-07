<?php

namespace DWS_Plugin_Collections;

/**
 * Class Plugin_Collections_Base
 *
 * @package DWS_Plugin_Collections
 */
class Plugin_Collections_Base {

	protected $plugin_slug = 'dwspc';

	protected $current_theme_name;

	/**
	 * Get this party started.
	 *
	 * @return void
	 */
	public function init() {
		$data_structures  = new Data_Structures();
		$collections_meta = new Collections_Meta();
		new Post_Types( $data_structures, $collections_meta );
		new Bulk_Plugin_Actions( $collections_meta );
	}

}
