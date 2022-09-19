<?php
/**
 * Gravity Perks // Populate Anything // Format LMT as Bulleted List in RTE
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Take a comma-delimited value (like that from a Multi Select field) and convert it into a bulleted list when it is
 * populated into a Rich-Text-Editor-enabled Paragraph Text field via its Live Merge Tag.
 */
// Update "123" with your form ID and "4" with your Checkbox field ID.
add_filter( 'gppa_live_merge_tag_value_123_4', function( $value, $merge_tag, $form, $field_id, $entry_values ) {

	$values = array_map( 'trim', explode( ',', $value ) );
	if ( empty ( $values ) ) {
		return $value;
	}

	return sprintf( '<ul><li>%s</li></ul>', implode( '</li><li>', $values ) );
}, 10, 5 );
