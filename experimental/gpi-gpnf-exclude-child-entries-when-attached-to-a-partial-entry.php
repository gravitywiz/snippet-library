<?php
/**
 * Gravity Perks // Inventory + Nested Forms // Exclude Child Entries of Partial Entry Parents from Inventory
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * WIP: This provides very basic support for excluding child entries attached to a partial entry parent
 * from inventory limits.
 *
 * @todo
 *
 * 1. Apply to all child forms without needing to specify a form ID.
 * 2. Count child entries attached to the current partial entry.
 *    Support has been implemented when traversing between pages; however, the Nested Forms markup AJAX request
 *    is not aware of the current partial entry ID. Will need to pass this via the `gpnf_session_script_data`
 *    filter so it can be accessed
 */
// Update "123" to your child form ID.
add_filter( 'gpi_query_123', function( $query, $field ) {
	global $wpdb;
	if ( class_exists( 'GF_Partial_Entries' ) ) {
		if ( rgpost( 'partial_entry_id' ) ) {
			$meta_value_clause = $wpdb->prepare( 'AND meta_value != %s', rgpost( 'partial_entry_id' ) );
		}
		$query['where'] .= "
			AND e.id IN (
				SELECT entry_id FROM {$wpdb->prefix}gf_entry_meta
				WHERE meta_key = 'gpnf_entry_parent'
				AND meta_value NOT IN(
					SELECT entry_id FROM {$wpdb->prefix}gf_entry_meta WHERE meta_key = 'partial_entry_id' $meta_value_clause
				)
			)";
	}
	return $query;
}, 10, 2 );
