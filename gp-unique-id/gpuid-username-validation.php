<?php
/**
 * Gravity Perks // GP Unique ID // Unique ID as Username or Site Address
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Instruction Video: https://www.loom.com/share/25a80c43df98463ea68227761b285a20
 *
 * The Unique ID field is populated on submission, after validation. This snippet runs post-validation to allow User Registration feeds with a Unique ID
 * field mapped as the username to still pass validation.
 *
 * Plugin Name:  GP Unique ID — Unique ID as Username or Site Address
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-unique-id/
 * Description:  This snippet runs post-validation to allow User Registration feeds with a Unique ID field mapped as the username to still pass validation.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_user_registration_validation', 'gp_unique_id_for_username_validation', 11, 2 );
function gp_unique_id_for_username_validation( $form, $feed ) {

	$username_field_id     = rgars( $feed, 'meta/username' );
	$site_address_field_id = rgars( $feed, 'meta/multisite_options/site_address' );

	foreach ( $form['fields'] as &$field ) {
		if ( in_array( $field['id'], array( $username_field_id, $site_address_field_id ) ) && $field['type'] == 'uid' ) {
			$field['failed_validation'] = false;
		}
	}

	return $form;
}
