/**
* Gravity Perks // Copy Cat // Copy as Comma-delimited List
* https://gravitywiz.com/documentation/gravity-forms-copy-cat/
*
* When copying multiple values to a single input, Copy Cat will join 
*
* Instructions:
*
* 1. Install this snippet with our free Custom JavaScript plugin.
*    https://gravitywiz.com/gravity-forms-custom-javascript/
*
* 2. Configure the snippet per the inline instructions.
*/
gform.addFilter( 'gppc_copied_value', function( value, $elem, data, sourceValues ) {
	// Update "4" to ID of the field that should be populated with commma-delimited values.
	if ( data.target == 4 ) {
		value = sourceValues.join( ', ' );	
	}
	return value;
} );
