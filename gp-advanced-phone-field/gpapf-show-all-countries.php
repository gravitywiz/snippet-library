<?php
/**
 * Gravity Perks // Advanced Phone Field // Show All Countries On Specific Field
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * This snippet will show all countries in the flag picker for a specific form/field. It is useful if you have limited to specific countries
 * on the plugin settings, but want to allow all countries on a specific form/field.
 */
// Update "123" to your form ID and "4" to your Phone field ID.
add_filter( 'gpapf_init_args_123_4', function( $args ) {
	$args['countriesAction'] = 'all';
	return $args;
} );
