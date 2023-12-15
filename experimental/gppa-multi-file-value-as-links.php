<?php
/**
 * Gravity Perks // Populate Anything // Format Comma-delimited List of Files as Links
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Populate a Single Line Text field with the value of a Multi-file Upload field and then use this snippet to display
 * the file URLs as links instead of plain text.
 */
// Update "123" to your form ID and "4" to the field ID being populated with your file URLs.
add_filter( 'gppa_live_merge_tag_value_348_2', function( $merge_tag_match_value, $merge_tag, $form, $field_id, $entry_values ) {
	$urls  = explode( ', ', $merge_tag_match_value );
	$links = array();
	foreach ( $urls as $url ) {
		$links[] = sprintf( '<a href="%s">%s</a>', $url, basename( $url ) );
	}
	return implode( '<br>', $links );
}, 10, 5 );
