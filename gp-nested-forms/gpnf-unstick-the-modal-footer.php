<?php
/**
 * Gravity Perks // Nested Forms // Unstick the Modal Footer
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gpnf_init_script_args', function( $args ) {
	$args['modalStickyFooter'] = false;
	return $args;
} );
