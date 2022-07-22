/**
 * Gravity Perks // Limit Dates // Populate the New Minimum Date into Linked Date Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * When Field B's minimum date is dependent on the selected date in Field A,
 * automatically populate the minimum date into Field B.
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
if( window.gform ) {
    gform.addAction( 'gpld_after_set_min_date', function( $input, date ) {
        $input.datepicker( 'setDate', date );
    } );
}
