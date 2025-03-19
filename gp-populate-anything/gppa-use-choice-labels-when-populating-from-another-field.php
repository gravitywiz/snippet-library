<?php
/**
 * Gravity Perks // Populate Anything // Populate choices using choice labels rather than values
 * http://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/0a3bc5ea99ee44f6acccceab3737101a (Out of date)
 *
 * Instructions:
 *  1. Install the snippet.
 *     https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *  2. Add "gppa-use-choice-label" to the "Custom CSS Class" field setting of the target field.
 */
add_filter( 'gppa_process_template', function ( $template_value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {

	if ( $template_name !== 'label' || strpos( $field->cssClass, 'gppa-use-choice-label' ) === false ) {
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
