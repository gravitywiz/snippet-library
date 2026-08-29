<?php
/**
 * Gravity Perks // Populate Anything // Force GPPA value over a Save & Continue Value
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gform_incomplete_submission_post_get', function( $submission_json, $resume_token, $form ) {
	// Update "29" to your form ID, and "1, 5, 6" to your field IDs.
	$target_form_id   = 29;
	$target_field_ids = array( 1, 5, 6 );
	static $_gppa_forcing_hydration;
	foreach ( $target_field_ids as $target_field_id ) {
		if ( $form['id'] == $target_form_id && ! $_gppa_forcing_hydration ) {
			$_gppa_forcing_hydration = true;
			$submission              = json_decode( $submission_json, ARRAY_A );
			$field                   = GFAPI::get_field( $form, $target_field_id );
			$hydrated_field          = gp_populate_anything()->hydrate_field( $field, $form, array(), null, false );

			$submission['submitted_values'][ $target_field_id ] = $hydrated_field['field_value'];

			$submission_json         = json_encode( $submission );
			$_gppa_forcing_hydration = false;
		}
	}
	return $submission_json;
}, 10, 3 );
