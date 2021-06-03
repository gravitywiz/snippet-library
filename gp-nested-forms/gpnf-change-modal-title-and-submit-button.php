<?php
/**
 * Gravity Perks // Nested Forms // Change Modal Title and Submit Button 
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
// Update "123" to your form ID and "4" to your Nested Form field ID.
add_filter( 'gpnf_init_script_args_123_4', function( $args ) {
	$args['modalLabels']['title'] = 'Create New Child Entry';
	$args['modalLabels']['editTitle'] = 'Edit Child Entry';
	$args['modalLabels']['submit'] = 'Submit';
	$args['modalLabels']['editSubmit'] = 'Edit';
	return $args;
} );
