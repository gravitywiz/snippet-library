<?php
/**
 * Gravity Perks // Unique ID // Set Minimum Numeric Unique ID Length
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 */
add_filter( 'gpui_numeric_minimum_length', function() { 
	return 3;
} );
