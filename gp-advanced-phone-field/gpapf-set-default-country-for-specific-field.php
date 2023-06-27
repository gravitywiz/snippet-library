<?php
/**
 * Gravity Perks // Advanced Phone Field // Set Default Country for a Specific Field
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 */
// Update "123" to your form ID and "4" to your Advanced Phone Field's ID.
add_filter( 'gpapf_init_args_123_4', function( $args ) {
	$args['defaultCountry'] = 'GB';
	return $args;
} );
