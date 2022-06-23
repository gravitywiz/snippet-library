/**
 * Gravity Wiz // Gravity Forms // Drop Down Lockouts
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/e07ca5170660413a9804f146b2b569c0
 *
 * Only allow a selection in a single Drop Down field, disabling other Drop Down fields in that group.
 */
var $selects = $( '#input_GFFORMID_1, #input_GFFORMID_2, #input_GFFORMID_3' );
$selects.change( function() {
	if ( $( this ).val() ) {
		$selects.not( $( this ) ).val( '' ).prop( 'disabled', true );
		$( this ).prop( 'disabled', false );
	} else {
		$selects.prop( 'disabled', false );
	}
} );
