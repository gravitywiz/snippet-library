<?php
/**
 * Gravity Perks // Populate Anything // Add Line Break Between Live Merge Tag Checkbox Values
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/0cfd96b62c264b5faf48fa0498b49124
 */
// Update "123" with your form ID and "4" with your Checkbox field ID.
add_filter( 'gppa_live_merge_tag_value_123_4', function( $value, $merge_tag, $form, $field_id, $entry_values ) {
	$values = array_map( 'trim', explode( ',', $value ) );
	return implode( '<br>', $values );
}, 10, 5 );
