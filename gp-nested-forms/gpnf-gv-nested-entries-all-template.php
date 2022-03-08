<?php
/**
 * Gravity Perks // Nested Forms // Use "Nested Entries All" Template for GravityView's Single Entry View
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gpnf_template_args', function( $args ) {
	if ( $args['template'] === 'nested-entries-detail-simple' && $args['field']->is_gravityview() ) {
		$args['template'] = 'nested-entries-all';
	}
	return $args;
} );
