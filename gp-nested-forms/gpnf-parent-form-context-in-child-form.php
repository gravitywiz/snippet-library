<?php
/**
 * Gravity Perks // Nested Forms // Get Parent Form Context from Child Form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Nested Form fields fetch the child form markup via an AJAX request after the parent form has loaded. This
 * means that when loading child form markup, the child form's context is not the same as the parent's. However,
 * most of the parent form context can be retrieved via the `gpnf_context` parameter passed in the AJAX request.
 *
 * Here is an example where we fetch the "my_id" query parameter that was set on the page on which the parent
 * form was loaded.
 */
add_filter( 'gform_pre_render', function( $form ) {
	if ( in_array( rgar( $_REQUEST, 'action' ), array( 'gpnf_refresh_markup', 'gpnf_edit_entry' ) ) ) {
		$my_id = rgars( $_REQUEST, 'gpnf_context/request/my_id' );
	}
	return $form;
} );
