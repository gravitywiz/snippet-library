/**
 * Gravity Wiz // Gravity Forms // Prevent Duplicate Selections
 * https://gravitywiz.com/
 * 
 * Prevent duplicate selections in choice-based fields. Currently works with Checkbox and
 * Radio Button fields with plans to support all choice-based fields (e.g. Drop Downs & Multi Selects).
 * 
 * For Drop Down field support, use this snippet: 
 * https://github.com/gravitywiz/snippet-library/blob/master/experimental/gw-prevent-duplicate-drop-down-selections.js
 * 
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Add 'gw-prevent-duplicates' to the CSS Class Name setting for any field in which duplicate selections
 *    should be prevented.
 */
$inputs = $( '.gw-prevent-duplicates' ).find( 'input' );

$inputs.click( function() {
	gwDisableDuplicates( $( this ), $inputs );
} );

$inputs.each( function() {
	gwDisableDuplicates( $( this ), $inputs );
} );

function gwDisableDuplicates( $elem, $group ) {
	
	let value     = $elem.val();
	let $targets  = $group.not( $elem );
	let isChecked = $elem.is( ':checked' );
	// We use this to instruct Gravity Forms not to re-enable disabled duplicate options when
	// that option is revealed by conditional logic.
	let disabledClass = 'gf-default-disabled';
	let previousValue;
	
	// Only one choice can be selected in a Radio Button field while multiple choices
	// can be selected in a Checkbox field. This logic handles saving/retrieving the previous
	// value and re-enabling inputs with the previous value.
	if ( $elem.is( ':radio' ) ) {
		previousValue = $elem.parents( '.gfield' ).data( 'previous-value' );
		$elem.parents( '.gfield' ).data( 'previous-value', $elem.val() );
		if ( previousValue ) {
			$targets
			.filter( '[value="{0}"]'.format( previousValue ) )
			.prop( 'disabled', false )
			.removeClass( disabledClass );
		}
	}
	
	let $filteredTargets = $targets
		.filter( '[value="{0}"]'.format( value ) )
		.prop( 'disabled', $elem.is( ':checked' ) );
	
	if ( isChecked ) {
		$filteredTargets.addClass( disabledClass );
	} else {
		$filteredTargets.removeClass( disabledClass );
	}
	
}
