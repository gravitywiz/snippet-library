/**
 * Gravity Perks // GP Limit Checkboxes // Set Max Limit by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-limit-checkboxes/
 * 
 * This javascript snippet sets the max limit of a Checkbox field using the value in a field on the form. 
 */
gform.addFilter( 'gplc_group', function( group, fieldId, $elem, gplc ) {

	var formId = 123,
		  checkboxFieldId = 4,
		  maxFieldId = 5;

	var $maxField = $( '#input_{0}_{1}'.format( formId, maxFieldId ) );

	if ( $elem.parents( '.gfield' ).attr( 'id' ) === 'field_{0}_{1}'.format( formId, checkboxFieldId ) ) {
		group.max = $maxField.val() ? $maxField.val() : 0;
		// Only bind our event listener once.
		if ( ! $maxField.data( 'gplcIsBound' ) ) {
			// If our max field value changes, reset the checkboxes and reinitialize GPLC.
			$maxField.on( 'change', function() {
				$( '#field_{0}_{1}'.format( formId, checkboxFieldId ) ).find( 'input' ).prop( 'checked', false );
				gplc.handleCheckboxClick( $elem );
			} );
			$maxField.data( 'gplcIsBound', true );
		}
	}

	return group;
} );
