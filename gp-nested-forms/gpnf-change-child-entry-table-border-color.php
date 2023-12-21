<?php
/**
 * Gravity Perks // Nested Forms // Change Child Entry Table Border Color
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Change the top border color that appears for individual child entry tables in the {all_fields} and Nested Form field
 * merge tags for Nested Form fields.
 */
add_filter( 'gp_template_output_nested-entries-all', function( $markup ) {
	// Replace top border color of individual child entry table in {all_fields}.
	$markup = str_replace( '#faebd2', '#f00', $markup );
	// Replace top border color of individual child entry table in Nested Form field merge tag.
	$markup = str_replace( '#d2e6fa', '#0f0', $markup );
	return $markup;
} );
