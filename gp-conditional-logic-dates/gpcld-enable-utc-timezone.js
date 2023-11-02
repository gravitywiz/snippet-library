/**
 * WARNING! THIS SNIPPET IS DEPRECATED. 🚧
 * This snippet has been replaced by the `gpcld_use_visitor_timezone` filter.
 * https://gravitywiz.com/documentation/gpcld_use_visitor_timezone/
 */
/**
 * Gravity Perks // Conditional Logic Dates // Adjust User's Local Time to UTC
 * https://gravitywiz.com/documentation/gravity-forms-conditional-logic-dates/
 *
 * Instructions:
 * 1. Install our free Custom Javascript for Gravity Forms plugin. 
 *    Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 * 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
gform.addFilter( 'gpcld_enable_utc_timezone', function( enable ) {
	return true;
} );
