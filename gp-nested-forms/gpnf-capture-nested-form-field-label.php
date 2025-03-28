<?php
/**
 * Gravity Forms // Nested Forms // Capture Nested Form Field Label in Child Form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/1630b49c960441e8a5d13b0d5c2fa3e8
 *
 * This snippet allows you to capture the label of the Nested Form field from which the child
 * form has been opened.
 *
 * This is useful when you have two or more Nested Form fields on the same parent form, using
 * the same child form and need to identify to which Nested Form field the child entry belongs
 * (e.g. Team Captains vs Players).
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 * 
 * 2. Enable dynamic population on your desired field on the child form.
 *
 * 3. Set the dynamic population parameter to "gpnf_nested_form_field_label".
 */
add_filter( 'gform_field_value_gpnf_nested_form_field_label', function () {

	// Get the parent form ID
	$form_id = gp_nested_forms()->get_parent_form_id();
	if ( ! $form_id ) {
		return '';
	}

	// Retrieve the form object
	$form = GFAPI::get_form( $form_id );
	if ( ! $form ) {
		return '';
	}

	// Get the posted Nested Form field ID
	$nested_form_field_id = rgar( $_REQUEST, 'gpnf_nested_form_field_id' );
	if ( ! $nested_form_field_id ) {
		return '';
	}

	// Get the Nested Form field object
	$nested_form_field = GFFormsModel::get_field( $form, $nested_form_field_id );
	if ( ! $nested_form_field || ! is_object( $nested_form_field ) ) {
		return '';
	}

	// Return the field label
	return $nested_form_field->get_field_label( false, '' );
} );
