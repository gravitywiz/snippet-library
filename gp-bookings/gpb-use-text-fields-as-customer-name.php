<?php
/**
 * Gravity Perks // Bookings // Use Text Fields as Customer Name
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Maps plain text fields to be used as the customer name in the
 * GP Bookings dashboard (Upcoming Bookings, etc.).
 *
 * Credit: Gui Lamu
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the `$name_fields` array below with your form ID
 *    and field IDs for first name and last name.
 */
add_filter( 'gpb_customer_name', function( $name, $entry, $booking ) {
	if ( ! is_array( $entry ) ) {
		return $name;
	}

	// Update "1" to your form ID. Update "9" to your First Name field ID and "10" to your Last Name field ID.
	$name_fields = array(
		1 => array( 9, 10 ),
	);

	$form_id = (int) rgar( $entry, 'form_id' );
	if ( ! isset( $name_fields[ $form_id ] ) ) {
		return $name;
	}

	$parts = array();
	foreach ( $name_fields[ $form_id ] as $field_id ) {
		$value = trim( (string) rgar( $entry, (string) $field_id ) );
		if ( '' !== $value ) {
			$parts[] = $value;
		}
	}

	return ! empty( $parts ) ? implode( ' ', $parts ) : $name;
}, 10, 3 );
