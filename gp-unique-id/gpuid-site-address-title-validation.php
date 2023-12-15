<?php
/**
 * Gravity Perks // Unique ID // Unique ID as Site Address and Site Title
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * The Unique ID field is populated on submission, after validation. This snippet runs post-validation to allow User Registration feeds with a Unique ID
 * field mapped as the site address and/or site title to still pass validation.
 *
 * Plugin Name:  GP Unique ID - Unique ID as Site Address and Site Title
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-unique-id/
 * Description:  Validate Unique ID Field Used as Site Address and Site Title.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_user_registration_validation', 'gp_unique_id_for_site_validation', 11, 2 );
function gp_unique_id_for_site_validation( $form, $feed ) {

	$site_address_field_id = rgars( $feed, 'meta/multisite_options/site_address' );
	$site_name_field_id    = rgars( $feed, 'meta/multisite_options/site_title' );

	foreach ( $form['fields'] as &$field ) {

		if ( $field['type'] == 'uid' && in_array( $field['id'], array( $site_address_field_id, $site_name_field_id ) ) ) {
			$field['failed_validation'] = false;
		}
	}

	return $form;
}
