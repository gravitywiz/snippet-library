<?php
/**
 * Gravity Perks // Nested Forms // Add Custom Classes to Add Entry Button
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/bfa19506533a42f98fb22cf5b951dc3a
 *
 */
add_filter( 'gpnf_template_args', function( $args ) {
	if ( $args['template'] === 'nested-entries' ) {
		$append_classes     = 'class-a class-b';
		$args['add_button'] = str_replace( 'gpnf-add-entry', 'gpnf-add-entry ' . $append_classes, $args['add_button'] );
	}
	return $args;
} );
