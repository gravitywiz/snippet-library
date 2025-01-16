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
// If you want to exclude a column from this behavior, set the column label here.
// If you don't want it to exclude any column, set it to an empty string.
const exceptionColumnLabel = 'Not Available';

$( document ).on( 'change', `#field_${GFFORMID}_${fieldId} input[type="radio"]`, function () {
	const $selectedRadio = $(this);
	const $td            = $selectedRadio.closest('td');

	// Skip logic if the column label matches the exception label.
	if ( exceptionColumnLabel && $td.data('label') == exceptionColumnLabel ) {
		return;
	}

	const ariaLabels = $selectedRadio.attr( 'aria-labelledby' ).split( ' ' );
	const columnId   = ariaLabels.find( label => label.startsWith( 'likert_col_' ) ) ;

	if ( columnId ) {
		// Find all radio buttons in the same column.
		const $table          = $selectedRadio.closest( 'table' );
		const $radiosInColumn = $table.find( `input[type="radio"][aria-labelledby*="${columnId}"]` );

		// Deselect all other radio buttons in the same column.
		$radiosInColumn.not( $selectedRadio ).prop( 'checked', false );
	}
});
