/**
 * Gravity Perks // File Upload Pro // Enforce Exact Width or Height
 * https://gravitywiz.com/path/to/article/
 *
 * By default, the exact exact width *and* height settings applying cropping for conformity.
 * This snippet enforces exact dimensions and any image other the specified width and height is rejected.
 * The dimensions which are needed to be applied for exact match must be saved on Min Dimensions settings.
 *
 * Instruction Video: https://www.loom.com/share/4b28e2dbdbbb4b399b7220d3d77d71f5
 *
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
window.gform.addFilter( 'gpfup_meets_minimum_requirement', function ( meetsMinimum, imageSize, formId, fieldId, GPFUP ) {
	if ( imageSize.width == GPFUP.minWidth && imageSize.height == GPFUP.minHeight ) {
		return true;
	}
	return false;
} );

window.gform.addFilter( 'gpfup_strings', function( strings, formId, fieldId ) {
	// REPLACE 1 with the field id of your File Upload Pro field
	if ( formId != GFFORMID && fieldId == 1 ) {
		return strings;
	}

	// Alter the message, if needed.
	strings.does_not_meet_minimum_dimensions = 'This image does not meet the exact dimensions: {minWidth}x{minHeight}px.';
	return strings;
} );
