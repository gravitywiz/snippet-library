<?php
/**
 * Gravity Perks // Bookings // Fixed Check-in and Check-out Days
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Only allow nightly stays between set days of the week, such as Monday to Friday or Friday to Monday.
 * The calendar only offers those check-in and check-out days.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Enable "Treat as Nights" and "Flexible Duration" on the service. The snippet sets the service's
 *    minimum and maximum number of nights from the stays you configure.
 *
 * 3. Update the configuration at the bottom of the snippet.
 */
class GPB_Fixed_Check_In_Check_Out_Days {

	private $service_ids;
	private $stays;
	private $nights = array();

	public function __construct( $args ) {
		$this->service_ids = $args['service_ids'];
		$this->stays       = $args['stays'];

		foreach ( $this->stays as $check_in => $check_out ) {
			$date           = new DateTime( $check_in );
			$this->nights[] = $date->diff( ( clone $date )->modify( 'next ' . $check_out ) )->days;
		}

		add_filter( 'gpb_calendar_date_endpoints', array( $this, 'filter_endpoints' ), 10, 3 );
		add_filter( 'gpb_min_duration', array( $this, 'filter_min_duration' ), 10, 2 );
		add_filter( 'gpb_max_duration', array( $this, 'filter_max_duration' ), 10, 2 );
		add_filter( 'gpb_validate_booking_duration', array( $this, 'validate_stay' ), 10, 4 );
	}

	// Only offer the check-in and check-out days in the calendar.
	public function filter_endpoints( $endpoints, $date, $service ) {
		if ( $this->applies( $service ) ) {
			$day                = strtolower( $date->format( 'l' ) );
			$endpoints['start'] = isset( $this->stays[ $day ] );
			$endpoints['end']   = in_array( $day, $this->stays );
		}

		return $endpoints;
	}

	// Limit stays to the shortest and longest allowed number of nights (in minutes), so a stay cannot run past its check-out day.
	public function filter_min_duration( $minutes, $service ) {
		return $this->applies( $service ) ? min( $this->nights ) * 1440 : $minutes;
	}

	public function filter_max_duration( $minutes, $service ) {
		return $this->applies( $service ) ? max( $this->nights ) * 1440 : $minutes;
	}

	// Reject any other stay. A nightly booking ends at the start of its check-out day.
	public function validate_stay( $result, $start, $end, $service ) {
		if ( ! $result['is_valid'] || ! $this->applies( $service ) ) {
			return $result;
		}

		$check_in = strtolower( $start->format( 'l' ) );
		if ( isset( $this->stays[ $check_in ] ) && $start->modify( 'next ' . $this->stays[ $check_in ] )->format( 'Y-m-d' ) === $end->format( 'Y-m-d' ) ) {
			return $result;
		}

		return array(
			'is_valid' => false,
			'message'  => 'Please choose one of the available check-in and check-out days.',
		);
	}

	private function applies( $service ) {
		return in_array( $service->get_id(), $this->service_ids );
	}

}

# Configuration
new GPB_Fixed_Check_In_Check_Out_Days( array(
	'service_ids' => array( 123 ), // Enter one or more service IDs
	'stays'       => array(
		'monday' => 'friday', // Check in on Monday, check out on Friday
		'friday' => 'monday', // Check in on Friday, check out on Monday
	),
) );
