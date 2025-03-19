/**
 * Gravity Perks // Limit Dates // Prevent Exceptions Between Linked Dates Fields
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Video: https://www.loom.com/share/5914ba498bcb4616bac755d4eb318ed1
 *
 * Limit Dates allows you to "link" Date fields by setting the minimum (or maximum) value of one Date
 * field based on the date selected in another. Unfortunately, it does not prevent date range selections
 * that may contain excepted dates.
 *
 * This snippet adds support for this concept.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Set the **Maximum Date** of your end Date field to the start Date field, then set the modifier value
 *    to `next exception`.
 */
gform.addFilter( 'gpld_modified_date', function( modifiedDate, modifier, date, data, fieldId ) {
	if ( modifier === 'next exception' ) {
		debugger;
		if ( ! ( date instanceof Date ) || isNaN( date.getTime() ) ) {
			return null;
		}
		modifiedDate = gwfindClosestFutureDate( data[ fieldId ].exceptions, date );
	}
	return modifiedDate;
} );

function gwfindClosestFutureDate(dates, baseDate ) {
    const now = new Date( baseDate );
    return dates
        .map(dateStr => new Date(dateStr)) // Convert strings to Date objects
        .filter(date => date > now) // Keep only future dates
        .sort((a, b) => a - b) // Sort dates in ascending order
        [0] || null; // Return the closest future date, or null if there are none
}
