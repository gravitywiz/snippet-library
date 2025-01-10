<?php
/**
 * Gravity Perks // Nested Forms // Always Includes Child Entries from Session
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * By default, if child entries are populated into a Nested Form field via any type of pre-population mechanic, child
 * entries from the session will not be included. This snippet overrides this behavior and always includes child entries
 * in the current session.
 */
add_filter( 'gpnf_submitted_entry_ids', function( $entry_ids, $form, $field ) {

	$session  = new GPNF_Session( $form['id'] );
	$_entries = $session->get( 'nested_entries' );
	if ( ! empty( $_entries[ $field->id ] ) ) {
		$entry_ids = array_merge( $entry_ids, $_entries[ $field->id ] );
	}

	// When traversing multi-page forms, entry IDs will be duplicated from the session and the posted entry ID values.
	// Ensure each unique entry ID is only included once.
	return array_unique( $entry_ids );
}, 10, 3 );
