<?php
/**
 * Gravity Perks // Inventory // Limit by Approved Entries Only
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * When using GravityView, only count "Approved" entries towards the inventory limit.
 */
add_filter( 'gpi_query', function( $query ) {
	global $wpdb;

	$query['join']  .= ' INNER JOIN wp_gf_entry_meta emgv ON emgv.entry_id = em.entry_id ';
	$query['where'] .= ' AND emgv.meta_key = "is_approved" AND emgv.meta_value = "1" ';

	return $query;
} );
