/**
 * Gravity Perks // Limit Dates // Populate the New Minimum Date into Linked Date Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * When Field B's minimum date is dependent on the selected date in Field A,
 * automatically populate the minimum date into Field B.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
const sourceFieldId = 25; // Replace with the ID of the source field (Field A)
document.addEventListener( 'gform/post_render', ( event ) => {
	const $field = jQuery( `#input_GFFORMID_${sourceFieldId}` );
	const value  = $field.val();
	if ( value ) {
		requestAnimationFrame( function(){
			$field.trigger( 'input' ).trigger( 'change' );
		});
	}
});

gform.addAction( 'gpld_after_set_min_date', function( $input, date ) {
	$input.datepicker( 'setDate', date );
} );
