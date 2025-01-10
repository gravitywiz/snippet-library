/**
 * Gravity Perks // Limit Dates // Hide Datepicker on Manual Input
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Hide the datepicker if the user begins to manually enter a date via the Datepicker's associated input.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
$( '.datepicker' ).on( 'keypress', function() {
	$( this ).datepicker( 'hide' );
} );
