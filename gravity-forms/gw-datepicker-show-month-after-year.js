/**
 * Gravity Wiz // Gravity Forms // Datepicker Show Month After Year
 * https://gravitywiz.com/
 *
 * Configure datepicker to display month after the year in the datepicker UI.
 *
 * Instruction Video: 
 * 
 * https://www.loom.com/share/924747fb8cbd41869a79177843cd6f44
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 * 
 * 2. This snippet will automatically apply to all Date fields in your forms.
 */
gform.addFilter( 'gform_datepicker_options_pre_init', function ( options, formId, fieldId ) {
	options.showMonthAfterYear = true;
	return options;
});
