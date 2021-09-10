/**
* Gravity Perks // Copy Cat // Copy Product Value Without the Price
* https://gravitywiz.com/documentation/gravity-forms-copy-cat/
*
* By default, choice-based Product fields will copy a raw value that includes 
* both the selected value and the price of the selection option(s). 
* Use this snippet to copy only the value and exclude the price.
*
* Instructions:
* 1. Install our free Custom Javascript for Gravity Forms plugin. 
* Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
* 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
* 3. Update the target field ID within the snippet.
*/
gform.addFilter( 'gppc_copied_value', function( value, $elem, data ) {
	// Update "1" to the ID of the field being copied to.
	if( data.target == 1 && value ) {
		value = value.split( '|' )[0];
	}
	return value;
} );
