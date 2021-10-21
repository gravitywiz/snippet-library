<?php
/**
 * Gravity Wiz // Gravity Forms User Registration // Delete Entry Files When User Deleted
 * https://gravitywiz.com/
 *
 * Delete entry files associated with user when user is deleted. This snippet only works when the user was created or
 * updated via a Gravity Form with an associated User Registration feed.
 */
add_action( 'delete_user', function( $id, $reassign, $user ) {

	$entry_id = $user->get( '_gform-entry-id' ) ?: $user->get( '_gform-update-entry-id' );
	if ( ! $entry_id ) {
		return;
	}

	$user_registration_feeds = rgar( gform_get_meta( $entry_id, 'processed_feeds' ), 'gravityformsuserregistration' );
	if ( empty( $user_registration_feeds ) ) {
		return;
	}

	foreach ( $user_registration_feeds as $feed_id ) {
		$feed = GF_User_Registration::get_instance()->get_feed( $feed_id );
		if ( ! $feed ) {
			continue;
		}
		foreach ( $feed['meta']['userMeta'] as $meta ) {
			$field = GFAPI::get_field( $feed['form_id'], $meta['value'] );
			if ( ! $field || $field->get_input_type() !== 'fileupload' ) {
				continue;
			}
			GFFormsModel::delete_file( $entry_id, $field->id );
		}
	}

}, 10, 3 );
