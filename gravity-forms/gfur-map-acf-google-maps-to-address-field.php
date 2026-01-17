<?php
/**
 * Gravity Forms // User Registration // Map ACF Google Maps Field to Address Field
 * https://gravitywiz.com/
 *
 * Maps an ACF Google Maps field's data to individual Address field inputs
 * when using the Update User Registration feed.
 *
 * Instructions:
 * 1. Map your ACF Google Maps meta key to the Address field using "Full Address" option
 * 2. Update the form ID and field ID in the configuration below
 */
add_filter( 'gform_user_registration_user_data_pre_populate', function( $mapped_fields, $form, $feed ) {
	// Configuration
	$target_form_id   = 123; // Your form ID
	$address_field_id = 4;   // Your Address field ID

	if ( $form['id'] != $target_form_id ) {
		return $mapped_fields;
	}

	// When mapped to "Full Address", ACF data arrives as an array at the parent field ID
	$address_data = rgar( $mapped_fields, $address_field_id );

	if ( ! is_array( $address_data ) || empty( $address_data['city'] ) ) {
		return $mapped_fields;
	}

	// Build street address from components
	$street = trim( rgar( $address_data, 'street_number' ) . ' ' . rgar( $address_data, 'street_name_short' ) );
	if ( empty( $street ) ) {
		$street = rgar( $address_data, 'name' );
	}

	// Map to Address field inputs
	$mapped_fields[ $address_field_id . '.1' ] = $street;
	$mapped_fields[ $address_field_id . '.2' ] = '';
	$mapped_fields[ $address_field_id . '.3' ] = rgar( $address_data, 'city' );
	$mapped_fields[ $address_field_id . '.4' ] = rgar( $address_data, 'state_short' );
	$mapped_fields[ $address_field_id . '.5' ] = rgar( $address_data, 'post_code' );
	$mapped_fields[ $address_field_id . '.6' ] = rgar( $address_data, 'country' );

	return $mapped_fields;
}, 10, 3 );
