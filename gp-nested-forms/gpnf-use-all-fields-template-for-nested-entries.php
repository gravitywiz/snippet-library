<?php
/**
 * Gravity Perks // Nested Forms // Use All Fields Template for Nested Entries Output
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Use the default configured All Fields Template (see: https://gravitywiz.com/gravity-forms-all-fields-template/) when
 * displaying child entries in Nested Form fields via the {all_fields} and Nested Form field merge tags.
 */
add_filter( 'gp_template_output_nested-entry', function( $output, $template, $load, $args ) {
	return gw_all_fields_template()->replace_merge_tags( "{all_fields:{$args['modifiers']}}", $args['nested_form'], $args['entry'], false, false, false, 'html' );
}, 10, 4 );
