<?php
/**
 * Gravity Perks // Populate Anything // Search by Multiple Values w/ Contains
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * This snippet extends the "contains" operator to support comparisions where a multi-value field (like a Multi Select)
 * is the needle. Typically, the "in" operator would be used for this; however, it has the limitation of only being able
 * to match full values rather than checking if any of the needles are contained in the haystack.
 */
add_filter( 'gppa_where_clause', function( $where_clause, $object, $table, $column, $operator, $value ) {

	static $_processing;

	if ( $_processing || ! is_array( $value ) || $operator !== 'contains' ) {
		return $where_clause;
	}

	$_processing = true;

	$clauses = array();
	foreach ( $value as $_value ) {
		$clauses[] = $object->build_where_clause( $table, $column, $operator, $_value );
	}

	$_processing = false;

	return implode( "\nOR ", $clauses );
}, 10, 6 );
