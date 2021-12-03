<?php
/**
 * Gravity Perks // Nested Forms // Get Session Hash
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Use this function to get the parent hash that will be assigned to child entries created on in this session for a
 * given form ID.
 *
 * GPNF sets the hash via an AJAX call the first time the form is rendered. In order to reliably fetch the hash you must
 * call this function before the `gpnf_session_script_data` filter is called.
 */
function gpnf_session_hash( $form_id ) {

	$session = new GPNF_Session( $form_id );
	$cookie  = $session->get_cookie();

	if ( empty( $cookie ) ) {
		$hash = $session->make_hashcode();
		add_filter( "gpnf_session_script_data_{$form_id}", function( $data ) use ( $hash ) {
			$data['hash'] = $hash;
			return $data;
		} );
	} else {
		$hash = $cookie['hash'];
	}

	return $hash;
}

/**
 * Here is an example of an appropriate time to fetch the parent hash.
 */
add_filter( 'gform_pre_render', function( $form ) {

	if ( wp_doing_ajax() ) {
		return $form;
	}

	$nested_form_fields = GFAPI::get_fields_by_type( $form, 'form' );
	if ( empty( $nested_form_fields ) ) {
		return false;
	}

	$hash = gpnf_session_hash( $form );

	// Add your code here!

	return $form;
} );
