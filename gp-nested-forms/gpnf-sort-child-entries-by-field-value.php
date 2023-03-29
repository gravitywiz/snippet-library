<?php
/**
 * Gravity Perks // Nested Forms // Sort Child Entries by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Sort child entries by a field value (e.g. name, number, date, etc).
 *
 * Warning: This does not work for child entries being displayed inside a Nested Form field on the parent form.
 * Currently, this is limited to the Nested Entries Simple template but can be easily extended to support other
 * post-submission templates. Let us know what you need!
 */
// Update "123 to your parent form ID and "4" to your Nested Form field ID.
add_filter( 'gpnf_template_args_123_4', function( $args ) {
	// Update "5" to the ID of the child field by which you would like to sort.
	$field_id = 5;
	$order    = 'asc'; // 'asc' or 'desc'
	if ( $args['template'] === 'nested-entries-detail-simple' && $args['field']->is_gravityview() ) {
		usort( $args['entries'], function( $a, $b ) use ( $field_id, $order ) {
			$first  = rgar( $a, $field_id );
			$second = rgar( $b, $field_id );
			if ( $first == $second ) {
				return 0;
			}
			if ( $order === 'asc' ) {
				return ( $first < $second ) ? -1 : 1;
			} else {
				return ( $first > $second ) ? -1 : 1;
			}
		} );
	}
	return $args;
} );
