<?php
/**
 * Output value instead of label for choice-based fields when using {all_fields} template or Nested Forms merge tag.
 */
add_filter( 'gform_merge_tag_filter', function ( $field_value, $merge_tag, $options, $field, $raw_field_value ) {
	$child_form_id        = 463;        // Change this to the child form's ID
	$fields_to_use_values = array( 1 ); // Change this to the radio field ID, multiple can be specified with commas, example: array( 1, 17, 8)
	$is_view              = ( class_exists( 'GV\Request' ) && GV\Request::is_frontend() );
	if ( ! $is_view || empty( $field->choices ) || ! in_array( $field->id, $fields_to_use_values ) || $field->formId !== $child_form_id ) {
		return $field_value;
	}

	return $raw_field_value;
}, 10, 6 );
