<?php
/**
 * Gravity Perks // Read Only // Enable When Dynamically Populated
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 *
 * Use this snippet to make fields readonly if they are dynamically populated. You must enable the "Read-only" field
 * setting for this functionality to apply.
 *
 * See video: https://www.loom.com/share/7c42fc8c0aef42d990ae600675546774
 */
add_filter( 'gform_pre_render', function ( $form, $ajax, $field_values ) {

	foreach ( $form['fields'] as &$field ) {

		if ( ! $field->gwreadonly_enable ) {
			continue;
		}

		$value = GFFormsModel::get_field_value( $field, $field_values, false ) || $field->gppa_hydrated_value;

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

		if ( ! $value || ( isset( $has_matching_choice ) && ! $has_matching_choice ) ) {
			$field->gwreadonly_enable = false;
		}

	}

	return $form;
}, 10, 3 );
