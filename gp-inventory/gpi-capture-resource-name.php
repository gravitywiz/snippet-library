<?php
/**
 * Gravity Perks // Inventory // Capture Resource Name (as Field Value)
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * If you intend to map different Resources to different fields throughout the life of your form, you may wish to capture
 * the current Resource at the time of submission and save that value to a field. This snippet can help.
 *
 * Instructions
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Enable "Allow field to be populated dynamically" option under the "Advanced" field settings for the field
 *    in which you would like to capture the Resource name.
 *
 * 3. Set the parameter name to `gpi_capture_resource_1` replacing the "1" with the GPI-enabled field ID for which
 *    you would like to capture the current resource.
 */
add_action( 'gform_field_value', function( $value, $field, $name ) {

	if ( strpos( $name, 'gpi_capture_resource' ) !== false ) {
		$resource = gpi_get_resource_by_parameter( $name, $field->formId );
		$value    = $resource->post_title;
	}

	return $value;
}, 10, 3 );

add_action( 'gform_after_submission', function( $entry, $form ) {

	foreach ( $form['fields'] as &$field ) {
		if ( strpos( $field->inputName, 'gpi_capture_resource' ) === false ) {
			continue;
		}

		$resource = gpi_get_resource_by_parameter( $field->inputName, $field->formId );

		GFAPI::update_entry_field( $entry['id'], $field->id, $resource->post_title );
	}

}, 10, 2 );

if ( ! function_exists( 'gpi_get_resource_by_parameter' ) ) {
	function gpi_get_resource_by_parameter( $parameter, $form_id ) {

		$bits             = explode( '_', $parameter );
		$product_field_id = array_pop( $bits );

		$product_field = GFAPI::get_field( GFAPI::get_form( $form_id ), $product_field_id );

		return get_post( $product_field->gpiResource );
	}
}
