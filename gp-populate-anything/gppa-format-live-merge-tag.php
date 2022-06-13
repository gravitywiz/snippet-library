<?php
/**
 * Gravity Perks // Populate Anything // Format Live Merge Tag Depending on another field
 * http://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Installation instructions:
 *   1. https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Update FORMID, FIELDID, and the $style_field_id variable accordingly. FIELDID should be the paragraph field.
 */
add_filter( 'gppa_live_merge_tag_value_FORMID_FIELDID', function( $merge_tag_match_value, $merge_tag, $form, $field_id, $entry_values ) {
	$style_field_id = 5;
	$style          = rgar( $entry_values, $style_field_id );

	if ( $style !== 'Bold First 3 Words' ) {
		return $merge_tag_match_value;
	}

	return preg_replace( '/(^\w+ \w+ \w+)/', '<strong>$1</strong>', $merge_tag_match_value );
}, 10, 5 );