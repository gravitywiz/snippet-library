<?php
/**
 * Gravity Perks // Populate Anything // Populate Gravity PDF URL via Live Merge Tag
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Use this snippet to convert an entry ID value from one field to a Gravity PDF URL for population in another field via
 * Live Merge Tags.
 *
 * Instructions:
 *
 * 1. Add the :gravitypdf modifier to any Live Merge Tag for a field that contains an entry ID value.
 * 2. Replace "643772a552da1" (in the code below) with the ID of the PDF you'd like to generate.
 */
add_filter( 'gppa_live_merge_tag_value', function( $value, $merge_tag, $form, $field_id, $entry_values ) {
	if ( strpos( $merge_tag, ':gravitypdf' ) !== false ) {
		$pdf = GPDFAPI::get_mvc_class( 'Model_PDF' );
		// Update "643772a552da1" with the ID of the PDF you'd like to generate.
		return $pdf->get_pdf_url( '643772a552da1', $value, false, false );
	}
	return $value;
}, 10, 5 );
