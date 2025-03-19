/**
 * Gravity Perks // Advanced Select // Select on Tab
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Select the currently focused item from the Advanced Select menu when pressing the tab button.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter( 'gpadvs_settings', function( settings, gpadvs ) {
	if ( gpadvs.formId == GFFORMID ) {
		settings.selectOnTab = true;
	}
	return settings;
} );
