<?php
/**
 * Gravity Perks // Nested Forms // Dynamically Populate Session Hash
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Set a field's dynamic population parameter to "gpnf_session_hash" to populate the current Nested Forms session hash.
 * Works for fields in the parent and child form. 
 *
 * Screenshot: https://gwiz.io/3m9YImu
 */
add_filter( 'gform_field_value_gpnf_session_hash', function( $value, $field ) {
	if ( ! is_callable( 'gp_nested_forms' ) ) {
		return $value;
	}
	if ( gp_nested_forms()->get_parent_form_id() ) {
		$parent_form_id = gp_nested_forms()->get_parent_form_id();
	} else {
		$parent_form_id = $field->formId;
	}
	$session = new GPNF_Session( $parent_form_id );
	return $session->get_runtime_hashcode();
}, 10, 2 );
