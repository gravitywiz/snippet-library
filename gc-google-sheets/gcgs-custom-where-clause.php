<?php
/**
 * Gravity Connect // Google Sheets // Custom Where Clause
 *
 * This snippet customizes the generation of `where` clauses for Google Sheets queries.
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gcgs_gppa_build_gviz_where_clause', function( $clause, $clauses, $value, $column_letter, $operator, $args ) {
	$conditions = array();

	// Loop over the $value array and create conditions.
	foreach ( $value as $v ) {
		if ( ! empty( $v ) ) {
			$conditions[] = sprintf( "lower(%s) = '%s'", $column_letter, strtolower( $v ) );
		}
	}

	// Implode the conditions array with ' OR ' to form the $clause.
	$clause = implode( ' OR ', $conditions );

	return $clause;
}, 10, 6 );
