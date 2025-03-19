<?php
/**
 * Gravity Perks // Inventory // Limit by Field Value Match
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Only count an entry towards the inventory limit if a specific field matches a specific value.
 */
// Update "123" to your form ID and "4" to your Inventory-enabled field ID.
add_filter( 'gpi_query_123_4', function ( $query ) {
	global $wpdb;

	// Update "5" to the ID of the field you would like to match and "xyz" to the value you would like to match.
	$query['where'] .= ' AND em_by_field.meta_key = "5" AND em_by_field.meta_value = "xyx" ';
	$query['join']  .= " INNER JOIN {$wpdb->prefix}gf_entry_meta em_by_field ON em_by_field.entry_id = em.entry_id ";

	return $query;
} );
