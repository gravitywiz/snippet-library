<?php
/**
 * Gravity Perks // Nested Forms // Modify Labels for Nested Entries Table
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/f880c6c804614d24a2cb79c83c8e5981
 *
 * It can be helpful to have longform field labels in your child form (like "How many pets do you have?") and shorter
 * field labels when this field is presented in the Nested Forms field (like "No. of Pets").
 */
// Update "123" to your parent form ID and "4" to your Nested Form field ID.
add_filter( 'gpnf_template_args_123_4', function( $args ) {
	if ( $args['template'] === 'nested-entries' ) {
		foreach ( $args['nested_fields'] as &$nested_field ) {
			switch ( $nested_field->id ) {
				// Update "1" to the field ID on your child form for which you would like to provide a shorter label.
				case 1:
					$nested_field->label = 'Choice';
					break;
				// Add a new "case" statement for each field you need to customize.
				case 2:
					$nested_field->label = 'Text';
					break;
			}
		}
	}
	return $args;
} );
