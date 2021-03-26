<?php
/**
 * Gravity Perks // GP Unique ID // Unique ID as Username or Site Address
 *
 * The Unique ID field is populated on submission, after validation. This snippet runs post-validation to allow User Registration feeds with a Unique ID
 * field mapped as the username to still pass validation.
 *
 */
add_filter( 'gform_user_registration_validation', 'gp_unique_id_for_username_validation', 11, 2 );
function gp_unique_id_for_username_validation( $form, $feed ) {

	$username_field_id     = rgars( $feed, 'meta/username' );
	$site_address_field_id = rgars( $feed, 'meta/multisite_options/site_address' );

	foreach( $form['fields'] as &$field ) {
		if( in_array( $field['id'], array( $username_field_id, $site_address_field_id ) ) && $field['type'] == 'uid' ) {
			$field['failed_validation'] = false;
		}
	}

	return $form;
}
