<?php
/**
 * Gravity Wiz // Nested Forms // Populate Parent Entry ID
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Populate the current parent entry ID (or hash) using the "gpnf_parent_entry_id" dynamic population parameter.
 * This parameter can only be used for fields in the parent form. To capture in a child form, you must populate
 * the parent entry ID in a field on the parent form and then use the {Parent} merge tag to capture the ID in
 * a child field.
 */
add_filter( 'gform_field_value_gpnf_parent_entry_id', function( $value, $field ) {

	// GV condition was pulled from another snippet and is untested.
	if ( is_callable( 'gravityview_get_context' ) && gravityview_get_context() === 'edit' ) {
		$value = GravityView_frontend::is_single_entry();
	} else {
		$session = new GPNF_Session( $field->formId );
		$cookie  = $session->get_cookie();
		if ( $cookie ) {
			$value = rgar( $cookie, 'hash', '' );
		} else {
			// The first time the parent form loads there will be no cookie. Create a hash and set it as the default
			// hash for this cookie created for this form.
			$value = $session->make_hashcode();
			add_filter( "gpnf_session_script_data_{$field->formId}", function( $data ) use ( $value ) {
				$data['hash'] = $value;
			} );
		}
	}

	return $value;
}, 10, 2 );
