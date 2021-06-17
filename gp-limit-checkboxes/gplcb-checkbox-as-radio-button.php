$( '.gplcb-cb-as-radio' ).find( 'input[type="checkbox"]' ).change( function() {
	$( this ).parents( '.gfield' ).find( 'input[type="checkbox"]' ).prop( 'checked', false );
	$( this ).prop( 'checked', true );
} );
