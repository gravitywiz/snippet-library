<?php
/**
 * Gravity Perks // Address Autocomplete + ACF // Populate Google Maps Custom Field
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Map GPPA-enabled Address fields to ACF Google Map fields via the GF Advanced Post Creation add-on.
 *
 * Plugin Name:  GP Address Autocomplete — ACF Google Maps Support
 * Plugin URI:   ...
 * Description:  Map GPPA-enabled Address fields to ACF Google Map fields via the GF Advanced Post Creation add-on.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_action( 'gform_advancedpostcreation_post_after_creation', function( $post_id, $feed, $entry, $form ) {

	if ( ! is_callable( 'acf_get_field' ) || ! is_callable( 'gp_address_autocomplete' ) ) {
		return;
	}

	$auto_complete_field_ids = wp_list_pluck( gp_address_autocomplete()->get_autocomplete_fields( $form ), 'id' );
	if ( empty( $auto_complete_field_ids ) ) {
		return;
	}

	$meta_fields = gf_advancedpostcreation()->get_generic_map_fields( $feed, 'postMetaFields', $form, $entry );

	foreach ( $meta_fields as $custom_field => $value ) {

		$acf_field = acf_get_field( $custom_field );
		if ( $acf_field['type'] !== 'google_map' ) {
			continue;
		}

		$gf_field = GFAPI::get_field( $form, gw_get_meta_field_id_by_meta_key( $feed, 'postMetaFields', $custom_field ) );
		if ( ! $gf_field || ! in_array( $gf_field->id, $auto_complete_field_ids ) ) {
			continue;
		}

		$value = array(
			'address' => $value,
			'lat'     => $entry[ "gpaa_lat_{$gf_field->id}" ],
			'lng'     => $entry[ "gpaa_lng_{$gf_field->id}" ],
		);

		update_field( $acf_field['key'], $value, $post_id );

	}

}, 10, 4 );

function gw_get_meta_field_id_by_meta_key( $feed, $field_name, $meta_key ) {
	$generic_fields = rgar( $feed, 'meta' ) ? rgars( $feed, 'meta/' . $field_name ) : rgar( $feed, $field_name );
	foreach ( $generic_fields as $generic_field ) {
		$field_key = 'gf_custom' === $generic_field['key'] ? $generic_field['custom_key'] : $generic_field['key'];
		if ( $field_key === $meta_key ) {
			return $generic_field['value'];
		}
	}
	return false;
}
