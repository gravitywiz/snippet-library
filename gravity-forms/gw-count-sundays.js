/**
 * Gravity Wiz // Gravity Forms // Count Number of Sundays Between Two Dates
 * https://gravitywiz.com/path/to/article/
 *
 * Experimental Snippet 🧪
 *
 * Use this snippet to count the number of Sundays between a start and end date, including those dates themselves.
 *
 * Note: This is a JavaScript snippet and there is no server-side validation to ensure that the calculated value
 * has not been tampered with prior to submission.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Update the required field IDs to match your own by following the inline instructions.
 */
// Update "1" with the ID of your start Date field.
const startDateField = document.getElementById('input_GFFORMID_1');

// Update "2" with the ID of your end Date field.
const endDateField = document.getElementById('input_GFFORMID_2');

// Update "3" with the ID of your Sunday count field.
const countField = document.getElementById('input_GFFORMID_3');

function calculateSundays() {
	const startDate = new Date(startDateField.value);
	const endDate = new Date(endDateField.value);
	let sundays = 0;

	// Loop through the dates and count Sundays
	while (startDate <= endDate) {
		if (startDate.getDay() === 0) { // Sunday is represented by 0 in JavaScript's getDay() function
			sundays++;
		}
		startDate.setDate(startDate.getDate() + 1); // Move to the next day
	}

	return sundays;
}
function updateSundayCount() {
	countField.value = calculateSundays();
}

// Update Sunday count each time a date is manually entered.
startDateField.addEventListener('change', updateSundayCount);
endDateField.addEventListener('change', updateSundayCount);

// Update Sunday count each time a date is selected via the Datepicker.
gform.addFilter( 'gform_datepicker_options_pre_init', function( optionsObj, formId, fieldId ) {
	var origOnSelect = optionsObj.onSelect;
	optionsObj.onSelect = function( value, dpObject ) {
		if ( origOnSelect ) {
			origOnSelect();
		}
		updateSundayCount()
	}
	return optionsObj;
} );
