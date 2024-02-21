/**
 * Gravity Perks // Advanced Phone Field // Preserve Input's Default Padding
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * By default, the `intlTelInput` library sets 6px of padding between the flag and the content of
 * the input. Gravity Forms pads its inputs with 8px of padding.
 *
 * Since this isn't a setting in `intlTelInput`, we must override it with JavaScript.
 *
 * This snippet will automatically adjust the padding to match the input's default padding on
 * init and anytime a new country is selected.
 *
 * Note: This may not work if an input's default padding is not set in `px`.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Update the Phone field ID per the inline instructions.
 */
// Update "4" to your Phone field ID.
var $telInput = $( '#input_GFFORMID_4_raw' );

$telInput[0].addEventListener( 'countrychange', function() {
	gpapfAdjustInputPadding( this );
} );

gpapfAdjustInputPadding( $telInput[0] );

function gpapfAdjustInputPadding( element ) {
	// intlTelInput adds 6px of padding by default.
	var flagPadding = parseFloat( getComputedStyle( element ).paddingLeft ) - 6;
	element.style.paddingLeft = null;
	var basePadding = parseFloat( getComputedStyle( element ).paddingLeft );
	element.style.paddingLeft = flagPadding + basePadding + 'px';
}
