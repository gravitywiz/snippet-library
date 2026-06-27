<?php
/**
 * Gravity Perks // Bookings // Delay Booking Completed Notifications
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Delay one or more "Booking Completed" notifications so they send a set amount of time
 * after a booking ends (e.g. a feedback request two days later) instead of immediately
 * when the booking is completed.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 3. Update the configuration at the bottom of the snippet.
 *
 * Notes:
 *  - The delay is measured from when the booking would have notified (within ~an hour
 *    of the booking ending), not the exact end time.
 *  - The listed notifications are sent only by this snippet's scheduled job, so manually
 *    resending them from the entry screen will not send while the snippet is active.
 */
class GPB_Delay_Completed_Notification {

	private $notification_ids;
	private $delay;
	private $delay_unit;
	private $action_hook    = 'gpb_send_delayed_completed_notification';
	private $is_dispatching  = false;

	private static $allowed_units = array( 'hours', 'days', 'weeks', 'months' );

	public function __construct( array $args ) {
		$args = wp_parse_args( $args, array(
			'notification_ids' => array(),
			'delay'            => 1,
			'delay_unit'       => 'days',
		));

		$this->notification_ids = array_filter( array_map( 'strval', (array) $args['notification_ids'] ) );
		$this->delay            = (float) $args['delay'];
		$this->delay_unit       = in_array( $args['delay_unit'], self::$allowed_units, true ) ? $args['delay_unit'] : 'days';

		if ( empty( $this->notification_ids ) || $this->delay <= 0 || ! function_exists( 'as_schedule_single_action' ) ) {
			return;
		}

		add_filter( 'gform_disable_notification', array( $this, 'reschedule_notification' ), 10, 4 );
		add_action( $this->action_hook, array( $this, 'send_notification' ), 10, 2 );
	}

	public function reschedule_notification( $is_disabled, $notification, $form, $entry ) {
		if ( $is_disabled || $this->is_dispatching ) {
			return $is_disabled;
		}

		if ( empty( $notification['id'] ) || ! in_array( (string) $notification['id'], $this->notification_ids, true ) ) {
			return $is_disabled;
		}

		$args = array( (int) $entry['id'], (string) $notification['id'] );

		if ( ! as_next_scheduled_action( $this->action_hook, $args ) && ! $this->already_sent( $args[0], $args[1] ) ) {
			as_schedule_single_action( strtotime( "+{$this->delay} {$this->delay_unit}" ), $this->action_hook, $args );
		}

		return true;
	}

	public function send_notification( $entry_id, $notification_id ) {
		if ( ! class_exists( '\GFCommon' ) || $this->already_sent( $entry_id, $notification_id ) ) {
			return;
		}

		if ( ! $this->booking_still_confirmed( $entry_id ) ) {
			return;
		}

		$entry = \GFAPI::get_entry( (int) $entry_id );
		if ( ! $entry || is_wp_error( $entry ) ) {
			return;
		}

		$form = \GFAPI::get_form( $entry['form_id'] );
		if ( ! $form ) {
			return;
		}

		// Record before dispatching so the notification cannot be sent twice.
		gform_update_meta( $entry_id, $this->sent_meta_key( $notification_id ), true );

		$this->is_dispatching = true;
		\GFCommon::send_notifications( array( $notification_id ), $form, $entry, true, 'gpb_booking_completed' );
		$this->is_dispatching = false;
	}

	private function booking_still_confirmed( $entry_id ): bool {
		global $wpdb;

		if ( ! class_exists( '\GP_Bookings\Database' ) ) {
			return false;
		}

		$booking_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT booking_id FROM %i
				 WHERE gf_entry_id = %d
				 AND object_type = 'service'
				 AND status = 'confirmed'
				 LIMIT 1",
				\GP_Bookings\Database::table_bookings(),
				(int) $entry_id
			)
		);

		return ! empty( $booking_id );
	}

	private function already_sent( $entry_id, $notification_id ): bool {
		return (bool) gform_get_meta( (int) $entry_id, $this->sent_meta_key( $notification_id ) );
	}

	private function sent_meta_key( $notification_id ): string {
		return 'gpb_delayed_completed_sent_' . $notification_id;
	}

}

# Configuration

new GPB_Delay_Completed_Notification(
	array(
		'notification_ids' => array( '6a3f4210238ed' ), // Booking Completed notification ID(s) to delay
		'delay'            => 2, // How long after the booking completes
		'delay_unit'       => 'days', // hours | days | weeks | months
	)
);
