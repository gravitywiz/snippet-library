<?php
/**
 * Gravity Perks // Address Autocomplete // Populate ACF Google Maps Fields
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Map GPAA-enabled Address fields to ACF Google Map fields via the GF User Registration add-on.
 */

add_action( 'gform_user_updated', 'gpaa_acf_gfur_update_user_acf_map_field', 10, 3 );
add_action( 'gform_user_registered', 'gpaa_acf_gfur_update_user_acf_map_field', 10, 3 );

function gpaa_acf_gfur_update_user_acf_map_field( $user_id, $feed, $entry ) {
	if ( ! is_callable( 'acf_get_field' ) || ! is_callable( 'gp_address_autocomplete' ) ) {
		return;
	}

	$form                    = GFAPI::get_form( $entry['form_id'] );
	$auto_complete_field_ids = wp_list_pluck( gp_address_autocomplete()->get_autocomplete_fields( $form ), 'id' );
	if ( empty( $auto_complete_field_ids ) ) {
		return;
	}

	$meta_fields = gf_user_registration()->get_generic_map_fields( $feed, 'userMeta', $form, $entry );

	foreach ( $meta_fields as $custom_field => $value ) {

		$acf_field = acf_get_field( $custom_field );
		if ( $acf_field['type'] !== 'google_map' ) {
			continue;
		}

		$gf_field = GFAPI::get_field( $form, gpaa_acf_gfur_get_meta_field_id_by_meta_key( $feed, 'userMeta', $custom_field ) );
		if ( ! $gf_field || ! in_array( $gf_field->id, $auto_complete_field_ids ) ) {
			continue;
		}

		$value = array(
			'address' => $value,
			'lat'     => rgar( $entry, "gpaa_lat_{$gf_field->id}" ),
			'lng'     => rgar( $entry, "gpaa_lng_{$gf_field->id}" ),
		);

		update_field( $acf_field['key'], $value, "user_{$user_id}" );
	}

}

function gpaa_acf_gfur_get_meta_field_id_by_meta_key( $feed, $field_name, $meta_key ) {
	$generic_fields = rgar( $feed, 'meta' ) ? rgars( $feed, 'meta/' . $field_name ) : rgar( $feed, $field_name );
	foreach ( $generic_fields as $generic_field ) {
		$field_key = 'gf_custom' === $generic_field['key'] ? $generic_field['custom_key'] : $generic_field['key'];
		if ( $field_key === $meta_key ) {
			return $generic_field['value'];
		}
	}
	return false;
}
