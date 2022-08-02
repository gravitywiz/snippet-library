<?php
/**
 * Gravity Perks // Nested Forms // Change Template for GravityView Entry List View
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gpnf_template_args_123_4', function ( $args, $field ) {

	if ( gravityview()->request->is_view() && ( ! gravityview()->request->is_entry() || ! gravityview()->request->is_edit_entry() ) ) {
		// Specify the template you would like to use.
		$args['template'] = 'nested-entries-detail-simple';
	}

	return $args;
}, 10, 2 );
