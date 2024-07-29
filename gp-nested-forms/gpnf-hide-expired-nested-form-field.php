<?php
/**
 * Gravity Perks // GP Nested Forms // Hide Nested Form Field if the Nested Form has expired.
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/798f30d73d254864aa6a65e7d16d390b
 */
add_filter( 'gform_pre_render', 'check_nested_form_expiration' );
function check_nested_form_expiration( $form ) {
	// Update '435' to the targeted Parent Form's ID.
	if ( rgar( $form, 'id' ) != 435 || ! is_callable( 'gp_nested_forms' ) ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {
		if ( $field->type == 'form' && $field->gpnfForm ) {
			$nested_form = GFAPI::get_form( $field->gpnfForm );
			$is_expired  = GFFormDisplay::validate_form_schedule( $nested_form );
			if ( $is_expired ) {
				$field->visibility = 'hidden';
			}
		}
	}
	return $form;
}
