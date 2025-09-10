/**
 * Gravity Perks // Copy Cat // Copy Multiple Checkbox Fields to a List Field
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 *
 * Copy checked checkbox values from multiple Checkbox fields to a single List field.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Update snippet based on inline instructions.
 */
gform.addFilter( 'gpcc_field_group', function( $group, field, groupType, $field ) {
	// Update "3.1" to your List field ID and column number.
	if ( groupType !== 'source' || field.target !== '3.1' ) {
		return $group;
	}
	// Update "1" to your first Checkbox field ID and "2" to your second Checkbox field ID.
	$group = $( '#field_GFFORMID_1, #field_GFFORMID_2' ).find( 'input:checked' );
	return $group;
} );
