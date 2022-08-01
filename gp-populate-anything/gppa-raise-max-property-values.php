<?php
/**
 * Gravity Perks // GP Populate Anything // Add Menu Order Property for Post Object
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_max_property_values_in_editor', function( $max_property_values ) {
	return 2500;
} );
