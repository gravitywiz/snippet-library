<?php
/**
 * Gravity Perks // Populate Anything // Populate choices using choice labels rather than values
 * http://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/0a3bc5ea99ee44f6acccceab3737101a
 *
 * Installation instructions:
 *   1. https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Update variables below accordingly.
 */
add_filter( 'gppa_process_template', function ( $template_value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {
	/* Variables to customize */
	$populated_form_id  = 3;
	$populated_field_id = 3;
	/* End variables to customize */

	if ( $template_name !== 'label' || $field->id !== $populated_field_id || $field->formId !== $populated_form_id ) {
		return $template_value;
	}

	$entry = (array) $object;

	$populating_from_field_id = str_replace( 'gf_field_', '', $template );
	$populating_from_field    = GFAPI::get_field( $entry['form_id'], $populating_from_field_id );

	// For dynamically populated choices
	$choice_text = gp_populate_anything()->get_submitted_choice_label( $template_value, $populating_from_field, $entry['id'] );

	// Get label for static choices.
	if ( $choice_text === $template_value ) {
		$choice_text = RGFormsModel::get_choice_text( $populating_from_field, $template_value );
	}

	return $choice_text;
}, 10, 8 );
