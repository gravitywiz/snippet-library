<?php
/**
 * Gravity Perks // Populate Anything // Conditionally Exclude Filter by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Experimental Snippet 🧪
 */
add_filter( 'gppa_object_type_query', function( $query, $args ) {
	// Update "123 to your form ID and "4" to your field ID that is being populated.
	if ( $args['field']->formId == 123 && $args['field']->id == 4 ) {
		foreach ( $query['where'] as &$where_group ) {
			foreach ( $where_group as &$where ) {
				// Update "Unsure" to the field value you wish to exempt a filter.
				if ( strpos( $where, 'Unsure' ) !== false ) {
					$where = null;
					unset( $where );
				}
			}
			$where_group = array_filter( $where_group );
		}
		$query['where'] = array_filter( $query['where'] );
	}
	return $query;
}, 10, 2 );
