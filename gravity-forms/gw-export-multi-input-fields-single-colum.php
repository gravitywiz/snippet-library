<?php
/**
 * Gravity Wiz // Gravity Forms // Export Multi-input Fields in a Single Column
 *
 * By default, Gravity Forms only allows you to export each input of a multi-input field (e.g. Checkbox field,
 * Name Field, etc) as a separate column. This snippet allows you to export all inputs (of a specific field) in a
 * single column.
 *
 * Plugin Name: Gravity Forms - Export Multi-input Fields in Single Column
 * Plugin URI: http://gravitywiz.com/how-do-i-export-multi-input-fields-in-a-single-column-with-gravity-forms/
 * Description: Export multi-input Gravity Forms fields as a single column.
 * Author: David Smith
 * Version: 1.2
 * Author URI: http://gravitywiz.com
 */
add_filter( 'gform_export_fields', function( $form ) {

	// only modify the form object when the form is loaded for field selection; not when actually exporting
	if ( rgpost( 'export_lead' ) || rgpost( 'action' ) == 'gf_process_export' ) {
		return $form;
	}

	$fields = array();

	foreach ( $form['fields'] as $field ) {
		if ( is_a( $field, 'GF_Field' ) && is_array( $field->inputs ) ) {
			$orig_field    = clone $field;
			$field->inputs = null;
			$fields[]      = $field;
			$fields[]      = $orig_field;
		} else {
			$fields[] = $field;
		}
	}

	$form['fields'] = $fields;

	return $form;
} );
