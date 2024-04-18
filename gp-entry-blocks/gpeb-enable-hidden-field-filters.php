<?php
/**
 * Gravity Perks // Entry Blocks // Enable Hidden Field Filters
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Hidden Fields are not displayed as Entry Blocks Filters by default. This snippet overrides that to enable Hidden Fields on Filters.
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
