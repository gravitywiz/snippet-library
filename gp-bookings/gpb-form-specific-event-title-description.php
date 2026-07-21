<?php
/**
 * Gravity Perks // Bookings // Form-Specific Event Title & Description
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Use different Google Calendar event titles and/or descriptions depending on which form
 * created the booking. This is useful when the same service is shared across several booking
 * forms and each form needs its own merge tags (which vary by field ID from form to form).
 *
 * Event settings are configured on the service, so the same template applies to every form
 * that books that service. Each rule below targets a specific service + form pair and overrides
 * the template for that combination. Templates are processed with the booking's own form and
 * entry, so form-specific merge tags (e.g. {:3}) resolve correctly.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the $rules below with a row per service + form. Omit 'title' or 'description' to
 *    leave that value as configured in the service's event settings.
 */
add_filter( 'gpb_google_calendar_event_data', function( $event_data, $booking ) {

	// Each rule targets a service_id + form_id. Values support GPB and Gravity Forms merge tags.
	$rules = array(
		array(
			'service_id'  => 42, // The GPB service this booking is for.
			'form_id'     => 1,
			'title'       => '{:3} - {:5}',
			'description' => 'Booking for {:3} - {gpb_datetime}',
		),
		array(
			'service_id'  => 42,
			'form_id'     => 2,
			'title'       => '{:7} with {gpb_service}',
			'description' => 'Location: {:9}',
		),
	);

	$entry = $booking->get_entry();
	if ( ! $entry ) {
		return $event_data;
	}

	$form_id    = (int) rgar( $entry, 'form_id' );
	$service_id = (int) $booking->get_service_id();

	foreach ( $rules as $rule ) {
		if ( (int) $rule['form_id'] !== $form_id || (int) $rule['service_id'] !== $service_id ) {
			continue;
		}

		if ( ! empty( $rule['title'] ) ) {
			$event_data['summary'] = \GP_Bookings\Merge_Tags::apply_template( $booking, $rule['title'] );
		}

		if ( ! empty( $rule['description'] ) ) {
			$event_data['description'] = \GP_Bookings\Merge_Tags::apply_template( $booking, $rule['description'] );
		}

		break;
	}

	return $event_data;
}, 10, 2 );
