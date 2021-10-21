/**
 * Gravity Wiz // Gravity Forms // Check All Checkboxes by Default
 * https://gravitywiz.com/
 *
 * @see Form Export: https://gwiz.io/3DyGUrR
 */
// Update "4" and "5" to Checkbox field IDs which should be selected by default.
$.each( [ 4, 5 ], function( index, fieldId ) {
	$checkboxes = $( '#field_GFFORMID_{0}'.format( fieldId ) ).find( 'input' );
	if ( $checkboxes.filter( ':checked' ).length === 0 ) {
		$checkboxes.prop( 'checked', true );
	}	
} );
