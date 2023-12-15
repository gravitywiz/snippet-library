<?php
/**
 * Gravity Perks // Unique ID // Set Minimum Length for Numeric Unique ID
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 */
add_filter( 'gpui_numeric_minimum_length', function() {
	// Update "3" to your desired minimum length.
	return 3;
} );
