/**
 * Gravity Perks // Limit Dates // Populate the New Maximum Date into Linked Date Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * When Field B’s maximum date is dependent on the selected date in Field A, 
 * this snippet will automatically populate the maximum date into Field B.
 */
if( window.gform ) {
	gform.addAction( 'gpld_after_set_max_date', function( $input, date ) {
		$input.datepicker( 'setDate', date );
	} );
}
