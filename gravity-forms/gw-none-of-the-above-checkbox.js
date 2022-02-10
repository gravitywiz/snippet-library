/**
 * Gravity Wiz // Gravity Forms // "None of the Above" Checkbox
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/0f8de708790b4afd879bf0632efd7eae
 *
 * Use this snippet to enable a proper "None of the Above" option in your Checkbox fields. If any
 * other option is checked, the "None of the Above" option will be disabled. If the "None of the Above"
 * option is checked, all other options will be disabled.
 */
// Update "1" to your Checkbox field ID.
var $field  = $( '#field_GFFORMID_1' );
var $last   = $field.find( '.gchoice:last-child input' );
var $others = $field.find( 'input' ).not( $last );

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
