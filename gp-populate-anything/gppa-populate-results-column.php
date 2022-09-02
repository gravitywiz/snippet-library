<?php
/**
 * Gravity Perks // Populate Anything // Return Column Name of Matching Value
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * When populating a field with multiple "or" filter groups, use this snippet to populate the column name of the matching
 * group. [Example Configuration](https://gwiz.io/3B7d6Em).
 */
add_filter( 'gppa_process_template_value', function( $template_value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {

	$form_id         = 123;
	$source_field_id = 4;
	$target_field_id = 5;

	if ( $field->formId == $form_id && $field->id == $target_field_id ) {
		$source_value     = rgpost( "input_{$source_field_id}" );
		$columns_by_value = array_flip( $object );
		$template_value   = rgar( $columns_by_value, $source_value );
	}

	return $template_value;
}, 10, 8 );
