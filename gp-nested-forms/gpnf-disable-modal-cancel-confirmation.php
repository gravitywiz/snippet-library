<?php
/**
 * Gravity Perks // Nested Forms // Disable Modal Cancel Confirmation
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This snippet removes the confirmation prompt when canceling a Nested Form modal. 
 * Normally, when a user attempts to close the modal without saving, they are asked 
 * to confirm their action. With this snippet, the modal closes immediately.
 *
 * Instructions:
 *
 * 1. Install this snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 */
add_filter( 'gpnf_init_script_args', function( $args, $field, $form ) {
	$args['modalLabels']['confirmAction'] = false;
	return $args;
}, 10, 3 );
