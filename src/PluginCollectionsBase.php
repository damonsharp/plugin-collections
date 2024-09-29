<?php

namespace DWSPluginCollections;

/**
 * Class PluginCollectionsBase
 *
 * @package DWSPluginCollections
 */
class PluginCollectionsBase {

	protected $plugin_slug = 'dwspc';

	/**
	 * Get this party started.
	 *
	 * @return void
	 */
	public function init() {
		$data_structures  = new DataStructures();
		$collections_meta = new CollectionsMeta();
		new PostTypes( $data_structures, $collections_meta );
		new BulkPluginActions( $collections_meta );
	}

}
