<?php
/**
 * Gravity Perks // Entry Blocks // Enable Hidden Field Filters
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Rather than displaying 50 checkboxes when using a Checkbox field in the Filter block, allow Entry Blocks
 * to convert the Checkbox field to a Single Line Text field in the Filter block context. Users can then
 * search for any checkbox value.
 */
add_filter( 'gpeb_filter_form', function ( $form ) {

	foreach ( $form['fields'] as &$field ) {
		// Update Hidden Field visibility.
		$field->visibility = 'visible';

		// Replace "Hidden" Field with Text Field to ensure it's rendered on the Filters
		if ( $field->type === 'hidden' ) {
			$field = new GF_Field_Text( $field );
			$field->inputType = 'text';
		}
	}

	return $form;
}, 10, 1 );
