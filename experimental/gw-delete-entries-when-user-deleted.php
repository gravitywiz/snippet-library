<?php
/**
 * Gravity Wiz // Gravity Forms // Delete User's Entries When User Deleted
 * https://gravitywiz.com/
 *
 * Automatically delete all of a user's entries across all forms when the user is deleted. If the user's content is
 * reassigned to another user, their entries will be reassigned to the same user rather than deleted.
 *
 * Note: This will only delete up to 300 entries due to performance concerns with this implementation. If you need to
 * delete more entries for a deleted user, [contact our support](https://gravitywiz.com/support/).
 */
add_action( 'deleted_user', function( $deleted_user_id, $reassigned_user_id ) {

	$search = array(
		'field_filters' => array(
			array(
				'key'   => 'created_by',
				'value' => $deleted_user_id,
			),
		),
	);

	$paging = array(
		'offset'    => 0,
		'page_size' => 300,
	);

	$entries = GFAPI::get_entries( 0, $search, null, $paging );

	foreach ( $entries as $entry ) {
		if ( $reassigned_user_id ) {
			GFAPI::update_entry_property( $entry['id'], 'created_by', $reassigned_user_id );
		} else {
			GFAPI::delete_entry( $entry['id'] );
		}
	}

}, 10, 2 );
