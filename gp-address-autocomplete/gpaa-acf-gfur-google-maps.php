<?php
/**
 * Gravity Perks // Address Autocomplete // Populate ACF Google Maps Fields
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Map GPAA-enabled Address fields to ACF Google Map fields via the GF User Registration add-on.
 * Preserves existing ACF data if the user didn't select a new address via autocomplete.
 */
add_action( 'gform_user_updated', 'gpaa_acf_gfur_update_user_acf_map_field', 10, 3 );
add_action( 'gform_user_registered', 'gpaa_acf_gfur_update_user_acf_map_field', 10, 3 );

// Cache existing ACF values before GFUR overwrites them
add_action( 'gform_pre_process', 'gpaa_acf_gfur_cache_existing_values', 10, 1 );

function gpaa_acf_gfur_get_cached_values( $values = null ) {
	static $cache = array();
	if ( $values !== null ) {
		$cache = $values;
	}
	return $cache;
}

function gpaa_acf_gfur_cache_existing_values( $form ) {
	if ( ! is_callable( 'acf_get_field' ) || ! is_callable( 'gp_address_autocomplete' ) || ! is_user_logged_in() ) {
		return;
	}

	$auto_complete_field_ids = wp_list_pluck( gp_address_autocomplete()->get_autocomplete_fields( $form ), 'id' );
	if ( empty( $auto_complete_field_ids ) ) {
		return;
	}

	$feeds = gf_user_registration()->get_feeds( $form['id'] );
	if ( empty( $feeds ) ) {
		return;
	}

	$cached_values = array();
	$user_id       = get_current_user_id();

	foreach ( $feeds as $feed ) {
		// Only update feeds need caching; new registrations have no existing data
		if ( rgars( $feed, 'meta/feedType' ) !== 'update' ) {
			continue;
		}

		$user_meta_mappings = rgars( $feed, 'meta/userMeta' );
		if ( empty( $user_meta_mappings ) ) {
			continue;
		}

		foreach ( $user_meta_mappings as $mapping ) {
			$meta_key  = 'gf_custom' === $mapping['key'] ? $mapping['custom_key'] : $mapping['key'];
			$acf_field = acf_get_field( $meta_key );

			if ( $acf_field && $acf_field['type'] === 'google_map' ) {
				$cached_values[ $meta_key ] = get_field( $meta_key, "user_{$user_id}", false );
			}
		}
	}

	gpaa_acf_gfur_get_cached_values( $cached_values );
}

function gpaa_acf_gfur_update_user_acf_map_field( $user_id, $feed, $entry ) {
	if ( ! is_callable( 'acf_get_field' ) || ! is_callable( 'gp_address_autocomplete' ) ) {
		return;
	}

	$form                    = GFAPI::get_form( $entry['form_id'] );
	$auto_complete_field_ids = wp_list_pluck( gp_address_autocomplete()->get_autocomplete_fields( $form ), 'id' );
	if ( empty( $auto_complete_field_ids ) ) {
		return;
	}

	$meta_fields   = gf_user_registration()->get_generic_map_fields( $feed, 'userMeta', $form, $entry );
	$cached_values = gpaa_acf_gfur_get_cached_values();

	foreach ( $meta_fields as $custom_field => $value ) {
		$acf_field = acf_get_field( $custom_field );
		if ( ! $acf_field || $acf_field['type'] !== 'google_map' ) {
			continue;
		}

		$gf_field = GFAPI::get_field( $form, gpaa_acf_gfur_get_meta_field_id_by_meta_key( $feed, 'userMeta', $custom_field ) );
		if ( ! $gf_field || ! in_array( $gf_field->id, $auto_complete_field_ids, true ) ) {
			continue;
		}

		$lat = rgar( $entry, "gpaa_lat_{$gf_field->id}" );
		$lng = rgar( $entry, "gpaa_lng_{$gf_field->id}" );

		if ( ! empty( $lat ) && ! empty( $lng ) ) {
			// Autocomplete was used - save new value
			$new_value = array(
				'address' => $value,
				'lat'     => $lat,
				'lng'     => $lng,
			);
			update_field( $acf_field['key'], $new_value, "user_{$user_id}" );
		} elseif ( ! empty( $cached_values[ $custom_field ] ) ) {
			// Autocomplete not used - restore cached value
			update_field( $acf_field['key'], $cached_values[ $custom_field ], "user_{$user_id}" );
		}
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
