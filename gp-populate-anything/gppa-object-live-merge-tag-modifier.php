<?php
/**
 * Gravity Perks // GP Populate Anything // Add Object to Live Merge Tag Modifier
 *
 * Add support for an :object modifier to output a different property of the selected object.
 *
 * Example: @{:2:object[post_content]}
 */
add_filter( 'gppa_live_merge_tag_value', function ( $merge_tag_match_value, $merge_tag, $form, $field_id, $entry_values ) {
	$merge_tag_modifiers = gp_populate_anything()->live_merge_tags->extract_merge_tag_modifiers( $merge_tag );

	if ( empty( $merge_tag_modifiers['object'] ) ) {
		return $merge_tag_match_value;
	}

	/*
	 * Get the field ID out of the form object.
	 */
	foreach ( $form['fields'] as $current_field ) {
		if ( (int) $current_field->id === (int) $field_id ) {
			$field = $current_field;
			break;
		}
	}

	if ( ! isset( $field ) || empty( $field['choices'] ) ) {
		return $merge_tag_match_value;
	}

	$objects         = wp_list_pluck( $field['choices'], 'object' );
	$selected_object = null;

	foreach ( $field['choices'] as $choice ) {
		$processed_template = gp_populate_anything()->process_template( $field, 'value', $choice['object'], 'choices', $objects );

		if ( $processed_template == rgar( $entry_values, $field_id ) ) {
			$selected_object = $choice['object'];
			break;
		}
	}

	$object_type = gp_populate_anything()->get_object_type( rgar( $field, 'gppa-choices-object-type' ), $field );

	if ( ! $selected_object || ! $object_type ) {
		return $merge_tag_match_value;
	}

	return $object_type->get_object_prop_value( $selected_object, $merge_tag_modifiers['object'] );
}, 10, 5 );
