/**
 * Gravity Wiz // Gravity Forms // Prevent Duplicate Selections
 * https://gravitywiz.com/
 *
 * Prevent duplicate selections in choice-based fields. Currently works with Checkbox, Radio Button, Drop Down and
 * Enhanced-UI-enabled Multi Select fields.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Add 'gw-prevent-duplicates' to the CSS Class Name setting for any field in which duplicate selections
 *    should be prevented.
 */
window.gform.addFilter( 'gplc_excluded_input_selectors', function( selectors ) {
	selectors.push( '.gw-disable-duplicates-disabled' );
	return selectors;
});

$inputs = $( '.gw-prevent-duplicates' ).find( 'input, select' );

$inputs.change( function( event, selected ) {
	gwDisableDuplicates( $( this ), $inputs, selected );
} );

$inputs.each( function( event ) {
	gwDisableDuplicates( $( this ), $inputs );
} );

function gwDisableDuplicates( $elem, $group, selected ) {

	// Some elements have a parent element (e.g. a <select>) that contains the actual elements (e.g. <option>) we want enable/disable.
	let $parent = $elem;

	if ( $elem.is( 'select' ) ) {
		// Multi Selects fields require Chosen to be enabled. It provides the `selected` variable which indicates which
		// option was selected/deselected.
		if ( selected ) {
			let value = selected.selected ? selected.selected : selected.deselected;
			$elem     = $elem.find( '[value="' + value + '"]' );
		} else {
			$elem = $elem.find( 'option:selected' );
		}
		// Note: This prevents selects from working with other field types.
		$group = $group.find( 'option' );
	}

	let value     = $elem.val();
	let $targets  = $group.not( $elem ).not( '.gplc-disabled' ).not( '.gpi-disabled' );
	let isChecked = $elem.is( ':checked' );

	// We use this to instruct Gravity Forms not to re-enable disabled duplicate options when
	// that option is revealed by conditional logic.
	let disabledClass = 'gf-default-disabled gw-disable-duplicates-disabled';
	let previousValue;

	// Only one choice can be selected in a Radio Button or Drop Down field while multiple choices
	// can be selected in a Checkbox or Multi Select field. This logic handles saving/retrieving the
	// previous value and re-enabling inputs/options with the previous value.
	if ( $elem.is( ':radio, option' ) && ! $parent.prop( 'multiple' ) ) {
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
		.prop( 'disabled', isChecked );

	// For Drop Down and Multi Selects, we need to loop through each field and select the first available option - and -
	// trigger Chosen to update the select box so newly disabled options are displayed as disabled.
	if ( $elem.is( 'option' ) ) {
		$filteredTargets.parents( 'select' ).each( function() {
			let $options = $( this ).find( 'option' );
			if ( $options.filter( ':selected:disabled' ).length ) {
				$options.not( ':disabled' ).first().prop( 'selected', true );
			}
			$( this ).trigger( 'chosen:updated' );
		} );
	}

	if ( isChecked ) {
		$filteredTargets.addClass( disabledClass );
	} else {
		$filteredTargets.removeClass( disabledClass );
	}

}
