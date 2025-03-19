/**
 * Gravity Perks // File Upload Pro // Enforce Minimum Width or Height
 * https://gravitywiz.com/path/to/article/
 *
 * By default, the minimum width *and* height will be enforced. With this snippet you set a minimum width and enforce that for the width *or* height.
 * For example, if you specify a minimum width of 1200px, this snippet would accept an image that is either 1200px wide or 1200px tall.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
window.gform.addFilter( 'gpfup_meets_minimum_requirement', function ( meetsMinimum, imageSize, formId, fieldId, GPFUP ) {
	if ( imageSize.width > GPFUP.minWidth  || imageSize.height > GPFUP.minWidth ) {
		return true;
	}
	return meetsMinimum;
} );
