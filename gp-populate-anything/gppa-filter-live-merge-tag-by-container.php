<?php
/**
 * Gravity Perks // Populate Anything // Filter Live Merge Tag Value by Container Field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * The `gppa_live_merge_tag_value` provides a powerful method for filtering the value of a live merge tag but it does not
 * allow you to modify the value based on the field in which the live merge tag is being displayed (e.g. the "container" field).
 *
 * Use this combination of filters to limit your live merge tag value filter to a specific container field.
 */
// Update "123" to your form ID and "4" to the field that contains the live merge tag.
add_filter( 'gppa_hydrated_field_123_4', function( $field ) {
	// Update "5" to the ID of the field targeted by the live merge tag.
	$live_merge_tag_field_id = 5;
	$func = function( $merge_tag_match_value, $merge_tag, $form, $field_id, $entry_values ) use ( &$func, $live_merge_tag_field_id ) {
		remove_filter( "gppa_live_merge_tag_value_{$form['id']}_{$live_merge_tag_field_id}", $func );
		return $merge_tag_match_value . ' FILTERED!';
	};
	add_filter( "gppa_live_merge_tag_value_{$field->formId}_{$live_merge_tag_field_id}", $func, 10, 5 );
	return $field;
} );
