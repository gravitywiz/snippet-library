/**
 * Gravity Perks // Advanced Select // Enable "Add New" Option
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Enable Advanced Select's "Add New" option that allows users to create new items that aren't in the initial list of options.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Enable "Allow field to be populated dynamically" option under your Advanced-Select-enabled field's Advanced settings.
 *    NOTE: This step is not required if you are dynamically populating choices via Populate Anything.
 */
gform.addFilter( 'gpadvs_settings', function( settings, gpadvs ) {
	
	if ( gpadvs.formId != GFFORMID ) {
		return settings;	
	}

	settings.create = true;

	/**
     * Uncomment the below code to customize the display of the "Add New" option.
	 */
	// if ( ! settings.render ) {
	// 	settings.render = {};
	// }
	
	// settings.render.option_create = function( data, escape ) {
	// 	return '<div class="create">Add <strong>' + escape(data.input) + '</strong>&hellip;</div>';
	// }
	
	return settings;
} );
