/**
 * WARNING! THIS SNIPPET IS DEPRECATED. 🚧
 * Padding is automatically handled in Advanced Phone Field v1.1+.
 */
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

gform.addAction( 'gpapf_post_init', function( formId, fieldId, GPAPF ) {
	if ( formId == GFFORMID && fieldId == 4 ) {
		const iti = GPAPF.iti;

		GPAPF.$telInput.addEventListener( 'countrychange', gpapfAdjustInputPadding );
		GPAPF.$telInput.addEventListener( 'blur', gpapfAdjustInputPadding );
		gpapfAdjustInputPadding();

		function gpapfAdjustInputPadding() {
			if ( iti.options.separateDialCode ) {

				const selectedFlagWidth =
					iti.selectedFlag.offsetWidth || iti._getHiddenSelectedFlagWidth();

				// Add 8px of padding
				if ( iti.isRTL ) {
					iti.telInput.style.paddingRight = `${selectedFlagWidth + 8}px`;
				} else {
					iti.telInput.style.paddingLeft = `${selectedFlagWidth + 8}px`;
				}
			}
		}
	}
});
