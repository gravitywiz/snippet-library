<?php
/**
 * Gravity Perks // Date Time Calculator // Recalculate Field Value on View
 * https://gravitywiz.com/documentation/gravity-forms-date-time-calculator/
 *
 * Experimental Snippet 🧪
 *
 * Recalculate a calculated field's value every time it's viewed.
 *
 * Works great with Date Time Calculator's [:age modifier][1].
 *
 * [1]: https://gravitywiz.com/documentation/gravity-forms-date-time-calculator/#calculating-age
 */
add_action( 'wp_loaded', function() {

	$form_id  = 123;  // Change this to the form's ID
	$field_id = 4;    // Change this to the Calculation field's ID.

	$values      = array();
	$calculating = false; // Flag to prevent infinite recursion

	add_filter( sprintf( 'gform_get_input_value_%s', $form_id ), function( $value, $entry, $field, $input_id ) use ( $field_id, &$values, &$calculating ) {
		// Prevent infinite recursion
		if ( $calculating ) {
			return $value;
		}

		if ( $field['id'] !== $field_id ) {
			$values[ $field['id'] ] = $value;
			return $value;
		}

		// Set flag to prevent recursion
		$calculating = true;

		$form   = GFAPI::get_form( $entry['form_id'] );
		$_entry = $entry + $values;
		$calculated_value = GFCommon::calculate( $field, $form, $_entry );

		GFAPI::update_entry_field( $_entry['id'], $field_id, $calculated_value );
		// Reset flag
		$calculating = false;

		return $calculated_value;
	}, 10, 4 );

	// GravityView Support.
	add_filter( 'gravityview/field/number/value', function( $value, $field, $view, $form, $entry ) use ( $field_id, $form_id ) {
		if ( $field->ID != $field_id && $form->ID != $form_id ) {
			return $value;
		}
		$orig_entry = GFAPI::get_entry( $entry->ID );
		return rgar( $orig_entry, $field_id );
	}, 10, 5 );

} );
