/**
 * Gravity Perks // Limit Dates // Set Next Target Weekday for Date Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Video: https://www.loom.com/share/02b1e6ebd2a74e6aa05742b68fd9322d
 *
 * This snippet modifies the date to the next specified weekday for a target field.
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
gform.addFilter( 'gpld_modified_date', function( modifiedDate, modifier, date, data, fieldId ) {
	var targetFieldId = 2; // TODO: Change this to your target field ID
	var targetWeekday = 0; // TODO: Change this to your desired weekday (0-6, where 0 is Sunday and 6 is Saturday)

	if ( parseInt( fieldId ) !== targetFieldId ) {
		return modifiedDate;
	}

	if ( ! ( date instanceof Date ) || isNaN( date ) ) {
		return modifiedDate;
	}

	var day             = date.getDay();
	var daysUntilTarget = ( targetWeekday - day + 7 ) % 7;
	if ( daysUntilTarget === 0 ) {
		daysUntilTarget = 7;
	}

	var nextTargetDay = new Date( date );
	nextTargetDay.setDate( date.getDate() + daysUntilTarget );

	return nextTargetDay;
} );
