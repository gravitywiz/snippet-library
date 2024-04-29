/**
* Gravity Perks // Copy Cat // Selectively Copy Based on Radio Button Choice
* https://gravitywiz.com/documentation/gravity-forms-copy-cat/
*
* By default, the value of a source field is always fixed.
* Use this snippet to target different source fields by specifying the source field IDs as choices of a radio button trigger field.
*
* Instructions:
* 1. Install our free Custom Javascript for Gravity Forms plugin.
*    Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
* 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
*/
gform.addFilter( 'gpcc_copied_value', function( value, $elem, data ) {

    // Get the radio button trigger field ID.
	var trigger = data['trigger'];

    // Check on all the radio button choices.
	var radioButtons = $( 'input[id^=choice_GFFORMID_' + trigger + ']' );

    // Loop and check which field should be used as the value to copy (based on the radio button selection)
	$.each( radioButtons, function (i, radio) {
		if ( radio.checked ) {
			var newSourceId = $( radio ).val();
			var newValue = $( '#input_GFFORMID_' + newSourceId ).val();
			value = newValue;
		}
	});

    return value;
} );
