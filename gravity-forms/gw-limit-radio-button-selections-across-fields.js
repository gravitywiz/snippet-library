/**
 * Gravity Wiz // Gravity Forms // Limit Radio Button Selections across Multiple Fields.
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/3d42f3c88ab14f23ad63d491bb77bac4
 *
 * * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
// Update radio button field ids to target
var fieldIds = [1, 3, 4];

// Function to clear the same value in other fields
function clearSameValue( changedFieldId, selectedValue ) {
	$.each( fieldIds, function( index, fieldId ) {
		if ( fieldId !== changedFieldId ) {
			var $otherField = $( 'input[name="input_' + fieldId + '"][value="' + selectedValue + '"]' );
			if ( $otherField.prop( 'checked' ) ) {
				$otherField.prop( 'checked', false );
			}
		}
	} );
}

// Attach change event listeners to the radio buttons
$.each( fieldIds, function( index, fieldId ) {
	$( 'input[name="input_' + fieldId + '"]' ).on( 'change', function() {
		var selectedValue = $( this ).val();
		clearSameValue( fieldId, selectedValue );
	} );
});
