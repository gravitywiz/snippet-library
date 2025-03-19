<?php
/**
 * Show values instead of labels for Populate-Anything-enabled fields in GravityView.
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Experimental Snippet 🧪
 */
add_filter( 'gform_entry_field_value', function( $display_value, $field, $entry, $form ) {

	// Update "123" to your form ID. Update to "4", "5", and "6" to your field IDs.
	$targets = array(
		123 => array( 4, 5, 6 ),
	);

	if ( ! function_exists( 'gravityview' ) || ! gravityview()->request->is_view() ) {
		return $display_value;
	}

	if ( ! is_callable( 'gp_populate_anything' ) ) {
		return $display_value;
	}

	remove_filter( 'gform_entry_field_value', array( gp_populate_anything(), 'entry_field_value' ), 20 );

	if ( ! in_array( $field->id, rgar( $targets, $field->formId, array() ) ) ) {
		$display_value = gp_populate_anything()->get_submitted_choice_label( $display_value, $field, $entry['id'] );
	}

	return $display_value;
}, 19, 4 );
