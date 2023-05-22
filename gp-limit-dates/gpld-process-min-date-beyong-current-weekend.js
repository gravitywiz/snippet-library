/**
 * Gravity Perks // Limit Dates // Process Minimum date beyound current weekend
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Pad the Minimum Date Beyond the Current Weekend
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *	  Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
gform.addAction( 'gpld_after_set_min_date', function( $input, date, selectedDate, fieldId, formId, data ) {
    // Update "3" to your target Date field ID.
	var targetFieldId = 3;
	var calculatedDay = (new Date(date)).getDay();
	var selectedDay = (new Date(selectedDate)).getDay();
	var minDateMod = data[targetFieldId].minDateMod;
	
	if ( minDateMod ) {
		const result = extractDateModifier(minDateMod);
		var dayPadding = 0;
		if ( result.stringValue === 'day' || result.stringValue === 'days' ) {	
			dayPadding = calculateDayPadding(selectedDay, result.dayValue);

			var calculateDate = new Date(date);
			calculateDate.setDate(calculateDate.getDate() + dayPadding);
			$input.datepicker( 'option', 'minDate', calculateDate );
			$input.datepicker( 'refresh' );
		}
	}
} );

function extractDateModifier( inputString ) {
	const pattern = /([+-]?\d+)\s*(day|days|week|weeks|year|years)/i;
	const match = inputString.match(pattern);
	if (match) {
		const dayValue = parseInt(match[1]);
		const stringValue = match[2].toLowerCase();
		return {
			dayValue: dayValue,
			stringValue: stringValue
		};
	} else {
		return null;
	}
}

function calculateDayPadding( selectedDay, addDayValue ) {
	var totalValue = selectedDay + addDayValue;
	// Adjust the total value to skip weekends
	while (totalValue > 7) {
		totalValue = (totalValue % 7) || 7;
	}

	var dayPadding = totalValue < selectedDay ? 2 : 0;
	if (totalValue == 7) {
		dayPadding = 1;
	}

	return dayPadding;
}
