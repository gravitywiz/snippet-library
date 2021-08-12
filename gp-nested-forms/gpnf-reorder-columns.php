<?php
/**
 * Gravity Perks // GP Nested Forms // Reorder Nested Form Field Columns
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Reorder the columns in the Nested Form Field summary table.
 *
 * Plugin Name:  GP Nested Forms — Reorder Nested Form Field Columns
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Reorder the columns in the Nested Form Field summary table.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */

// Update "123" to the ID of your form.
add_filter( 'gform_form_post_get_meta_123', function( $form ) {
	foreach( $form['fields'] as &$field ) {
	        // Update "4" to the ID of your Nested Form field.
		      if( $field->id == 4 ) {
		        // Update "5", "6" and "7" to the desired field IDs from your child form.
          	$field->gpnfFields = array( 7, 5, 6 );
		}
	}
	return $form;
} );
