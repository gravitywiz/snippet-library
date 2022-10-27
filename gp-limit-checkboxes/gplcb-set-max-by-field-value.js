/**
 * Gravity Perks // GP Limit Checkboxes // Set Max Limit by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-limit-checkboxes/
 * 
 * Set the max number of checkboxes that can be checked in a Checkbox field based
 * on the value entered/selected in another field.
 * 
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 2. Configure based on the inline instructions.
 */
gform.addFilter( 'gplc_group', function( group, fieldId, $elem, gplc ) {

	// Update "3" to the ID of your Checkbox field.
	var checkboxFieldId = 3;
	
	// Update "4" to the ID of your field whose value whould be used to set the max checkbox limit.
	var maxFieldId = 4;

	if ( group.fields.indexOf( checkboxFieldId ) === -1 ) {
		return group;
	}
	
	var $maxField = $( '#input_{0}_{1}'.format( GFFORMID, maxFieldId ) );
	var isRadio   = $maxField.hasClass( 'gfield_radio' );

	if ( isRadio ) {
		$maxField = $maxField.find( 'input' );
		group.max = Math.max( 0, parseInt( $maxField.filter( ':checked' ).val() ) );
	} else {
		group.max = $maxField.val() ? $maxField.val() : 0;
	}

	// Only bind our event listener once.
	if ( ! $maxField.data( 'gplcIsBound' ) ) {
		// If our max field value changes, reset the checkboxes and reinitialize GPLC.
		$maxField.on( 'change', function() {
			$( '#field_{0}_{1}'.format( GFFORMID, checkboxFieldId ) ).find( 'input' ).prop( 'checked', false );
			gplc.handleCheckboxClick( $elem );
		} );
		$maxField.data( 'gplcIsBound', true );
	}

	return group;
} );
