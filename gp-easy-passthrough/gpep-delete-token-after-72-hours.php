<?php
/**
 * Gravity Perks // Easy Passthrough // Delete EP Tokens After 72 Hours
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * This snippet intercepts requests that contain an EP token and deletes the token if the
 * entry was created more than 72 hours ago.
 */
add_action( 'init', function() {

	if ( ! isset( $_GET['ep_token'] ) ) {
		return;
	}

	$ep_token = $_GET['ep_token'];

	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array(
			array(
				'key'   => 'fg_easypassthrough_token', // Adjust the key if necessary
				'value' => $ep_token,
			),
		),
	);

	// Get entries that match the criteria
	$entry = rgar( GFAPI::get_entries( 0, $search_criteria ), 0, false );
	if ( ! $entry ) {
		return;
	}

	// Check if the entry was created more than 72 hours ago
	$date_created = strtotime( $entry['date_created'] );
	$hours_diff = ( time() - $date_created ) / 3600;

	if ( $hours_diff > 72 ) {
		// Delete the EP token meta from the entry.
		gform_delete_meta( $entry['id'], 'fg_easypassthrough_token' );
	}

} );
