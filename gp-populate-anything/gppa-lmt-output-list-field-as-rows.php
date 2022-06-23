<?php
/**
 * Gravity Perks // Populate Anything // Output List Field as Rows
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to your List field ID.
add_filter( 'gppa_live_merge_tag_value_123_4', function( $merge_tag_match_value, $merge_tag, $form, $field_id, $entry_values ) {
	$field  = GFAPI::get_field( $form, $field_id );
	$rows   = $field->create_list_array( $entry_values[ $field_id ] );
	$output = array();
	foreach ( $rows as $row ) {
		$output[] = implode( ' ', $row );
	}
	return nl2br( implode( "\n", $output ) );
}, 10, 5 );
