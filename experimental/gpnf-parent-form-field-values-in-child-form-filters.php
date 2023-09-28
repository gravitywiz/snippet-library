<?php
/**
 * Gravity Perks // Nested Forms // Fetch Parent form values when prerendering Child form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This demonstrates a proof-of-concept for storing and retrieving parent form field values for use in the
 * `gform_pre_render` filter when modifying the child form.
 * 
 * NOTE: The parent form must not have AJAX-enabled for this snippet to work.
 */
// Update "123" to your parent form ID.
add_filter( 'gpnf_init_script_args_123', function( $args ) {
	if ( ! empty( $_POST ) ) {
    // Update "1", "2", and "3" to the field IDs of parent form fields for which you would like to store the value.
		$keys = array( 'input_1', 'input_2', 'input_3' );
		$args['ajaxContext']['parent_form_values'] = array_intersect_key( $_POST, array_flip( $keys ) );
	}
	return $args;
} );

// Update "124" to your child form ID.
add_filter( 'gform_pre_render_124', function ( $form ) {
	if ( in_array( rgar( $_REQUEST, 'action' ), array( 'gpnf_refresh_markup', 'gpnf_edit_entry' ) ) ) {
		$parent_form_values = $_REQUEST['gpnf_context']['parent_form_values'];
    // Update "3" to the ID of the parent form field for which you would like to fetch the stored value.
		$field_value        = rgar( $parent_form_values, 'input_3' );
	}
	return $form;
} );
