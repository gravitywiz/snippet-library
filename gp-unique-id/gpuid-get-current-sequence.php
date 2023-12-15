<?php
/**
 * Gravity Perks // Unique ID // Get Current Sequence Value
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 */
function gpuid_get_current_sequence( $form_id, $field_id ) {
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "select current from {$wpdb->prefix}gpui_sequence where form_id = %d and field_id = %d", $form_id, $field_id ) );
}

// Update "123" to your form ID and "4" to your Sequential Unique ID field.
$current_sequence = gpuid_get_current_sequence( 123, 4 );
