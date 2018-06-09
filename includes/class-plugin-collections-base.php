<?php

namespace Sharp_Plugin_Collections;

/**
 * Class Plugin_Collections_Base
 *
 * @package Sharp_Plugin_Collections
 */
class Plugin_Collections_Base {

	protected $plugin_slug = 'dwspc';

	/**
	 * Get this party started.
	 *
	 * @return void
	 */
	public function init() {
		new Post_Types();
		new Bulk_Plugin_Actions();
	}

}