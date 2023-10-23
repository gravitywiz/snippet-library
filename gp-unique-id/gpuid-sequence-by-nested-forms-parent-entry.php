<?php
/**
 * Gravity Perks // Unique ID // Unique Sequences by Nested Forms Parent Entry
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 */
// Update "123" to your child form ID and "4" to your Unique ID field ID.
add_filter( 'gpui_unique_id_attributes_123_4', function ( $atts, $form_id, $field_id ) {

	// Update "5" to the ID of the field on your child form that is capturing the Nested Forms session hash.
	$session_hash = rgpost( 'input_5' );
	if ( empty( $session_hash ) ) {
		return $atts;
	}

	$atts['slug'] = "gpnf-session-hash-{$session_hash}";

	return $atts;
}, 10, 3 );
