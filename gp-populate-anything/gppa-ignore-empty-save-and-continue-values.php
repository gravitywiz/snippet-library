<?php
/**
 * Gravity Perks // Populate Anything // Ignore Empty Save & Continue Values
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * By default, Populate Anything will honor empty values saved for a field via Save & Continue. Use this snippet if you
 * would like to ignore empty Save & Continue values and use GPPA-populated values.
 */
add_filter( 'gppa_should_force_use_field_value', function( $should_use_field_value, $field ) {

	if ( ! rgar( $_REQUEST, 'gf_token' ) ) {
		return $should_use_field_value;
	}

	$save_and_continue_values = gp_populate_anything()->get_save_and_continue_values( rgar( $_REQUEST, 'gf_token' ) );
	if ( empty( $save_and_continue_values ) ) {
		return $should_use_field_value;
	}

	foreach ( $save_and_continue_values as $input_id => $value ) {
		if ( absint( $field->id ) === absint( $input_id ) ) {
			if ( is_array( $value ) ) {
				$value = array_filter( $value );
			}
			if ( empty( $value ) ) {
				$should_use_field_value = false;
			}
			break;
		}
	}

	return $should_use_field_value;
}, 10, 2 );
