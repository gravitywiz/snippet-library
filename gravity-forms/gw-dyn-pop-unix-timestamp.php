<?php
/**
 * Gravity Wiz // Gravity Forms // Dynamically Populate Unix Timestamp
 * http://gravitywiz.com/use-gravity-forms-conditional-logic-with-dates/
 *
 * Set a field's dynamic population parameter to "timestamp" to populate the current Unix timestamp.
 */
add_filter( 'gform_field_value_timestamp', function ( $value ) {
	return time();
} );
