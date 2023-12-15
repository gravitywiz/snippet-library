<?php
/**
 * Gravity Perks // Populate Anything // Remove Hidden Non-breaking Spaces
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Remove hidden non-breaking spaces from your field values. This snippet currently only applies to multi-selectable
 * choice fields (e.g. Checkboxes, Multi-selects) and resolves an issue where choices in these fields were not correctly
 * selected when strings contained nbsp's between values.
 */
add_filter( 'gppa_process_template_value', function( $value, $field ) {
	if ( ! is_array( $value ) && in_array( $field->get_input_type(), gp_populate_anything()::get_multi_selectable_choice_field_types(), true ) ) {
		$value = htmlentities( $value, null, 'utf-8' );
		$value = str_replace( '&nbsp;', '', $value );
		$value = html_entity_decode( $value );
	}
	return $value;
}, 10, 2 );
