<?php
/**
 * Gravity Perks // Populate Anything // Disable GFML gform_pre_render callback
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Bypass processing forms with Gravity Forms Multilingual when pulling results for Populate Anything via AJAX.
 *
 * The reason for this is due to the 'gform_pre_render' method in Gravity_Forms_Multilingual using a runtime cache to
 * cache all forms which interferes with Populate Anything.
 *
 * Instructions:
 *  1. https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *  2. Update the $form_ids_to_skip_gfml variable accordingly.
 */
add_action( 'init', function () {

	if ( ! isset( $GLOBALS['wpml_gfml_tm_api'] ) || ! wp_doing_ajax() ) {
		return;
	}

	if ( ! function_exists( 'gp_populate_anything' ) || ! function_exists( 'rgar' ) ) {
		return;
	}

	if ( rgar( $_REQUEST, 'action' ) !== 'gppa_get_batch_field_html' ) {
		return;
	}

	$data = gp_populate_anything()::maybe_decode_json( WP_REST_Server::get_raw_data() );

	// Update the form IDs below
	$form_ids_to_skip_gfml = array( 123, 456 );

	if ( ! in_array( rgar( $data, 'form-id' ), $form_ids_to_skip_gfml ) ) {
		return;
	}

	remove_filter( 'gform_pre_render', array( $GLOBALS['wpml_gfml_tm_api'], 'gform_pre_render' ) );

} );
