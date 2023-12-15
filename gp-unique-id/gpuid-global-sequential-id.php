<?php
/**
 * Gravity Perks // Unique ID // Share a global sequential unique ID across all forms
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Sets a global sequential unique ID across all forms on the website.
 *
 * To use this snippet, add Unique ID fields on all the forms you'd like to generate Unique IDs,
 * and update the $atts variable within the snippet to target any of the forms and its Unique ID field.
 *
 * Plugin Name:  GP Unique ID — Global Sequential ID
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-unique-id/
 * Description:  Sets a global sequential unique ID across all forms on the website.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gpui_unique_id_attributes', 'gwiz_unique_id_global_sequential_index', 10, 3 );
function gwiz_unique_id_global_sequential_index( $atts, $form_id, $field_id ) {

	if ( $atts['type'] == 'sequential' ) {
		$atts['starting_number'] = 1;
		$atts['form_id']         = 1;
		$atts['field_id']        = 1;
	}

	return $atts;
}
