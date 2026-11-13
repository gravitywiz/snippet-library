<?php
/**
 * Gravity Perks // Bookings // Attach ICS File to Notifications
 *
 * Attach an ICS file to specified notifications that includes all bookings from the entry.
 *
 * Note, this 
 *
 * Instructions:
 * 1. Install this snippet: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 * 2. Set 'form_id' and `notification` in the configuration at the bottom. Find the notification ID in the "nid" parameter when editing the notification. A notification ID array is also supported.
 */
class GPB_Notification_ICS {
	private $form_id;
	private $notification_ids;
	private $include_pending;
	private $files = [];

	public function __construct( array $args ) {
		$args = wp_parse_args( $args, [
			'form_id'         => 0,
			'notification'    => [],
			'include_pending' => false,
		] );
		$this->form_id          = (int) $args['form_id'];
		$this->notification_ids = (array) $args['notification'];
		$this->include_pending  = (bool) $args['include_pending'];
		add_filter( 'gform_pre_send_email', [ $this, 'attach' ], 20, 4 );
		add_action( 'gform_after_email', [ $this, 'after_email' ], 20, 6 );
		add_action( 'shutdown', [ $this, 'cleanup' ] );
	}

	public function attach( $email, $format, $notification, $entry ) {
		if ( ! empty( $email['abort_email'] )
			|| (int) rgar( $entry, 'form_id' ) !== $this->form_id
			|| ! in_array( rgar( $notification, 'id' ), $this->notification_ids, true )
			|| empty( $entry['id'] )
			|| ! class_exists( '\GP_Bookings\Queries\Booking_Query' )
			|| ! class_exists( '\Sabre\VObject\Component\VCalendar' ) ) {
			return $email;
		}

		try {
			$bookings = array_values( array_filter(
				\GP_Bookings\Queries\Booking_Query::get_entry_bookings( (int) $entry['id'] ),
				static function ( $booking ) {
					return $booking->get_type() === 'service';
				}
			) );

			// No saved booking is a normal case (for example, an early notification).
			if ( empty( $bookings ) ) {
				return $email;
			}

			$utc      = new \DateTimeZone( 'UTC' );
			$calendar = new \Sabre\VObject\Component\VCalendar( [
				'VERSION'  => '2.0',
				'PRODID'   => '-//Gravity Wiz//GPB Notification ICS//EN',
				'CALSCALE' => 'GREGORIAN',
			] );
			foreach ( $bookings as $booking ) {
				try {
					$status  = $booking->get_status();
					$allowed = $status === 'confirmed' || ( $this->include_pending && $status === 'pending' );
					$service = $booking->get_bookable();
					if ( ! $allowed || ! $service ) {
						continue;
					}

					// Build separately so a failed booking cannot leave a partial event.
					$event = $calendar->createComponent( 'VEVENT', [
						'UID'         => 'gpb-notification-' . hash( 'sha256', home_url( '/' ) . '|' . $booking->get_id() ),
						'DTSTAMP'     => new \DateTimeImmutable( 'now', $utc ),
						'SUMMARY'     => wp_strip_all_tags( $service->get_name() ),
						'DESCRIPTION' => 'Your booking: ' . wp_strip_all_tags( $service->get_name() ),
						'STATUS'      => $status === 'pending' ? 'TENTATIVE' : 'CONFIRMED',
					] );

					$mode = $booking->get_mode();
					if ( $mode === 'datetime-range' ) {
						$start = $this->parse_datetime( $booking->get_start_datetime_utc(), $utc );
						$end   = $this->parse_datetime( $booking->get_end_datetime_utc(), $utc );
						$event->add( 'DTSTART', $start );
						$event->add( 'DTEND', $end );
					} elseif ( in_array( $mode, [ 'nightly', 'date-range-end-inclusive' ], true ) ) {
						// Keep date-only values as local calendar dates, without a UTC shift.
						$start = $this->parse_datetime( $booking->get_start_datetime(), $utc )->setTime( 0, 0 );
						$end   = $this->parse_datetime( $booking->get_end_datetime(), $utc )->setTime( 0, 0 );
						// ICS end dates are exclusive; GPB's day-range end is inclusive.
						if ( $mode === 'date-range-end-inclusive' ) {
							$end = $end->modify( '+1 day' );
						}
						$event->add( 'DTSTART', $start->format( 'Ymd' ), [ 'VALUE' => 'DATE' ] );
						$event->add( 'DTEND', $end->format( 'Ymd' ), [ 'VALUE' => 'DATE' ] );
					} else {
						continue;
					}

					if ( $end <= $start ) {
						throw new \RuntimeException( 'Invalid booking date range.' );
					}
					$calendar->add( $event );
				} catch ( \Throwable $exception ) {
					// Other valid bookings can still be attached. Do not log customer data.
					\GFCommon::log_error( __METHOD__ . ': Skipped an invalid booking in the ICS attachment.' );
				}
			}

			if ( ! count( $calendar->select( 'VEVENT' ) ) ) {
				return $email;
			}

			// A random, private directory outside WordPress uploads; no public feed URL.
			$directory = trailingslashit( sys_get_temp_dir() ) . 'gpb-ics-' . bin2hex( random_bytes( 16 ) );
			if ( ! mkdir( $directory, 0700 ) ) {
				throw new \RuntimeException( 'Could not create temporary directory.' );
			}
			$file = $directory . '/booking.ics';
			$this->files[ $file ] = $directory;
			$content = $calendar->serialize();
			if ( file_put_contents( $file, $content ) !== strlen( $content ) || ! chmod( $file, 0600 ) ) {
				throw new \RuntimeException( 'Could not write temporary calendar file.' );
			}

			// Keep all attachments supplied by Gravity Forms or other snippets.
			$attachments          = $email['attachments'] ?? [];
			$email['attachments'] = array_merge( $attachments ? (array) $attachments : [], [ $file ] );
		} catch ( \Throwable $exception ) {
			// Keep the notification working if an attachment cannot be generated.
			// Do not log calendar contents or customer data.
			\GFCommon::log_error( __METHOD__ . ': Could not generate the booking ICS attachment.' );
		}

		return $email;
	}

	private function parse_datetime( $value, $timezone ) {
		// Empty/malformed values must not silently become "now" or a normalized date.
		$date = \DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', (string) $value, $timezone );
		if ( ! $date || $date->format( 'Y-m-d H:i:s' ) !== $value ) {
			throw new \RuntimeException( 'Invalid booking datetime.' );
		}
		return $date;
	}

	public function after_email( $success, $to, $subject, $message, $headers, $attachments ) {
		foreach ( (array) $attachments as $file ) {
			$this->remove_file( $file );
		}
	}

	private function remove_file( $file ) {
		if ( ! isset( $this->files[ $file ] ) ) {
			return;
		}
		if ( is_file( $file ) ) {
			unlink( $file );
		}
		if ( is_dir( $this->files[ $file ] ) ) {
			rmdir( $this->files[ $file ] );
		}
		unset( $this->files[ $file ] );
	}

	public function cleanup() {
		foreach ( array_keys( $this->files ) as $file ) {
			$this->remove_file( $file );
		}
	}
}

# Configuration

new GPB_Notification_ICS( array(
	'form_id'         => 123,
	'notification'    => 'abc123def456',
	//'include_pending' => true, // Uncomment to attach pending bookings. Only confirmed are attached by default.
) );
