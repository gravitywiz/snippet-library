<?php
/**
 * Gravity Perks // Bookings // Disable Automatic Booking Completed Notifications
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Prevent "Booking Completed" notifications from being sent automatically when a booking
 * ends. The notification will still be sent when a booking is manually marked as completed.
 */
add_action( 'wp_loaded', function() {
	remove_action(
		'gpb_check_completed_bookings',
		array(
			\GP_Bookings\Notifications\Notifications::class,
			'check_and_send_completion_notifications',
		),
		10
	);
}, PHP_INT_MAX );
