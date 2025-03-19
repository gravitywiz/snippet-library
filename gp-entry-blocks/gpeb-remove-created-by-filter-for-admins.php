<?php
/**
 * Gravity Perks // GP Entry Blocks // Remove "created_by" filter for users who can view all entries.
 *
 * Installation:
 *  1. See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *  2. Customize the form ID in the snippet below.
 */
add_filter( 'gpeb_entries_query', function( $query, $form_id, $block_context ) {
	// Customize `2` below or remove lines 11-13 if you want this behavior for all forms/blocks.
	if ( $form_id !== 2 ) {
		return $query;
	}

	if ( ! GFCommon::current_user_can_any( 'gravityforms_view_entries' ) ) {
		return $query;
	}

	foreach ( $query as $filter_group_index => $filters ) {
		foreach ( $filters as $filter_index => $filter ) {
			if ( $filter->left->field_id === 'created_by' ) {
				unset( $query[ $filter_group_index ][ $filter_index ] );
			}
		}

		if ( empty( $query[ $filter_group_index ] ) ) {
			unset( $query[ $filter_group_index ] );
		}
	}

	return $query;
}, 10, 3 );
