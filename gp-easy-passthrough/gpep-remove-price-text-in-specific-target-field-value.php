<?php
/**
 * Gravity Perks // Easy Passthrough // Remove Price at the End of a Specific Target Field’s Value.
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 */
 // Update '123' to the Tartget Form ID and '4' to the Target field ID.
add_filter('gpep_target_field_value_123_4', function( $field_value ) {
	return preg_replace( '/\s?\(.*?\)$/', '', $field_value );
});
