<?php
/**
 * Gravity Perks // Populate Anything // Populate choices using choice labels rather than values
 * http://gravitywiz.com/documentation/gravity-forms-populate-anything/
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
	$populating_from_field    = GFAPI::get_field( rgar( $entry, 'form_id' ), $populating_from_field_id );
	if( ! $populating_from_field ) {
		// create an empty field if field doesn't exist (PHP 8 spec).
		$populating_from_field = (object) [ 'choices' => [] ];
	}
	// For dynamically populated choices
	$choice_text = gp_populate_anything()->get_submitted_choice_label( $template_value, $populating_from_field, rgar( $entry, 'id' ) );

	// Get label for static choices.
	if ( $choice_text === $template_value ) {
		$choice_text = RGFormsModel::get_choice_text( $populating_from_field, $template_value );
	}

	return $choice_text;
}, 10, 8 );
