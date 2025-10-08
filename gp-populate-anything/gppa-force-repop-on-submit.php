<?php
/**
 * Gravity Perks // Populate Anything // Force Repopulation on Form Submission to Prevent Tampering
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * This snippet forces GP Populate Anything to repopulate field values on form submission,
 * overriding any potential user tampering with the $_POST data. This ensures that dynamically
 * populated fields always contain the correct values based on their population settings.
 *
 * Instructions:
 * 1. [Install the snippet.](https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets)
 * 1. Add the CSS class `gppa-secure` to any field where you want to force repopulation.
 * 2. The snippet will automatically replace submitted values with the correct populated values.
 */
add_filter( 'gform_pre_process', function( $form ) {
	
	// Skip if GPPA is not available
	if ( ! function_exists( 'gp_populate_anything' ) ) {
		return $form;
	}

	$field_values = gp_populate_anything()->get_posted_field_values( $form );
	
	foreach ( $form['fields'] as $field ) {
		
		// Skip fields that don't have the security CSS class
		if ( ! $field->cssClass || strpos( $field->cssClass, 'gppa-secure' ) === false ) {
			continue;
		}
		
		// Only process GPPA-enabled fields
		if ( ! gp_populate_anything()->is_field_dynamically_populated( $field ) ) {
			continue;
		}

		$working_field_values = $field_values;
		unset( $working_field_values[ $field->id ] );

		$populated_field = gp_populate_anything()->populate_field( $field, $form, $working_field_values, null, true );
		$correct_value   = $populated_field['field_value'];

		$working_field_values[ $field->id ] = $correct_value;
		
		// Handle multi-input fields (like Name, Address, etc.)
		$is_multi_input = is_array( $field->inputs ) && ! empty( $field->inputs );
		
		if ( $is_multi_input ) {
			// For multi-input fields, override each input with correct value
			foreach ( $field->inputs as $input ) {
				$input_id = str_replace( '.', '_', $input['id'] );
				$post_key = "input_{$input_id}";
				$correct_input_value = rgar( $correct_value, $input['id'] );
				
				// Override with the correct populated value
				$_POST[$post_key] = $correct_input_value;
			}
		} else {
			$_POST["input_{$field->id}"] = $correct_value;
		}
	}
	
	return $form;
}, 2 );
