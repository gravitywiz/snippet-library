<?php
/**
 * Gravity Connect // Google Sheets // Custom Query Build Arguments
 *
 * This snippet customizes the generation of query builder arguments for Google Sheets queries.
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gcgs_gppa_query_builder_args', function( $query_builder_args, $args, $object ) {
	/** @var string|string[] */
	$filter_value = null;

	/** @var array */
	$filter = null;

	/** @var int */
	$filter_group_index = null;

	/** @var string */
	$property_id = null;

	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	extract( $args );

	$column_letter = $object->get_column_letter( $args['primary_property_value'], $property_id );

	if ( ! empty( $filter_value ) ) {
		$conditions = array();
		// Loop over the $value array and create conditions.
		foreach ( $filter_value as $v ) {
			if ( ! empty( $v ) ) {
				$conditions[] = sprintf( "lower(%s) = '%s'", $column_letter, strtolower( $v ) );
			}
	}

		// Implode the conditions array with ' OR ' to form the $clause.
		$query_builder_args['where'][ $filter_group_index ][ $filter_group_index ] = implode( ' OR ', $conditions );
	}

	return $query_builder_args;
}, 10, 3);
