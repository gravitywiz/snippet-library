/**
 * Gravity Perks // Limit Dates // Populate the New Maximum Date into Linked Date Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * When Field B’s maximum date is dependent on the selected date in Field A,
 * this snippet will automatically populate the maximum date into Field B.
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
gform.addAction( 'gpld_after_set_max_date', function( $input, date ) {
	$input.datepicker( 'setDate', date );
} );
