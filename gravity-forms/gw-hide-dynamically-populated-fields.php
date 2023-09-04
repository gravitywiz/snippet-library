<?php
/**
 * Gravity Wiz // Gravity Forms // Hide Dynamically Populated Fields
 * https://gravitywiz.com/
 *
 * Use this snippet to automatically hide fields that are dynamically populated. Works with fields populated via
 * Easy Passthrough, Populate Anything, or Gravity Forms default dynamic population functionality
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_render_123', function ( $form, $ajax, $field_values ) {

	foreach ( $form['fields'] as &$field ) {

		$value = GFFormsModel::get_field_value( $field, $field_values, false );
		if ( is_array( $value ) ) {
			$value = array_filter( $value );
		}

		if ( ! $value ) {
			$value = $field->gppa_hydrated_value;
			if ( is_array( $value ) ) {
				$value = array_filter( $value );
			}
		}

		// If we have a value and we're populating a choice-based field, make sure the value matches a choice.
		if ( $value && ! empty( $field->choices ) ) {
			$has_matching_choice = false;
			foreach ( $field->choices as $choice ) {
				if ( $choice['value'] == $value ) {
					$has_matching_choice = true;
					break;
				}
			}
		}

		if ( $value && ( ! isset( $has_matching_choice ) || $has_matching_choice ) ) {
			$field->visibility = 'hidden';
		}

	}

	return $form;
}, 10, 3 );
