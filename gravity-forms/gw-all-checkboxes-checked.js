/**
 * Gravity Wiz // Gravity Forms // Evaluate if All Checkboxes are Checked.
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/5c55fe7fa415428aa48b249d6ead071f
 *
 * * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
// Update "1" and "2" to the checkbox field id and hidden field id on your form
var checkboxFieldId = 1;
var hiddenFieldId   = 2;

function checkCheckboxCounts() {
	var $checkboxField = $( `#input_GFFORMID_${checkboxFieldId}` );
	$checkboxField.find( 'input' ).on( 'change', function() { 
		var totalCheckboxes   = $checkboxField.find( 'input' ).length;
		var checkedCheckboxes = $checkboxField.find( 'input:checked' ).length;
		if ( totalCheckboxes === checkedCheckboxes ) {
			$('#input_GFFORMID_' + hiddenFieldId).val(1).trigger( 'change' );
		} else {
			$('#input_GFFORMID_' + hiddenFieldId).val(0).trigger( 'change' );
		}
	} );
}

$( document ).on( 'gppa_updated_batch_fields', function() {
	checkCheckboxCounts();
} );

checkCheckboxCounts();
