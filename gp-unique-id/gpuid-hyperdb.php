<?php
/**
 * Gravity Perks // GP Unique ID // HyperDB Support for Sequential IDs
 * http://gravitywiz.com/documentation/gravity-forms-unique-id/
 */
add_filter( 'gpui_sequential_unique_id_pre_insert', function ( $uid, $form_id, $field_id, $starting_number ) {
	global $wpdb;

	$wpdb->query( 'START TRANSACTION' );

	$wpdb->query( $wpdb->prepare(
		'INSERT INTO ' . $wpdb->prefix . 'gpui_sequence ( form_id, field_id, current ) VALUES ( %d, %d, ( @next := 1 ) ) ON DUPLICATE KEY UPDATE current = ( @next := current + 1 )',
		$form_id,
		$field_id
	) );

	$uid = $wpdb->get_var( $wpdb->prepare( 'SELECT `current` from ' . $wpdb->prefix . 'gpui_sequence where form_id = %d and field_id = %d', $form_id, $field_id ) );

	$wpdb->query( 'COMMIT' );

	return $uid;
}, 10, 5 );
