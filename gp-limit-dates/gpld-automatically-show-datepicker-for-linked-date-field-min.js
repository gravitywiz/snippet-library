/**
 * Gravity Perks // Limit Dates // Automatically Show Datepicker for Linked Date Field (Minimum Date)
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * When a date selected in Field A modifies the minimum date in Field B,
 * this snippet will automatically open the datepicker in Field B after
 * the date has been selected in Field A.
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
gform.addAction( 'gpld_after_set_min_date', function( $input, date ) { 
	$input.datepicker( 'show' );
} );
