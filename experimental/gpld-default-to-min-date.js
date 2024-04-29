/**
 * Gravity Perks // GP Limit Dates // Default value to the minimum date allowed
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Instructions:
 * 1. Install our free Custom Javascript for Gravity Forms plugin.
 *    Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 * 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 * 3. Update `formId` variable accordingly
 */
var fieldId = 5;

/* Not ideal to use the setTimeout, but it helps make sure the datepicker is ready to go. */
setTimeout( function () {
	jQuery( '#input_GFFORMID_' + fieldId )
		.val( GPLimitDates.getDateValue( '', 'minDate', fieldId, GFFORMID, GPLimitDatesDataGFFORMID ) )
		.change();
} );
