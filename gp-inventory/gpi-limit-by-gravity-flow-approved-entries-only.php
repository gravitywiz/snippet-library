<?php
/**
 * Gravity Perks // Inventory // Limit by Gravity Flow Approved Entries Only
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * When using Gravity Flow with an "Approval" step, only count "Approved" entries towards the inventory limit.
 */
// Update "123" to your form ID.
add_filter( 'gpi_query_123', function( $query ) {
	global $wpdb;

	$query['join']  .= ' INNER JOIN wp_gf_entry_meta emgflow ON emgflow.entry_id = em.entry_id ';
  // Update "4" to the ID of your Gravity Flow Approval step feed.
	$query['where'] .= ' AND emgflow.meta_key = "workflow_step_status_4" AND emgflow.meta_value = "approved" ';

	return $query;
} );
