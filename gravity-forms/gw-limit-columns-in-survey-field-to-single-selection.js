/**
 * Gravity Wiz // Gravity Forms // Limit Columns in Survey Field to Single Selection
 * https://gravitywiz.com/
 * 
 * Video: https://www.loom.com/share/cf1f7f5bb254430c8ae939d5d4b9ea20
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
// Update to the Survey field ID on your form.
const fieldId = '1';
document.addEventListener( 'change', function ( event ) {

	if ( event.target.type == 'radio' ) {
		const selectedRadio = event.target;

		const field = selectedRadio.closest( `#field_${GFFORMID}_${fieldId}` );
		// Exit if the radio button is not within the target field.
		if ( !field ) {
			return;
		}

		// Get the column ID of the radio button
		const ariaLabels = selectedRadio.getAttribute( 'aria-labelledby' ).split(' ');
		const columnId = ariaLabels.find(label => label.startsWith( 'likert_col_' ));
		
		if (columnId) {
			// Find all radio buttons in the same table within the specified field/
			const table = selectedRadio.closest( 'table' );
			const radiosInColumn = table.querySelectorAll( `input[type="radio"][aria-labelledby*="${columnId}"]` );

			// Deselect all other radio buttons in the same column/
			radiosInColumn.forEach(radio => {
				if (radio != selectedRadio) {
					radio.checked = false;
				}
			});
		}
	}
});
