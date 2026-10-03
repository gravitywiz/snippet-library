<?php
/**
 * Gravity Perks // Bookings // Bookings Today Merge Tag
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Adds a merge tag that outputs all bookings for a service for the current day.
 *
 * Usage:
 *
 * Example merge tag: {gpb_bookings_today:service_id[123]}
 */
class GPB_Bookings_Today_Merge_Tag {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		if ( class_exists( '\\GP_Bookings\\Queries\\Booking_Query' ) ) {
			add_filter( 'gform_replace_merge_tags', array( $this, 'replace_merge_tag' ), 10, 7 );
		}
	}

	public function replace_merge_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
		preg_match_all( '/\{gpb_bookings_today:service_id\[(\d+)\]\}/i', $text, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$text = str_replace( $match[0], $this->get_bookings_output( (int) $match[1], $format ), $text );
		}

		return $text;
	}

	private function get_bookings_output( $service_id, $format ) {
		$today    = \GP_Bookings\Utils\DateTimeUtils::today();
		$bookings = \GP_Bookings\Queries\Booking_Query::get_bookings( array(
			'object_type' => 'service',
			'service_id'  => $service_id,
			'start_date'  => $today->format( 'Y-m-d H:i:s' ),
			'end_date'    => $today->addDay()->format( 'Y-m-d H:i:s' ),
			'order'       => 'ASC',
			'orderby'     => 'start',
		) );

		$lines = array();
		foreach ( $bookings as $booking ) {
			if ( $booking instanceof \GP_Bookings\Booking && $booking->get_mode() === 'datetime-range' ) {
				$line = $this->format_booking_line( $booking );
				if ( $line ) {
					$lines[] = $line;
				}
			}
		}

		if ( empty( $lines ) ) {
			return '';
		}

		// Title
		$title = sprintf( "Today's Bookings — %s", $today->format( 'F j, Y' ) );

		return $format === 'html'
			? '<strong>' . esc_html( $title ) . '</strong><ul><li>' . implode( '</li><li>', array_map( 'esc_html', $lines ) ) . '</li></ul>'
			: $title . "\n- " . implode( "\n- ", $lines );
	}

	private function format_booking_line( $booking ) {
		$start = \GP_Bookings\Utils\DateTimeUtils::parse( $booking->get_start_datetime() );
		$end   = \GP_Bookings\Utils\DateTimeUtils::parse( $booking->get_end_datetime() );

		if ( ! $start || ! $end ) {
			return '';
		}

		// Time range
		$line = $start->isSameDay( $end )
			? sprintf( '%s - %s', $start->format( 'g:i A' ), $end->format( 'g:i A' ) )
			: sprintf( '%s %s - %s %s', $start->format( 'M j, Y' ), $start->format( 'g:i A' ), $end->format( 'M j, Y' ), $end->format( 'g:i A' ) );

		// Customer name
		$customer_name = $booking->get_customer_name();
		if ( $customer_name && $customer_name !== __( 'N/A', 'gp-bookings' ) ) {
			$line .= ' — ' . $customer_name;
		}

		// Resource name
		$resource_names = array();
		foreach ( $booking->get_resource_bookings() as $resource_booking ) {
			if ( $resource_booking instanceof \GP_Bookings\Booking && $resource_booking->get_bookable() ) {
				$resource_names[] = $resource_booking->get_bookable()->get_name();
			}
		}
		if ( $resource_names ) {
			$line .= ' (' . implode( ', ', $resource_names ) . ')';
		}

		return $line;
	}
}

new GPB_Bookings_Today_Merge_Tag();
