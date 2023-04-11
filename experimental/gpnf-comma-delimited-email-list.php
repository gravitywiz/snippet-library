<?php
/**
 * Gravity Perks // Nested Forms // Convert Simple List to Comma-delimited List of Emails
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Requires the All Fields Template plugin: https://gravitywiz.com/gravity-forms-all-fields-template/
 *
 * Example usage: {My Nested Form Field:1:filter[2]:listemails}
 *
 *   - where "1" is the ID of yoru Nested Form field.
 *   - "2" is the ID of the Email field on your child form.
 *   - and the "listemails" modifier activates this snippet.
 */
add_filter( 'gp_template_output_nested-entries-simple-list', function( $markup, $located_template, $load, $args ) {
	if ( strpos( $args['modifiers'], 'listemails' ) ) {
		$markup = array();
		foreach ( $args['items'] as $item ) {
			$markup[] = strip_tags( $item['value'] );
		}
		$markup = implode( ',', $markup );
	}
	return $markup;
}, 10, 4 );
