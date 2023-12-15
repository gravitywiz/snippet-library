<?php
/**
 * Gravity Perks // Easy Passthrough // Save EP Token to Field
 * http://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * This snippet allows you to populate the EP token into a field so it can be mapped in Gravity Forms feeds. The filter
 * fires before feeds are processed so the token is available in time.
 */
// Update "123" to your form ID.
add_filter( 'gform_entry_post_save_123', function( $entry, $form ) {

	// Update "4" to the ID of the field you woule like to populate with the EP token.
	$target_field_id = 4;

	if ( is_callable( 'gp_easy_passthrough' ) ) {
		$token = gp_easy_passthrough()->get_entry_token( $entry );
		GFAPI::update_entry_field( $entry['id'], $target_field_id, $token );
		$entry[ $target_field_id ] = $token;
	}

	return $entry;
}, 5, 2 );
