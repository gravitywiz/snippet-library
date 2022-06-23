<?php
/**
 * GP Unique ID // Gravity Perks // Fill Gaps in Sequential Unique IDs
 * http://gravitywiz.com/documentation/gp-unique-id/
 *
 * Use this snippet to automatically fill gaps in the sequence of unique IDs for Unique ID fields of the "Sequential"
 * type. Gaps will occur when entries containing a sequential unique ID are deleted.
 *
 * Update the filter name "gpui_sequential_unique_id_pre_insert_519_5", replacing "519" with your form ID and "5" with
 * your field ID.
 */
add_filter( 'gpui_sequential_unique_id_pre_insert_519_5', function ( $uid, $form_id, $field_id ) {
	global $wpdb;

	$result = $wpdb->get_results( $wpdb->prepare( "
		select value 
		from {$wpdb->prefix}rg_lead_detail 
		where form_id = %d and field_number = %d", $form_id, $field_id ) );

	if ( empty( $result ) ) {
		return $uid;
	}

	$_uids   = wp_list_pluck( $result, 'value' );
	$form    = GFAPI::get_form( $form_id );
	$field   = GFFormsModel::get_field( $form, $field_id );
	$numbers = array();

	foreach ( $_uids as $_uid ) {

		$clean_uid = str_replace( $field['gp-unique-id_prefix'], '', $_uid );
		$clean_uid = str_replace( $field['gp-unique-id_suffix'], '', $clean_uid );
		$clean_uid = intval( ltrim( $clean_uid, '0' ) );

		$numbers[] = $clean_uid;

	}

	sort( $numbers );

	$range = range( 1, max( $numbers ) );
	$diff  = array_diff( $range, $numbers );

	// looks like we've found a gap in the sequence; busted!
	if ( ! empty( $diff ) ) {
		$uid = array_shift( $diff );
	}

	return $uid;
}, 10, 3 );
