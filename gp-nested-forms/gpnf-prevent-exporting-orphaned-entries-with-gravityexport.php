/**
 * Gravity Perks // Nested Forms // Prevent Exporting Orphaned Entries with GravityExport
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Prevents exporting orphaned entries when using GravityExport with Nested Forms. Applies to all forms by default.
 *
 * Instructions
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 *
 * 2. Profit.
 */
add_filter( 'gfexcel_output_rows', function( $rows ) {
	for ( $i = count( $rows ) - 1; $i >= 0; $i-- ) {
		foreach ( $rows[ $i ] as $row_value ) {
			if ( $row_value->getField()->id == 'gpnf_entry_parent' && ! is_numeric( $row_value->getValue() ) ) {
				unset( $rows[ $i ] );
			}
		}
	}
	return $rows;
} );
