/**
 * Gravity Perks // Advanced Phone Field // Enable Country Search
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * By default, the county search feature is disabled. Use this snippet to enable it.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter( 'gpapf_intltelinput_options', function( options, formId, fieldId, instance ) {
	options.countrySearch = true;
	return options;
});
