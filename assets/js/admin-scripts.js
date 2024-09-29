( function () {

	/**
	 * Make sure the Plugin Collections plugin is checked if switching to a collection.
	 *
	 * WordPress requires at least one item checked for a bulk action to process.
	 */
	document.addEventListener("DOMContentLoaded", (event) => {
		const selections = document.querySelectorAll( '.bulkactions select' );
		const listTableCheckboxes = document.querySelectorAll( '.wp-list-table.plugins input[type="checkbox"]' );

		selections.forEach( selection => {
			selection.addEventListener('change', (e) => {
				e.preventDefault();
				const optionText = e.target.options[e.target.selectedIndex].text;

				if ( optionText?.match( 'Collection' ) ) {
					listTableCheckboxes.forEach( checkbox => {
						checkbox.checked = "plugin-collections/plugin-collections.php" === checkbox.value;
					} );
				}
			});
		});
	} );

} )();
