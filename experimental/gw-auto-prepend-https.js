/**
 * Gravity Wiz // Gravity Forms // Auto-prepend HTTPS to URLs
 * https://gravitywiz.com/
 *
 * Auto-prepend "https://" to URLs in Website fields.
 */
// Update "1" to your field ID.
$( '#input_GFFORMID_1' )
	.on( 'focus', function() {
		if ( $( this ).val() === '' ) {
			$( this ).val( 'https://' );
		}
	} )
	.on( 'keyup', function() {
		if ( $( this ).val() === 'https:/' ) {
			$( this ).val( 'https://' );
		} else if ( $( this ).val().indexOf( 'https://' ) !== 0 ) {
			$( this ).val( 'https://' + $( this ).val() );
		}
	} );
