<?php
/**
 * Gravity Perks // Nested Forms // Use "Nested Entries Simple" Template for GravityView's Entries View
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gpnf_template_args', function ( $args ) {
	if ( $args['template'] === 'nested-entries-count' && function_exists( 'gravityview' ) && gravityview()->request->is_renderable() ) {
		$args['template'] = 'nested-entries-detail-simple';
	}

	return $args;
} );
