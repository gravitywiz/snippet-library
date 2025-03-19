<?php
/**
 * Gravity Perks // Populate Anything // Change "Fill Out Other Fields" Text
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_missing_filter_text', function( $value, $field ) {
	if ( $field->id == 4 ) {
		$value = 'Use Search Field Above to Get Results';
	}

	return $value;
}, 10, 2 );
