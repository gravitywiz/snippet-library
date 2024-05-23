/**
 * Gravity Perks // Advanced Select // Clear Search on Selection
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Clear the search term(s) after a selection has been made.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter( 'gpadvs_settings', function( settings, gpadvs ) {
	if ( gpadvs.formId == GFFORMID ) {
		settings.onItemAdd = function(){
			this.setTextboxValue('');
			this.close();
		};
	}
	return settings;
} );
