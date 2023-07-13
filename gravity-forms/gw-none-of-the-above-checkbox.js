/**
 * Gravity Wiz // Gravity Forms // "None of the Above" Checkbox
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/0f8de708790b4afd879bf0632efd7eae (Out of date)
 *
 * Use this snippet to enable a proper "None of the Above" option in your Checkbox fields. If any
 * other option is checked, the "None of the Above" option will be disabled. If the "None of the Above"
 * option is checked, all other options will be disabled.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Add a new choice to your Checkbox field to act as the "None of the Above" choice.
 *    This must be the last choice in the field.
 *
 * 3. Add "gw-none-of-the-above" to the Custom CSS Class field setting under the Appearance tab for
 *    any Checkbox field to which this should be applied.
 */
$( '.gw-none-of-the-above' ).each( function() {

	var $field  = $( this );
	var $last   = $field.find( '.gchoice:last-child input' );
	var $others = $field.find( 'input' ).not( $last );

	// If "None of the Above" choice is checked by default.
	if ( $( last ).prop( 'checked' ) ) {
		var $checkboxes = $field.find( 'input' ).not( $last )
		$checkboxes
			.prop( 'checked', false )
			.prop( 'disabled', true );
	}

	$last.on( 'click', function() {
		var $checkboxes = $field.find( 'input' ).not( $( this ) )
		if ( $( this ).is( ':checked' ) ) {
			$checkboxes
				.prop( 'checked', false )
				.prop( 'disabled', true );
		} else {
			$checkboxes.prop( 'disabled', false );
		}
	} );

	$others.on( 'click', function() {
		if ( $others.filter( ':checked' ).length ) {
			$last.prop( 'disabled', true );
		} else {
			$last.prop( 'disabled', false );
		}
	} );

} );
