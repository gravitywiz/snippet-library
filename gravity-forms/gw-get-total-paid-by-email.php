<?php
/**
 * Gravity Wiz // Gravity Forms // Get Total Paid by Email
 * https://gravitywiz.com/
 *
 * Use this helper function to get the total paid across all forms for a email address.
 *
 * Example:
 *
 * $total = gw_get_total_by_email( 'dave@smiff.com' );
 */
function gw_get_total_by_email( $email, $form_ids = null ) {
	$entries = gfapi::get_entries( $form_ids, array(
		'field_filters' => array(
			array( 'value' => $email ),
			array(
				'key'   => 'payment_status',
				'value' => 'paid',
			),
		),
	) );
	$total   = 0;
	foreach ( $entries as $entry ) {
		$total += floatval( $entry['payment_amount'] );
	}
	return $total;
}
