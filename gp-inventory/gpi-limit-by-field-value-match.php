<?php
/**
 * Gravity Perks // Inventory // Limit by Field Value Match
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Only count an entry towards the inventory limit if a specific field matches a specific value.
 */
add_filter( 'gpi_query_330_3', function ( $query ) {

	// Update "1" to the field ID of the field you would like to match and "xyz" to the value you would like to match.
	$query['where'] .= ' AND em_by_field.meta_key = "1" AND em_by_field.meta_value = "xyx" ';
	$query['join']  .= ' INNER JOIN wp_gf_entry_meta em_by_field ON em_by_field.entry_id = em.entry_id ';

	return $query;
} );
