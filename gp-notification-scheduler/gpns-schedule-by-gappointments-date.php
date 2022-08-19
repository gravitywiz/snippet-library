<?php
/**
 * Gravity Wiz // Notification Scheduler // Schedule Notifications by gAppoinments Date
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 *
 * Use this snippet to map the date selected in a gAppointments Booking Calendar field to a Gravity Forms Date field.
 * Then, schedule your notifications based on the Date field.
 */
// Update "123" to the ID of your form.
add_action( 'gform_pre_submission_123', function( $form ) {
	// Update "4" to the ID of your Booking Calendar field.
	$appointment = unserialize( $_POST['input_4'] );
	// Update "5" to the ID of your Date field.
	$_POST['input_5'] = rgar( $appointment, 'date' );
} );
