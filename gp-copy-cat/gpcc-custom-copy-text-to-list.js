/**
* Gravity Perks // Copy Cat // Copy Text Value to List Field's first row without removing other List Rows
* https://gravitywiz.com/documentation/gravity-forms-copy-cat/
*
* Instructions:
* 1. Install our free Custom Javascript for Gravity Forms plugin. 
*    Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
* 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
*/
gform.addFilter( 'gpcc_custom_copy', function( copyMode, id, sourceGroup, targetGroup, currentField ) {
	// Get the source text field value.
	var sourceValue = $(sourceGroup[0]).val();
	
	// Set the source text field value to the first row of list input.
	$( '.gfield_list_row_odd' ).find(':input').first().val( sourceValue );

	// Return copyMode as true indicating the default logic for this case is bypassed by the above overriden logic.
	return true;
});
