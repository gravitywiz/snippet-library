<?php
/**
 * Gravity Perks // Entry Blocks // Disable Field Conditional Logic When Editing
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Removes conditional logic functionality on Entry Blocks' Edit Page.
 */
add_filter( 'gpeb_edit_form', function ( $form ) {
	foreach ( $form['fields'] as &$field ) {
		$field['conditionalLogic'] = '';
	}

	return $form;
} );
