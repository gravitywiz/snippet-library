<?php
/**
 * Gravity Perks // Unique ID // Generate Unique ID when Entry is Duplicated in GravityVIew
 * https://gravitywiz.com/documentation/gp-unique-id/
 *
 * When an entry is duplcicated in GravityView,the Unique ID value is copied to the duplicated entry.
 * This snippet generates a new unique ID value for the duplicated entry.
 *
 * Plugin Name: GP Unique ID – Generate Unique ID when Entry is Duplicated in GravityVIew
 * Plugin URI:  https://gravitywiz.com/documentation/gp-unique-id/
 * Description: Generates a new unique ID value for the duplicated entry in GravityVIew.
 * Author:      Gravity Wiz
 * Version:     0.1
 * Author URI:  https://gravitywiz.com
 */
add_action( 'gravityview/duplicate-entry/duplicated', function ( $duplicated_entry = array(), $entry = array() ) {
	if ( ! is_callable( 'gp_unique_id' ) ) {
		return $duplicated_entry;
	}

	$form = GFAPI::get_form( $duplicated_entry['form_id'] );
	foreach ( $form['fields'] as $field ) {
		if ( is_a( $field, 'GF_Field_Unique_ID' ) ) {
			$uid = gp_unique_id()->get_unique( $form['id'], $field );
			GFAPI::update_entry_field( $duplicated_entry['id'], $field->id, $uid );
			$duplicated_entry[ $field->id ] = $uid;
		}
	}
	return $duplicated_entry;
}, 10, 2 );
