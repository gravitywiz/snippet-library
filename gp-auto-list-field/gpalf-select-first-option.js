/**
 * Gravity Perks // Auto List Field // Select First Option in List Field Selects
 * https://gravitywiz.com/documentation/gravity-forms-auto-list-field/
 *
 * By default, when using the [`gform_input_column`][1] filter to convert a List field column
 * from an input into a select, no option is selected after adding a new row. This differs from
 * the behavior of the first row which defaults to the first option being selected.
 *
 * This snippet aligns the behavior for newly added rows to also select the first option.
 * Additionally, this resolves an issue where Gravity Forms threw an error after adding a new
 * row to a List field that contained a select when that List field was used in a calculation
 * formula via Auto List Field.
 *
 *   [1]: https://docs.gravityforms.com/gform_column_input/#examples
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter( 'gform_list_item_pre_add', function( $clone ) {
	$clone.find( 'select' ).find( 'option:first' ).prop( 'selected', true );
	return $clone;
} );
