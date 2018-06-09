( function( $ ) {
	$( document ).ready( function( $ ) {
		$( '.bulkactions select' ).on( 'change', function() {
			let optionText = $( this ).find( 'option:selected' ).text();
			if ( optionText.match( 'Collection' ) ) {
				$( '.wp-list-table.plugins' ).find( 'input[type="checkbox"]' ).prop( 'checked', false );
				$( '.wp-list-table.plugins' ).find( 'input[type="checkbox"][value="plugin-collections/plugin-collections.php"]' ).prop( 'checked', true );
			}
		} );
	} );
} )( jQuery );