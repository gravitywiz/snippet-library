/**
 * Gravity Wiz // Gravity Forms // Check All Checkboxes by Default
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/69c4a8905b934a2f92681c751f171a25
 *
 * * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 *
 * @see Form Export: https://gwiz.io/3DyGUrR
 */
// Update "4" and "5" to Checkbox field IDs which should be selected by default.
$.each( [ 4, 5 ], function( index, fieldId ) {
	$checkboxes = $( '#field_GFFORMID_{0}'.gformFormat( fieldId ) ).find( 'input' );
	if ( $checkboxes.filter( ':checked' ).length === 0 ) {
		$checkboxes.prop( 'checked', true );
	}
} );
