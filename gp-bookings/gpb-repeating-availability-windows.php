<?php
/**
 * Gravity Perks // Bookings // Repeating Availability Windows
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Repeat selected availability windows on a whole-week cycle, such as one week on and
 * one week off, without creating a separate window for each working week.
 * Weekdays, hours, and date ranges remain controlled by the availability window in GP Bookings.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Add an availability window to your resource or service and give it a unique Description.
 *    Set the working days, hours, and optional date range in GP Bookings.
 *
 * 3. Update the configuration at the bottom of the snippet:
 *    - Set bookable_ids to the resource and/or service IDs.
 *    - Set window_descriptions to the exact Description of each window to repeat.
 *    - Set anchor to the Monday of the first working week in YYYY-MM-DD format.
 *    - Set repeat_weeks to the number of weeks in each cycle.
 *    - Set active_weeks to the number of consecutive working weeks at the start of each cycle (defaults to 1). For alternating weeks, use repeat_weeks = 2 and active_weeks = 1.
 *
 * 4. Add another configuration instance for each different schedule. Configure each window
 *    in only one instance.
 *
 * Note: The anchor determines the cycle, not its start date. Use the window's date range
 * to limit when the schedule applies. Associated services and resources must also permit
 * the desired days and hours.
 */
class GPB_Repeating_Availability {
	private array $bookable_ids;
	private array $window_descriptions;
	private DateTimeImmutable $anchor;
	private int $cycle_days;
	private int $active_days;

	public function __construct( array $args ) {
		if ( array_key_exists( 'resource_ids', $args ) || array_key_exists( 'service_ids', $args ) ) {
			throw new InvalidArgumentException( 'Use bookable_ids for both resource and service IDs.' );
		}
		if ( array_key_exists( 'repeat', $args ) ) {
			throw new InvalidArgumentException( 'Use repeat_weeks with a positive integer instead of repeat.' );
		}
		$args = array_merge( [
			'bookable_ids'        => [],
			'window_descriptions' => [],
			'repeat_weeks'        => 2,
			'active_weeks'        => 1,
			'anchor'              => '',
		], $args );

		$this->bookable_ids = $this->validate_ids( $args['bookable_ids'] );
		if ( ! $this->bookable_ids ) {
			throw new InvalidArgumentException( 'Specify at least one resource or service ID in bookable_ids.' );
		}

		if ( ! is_array( $args['window_descriptions'] ) || ! $args['window_descriptions'] ) {
			throw new InvalidArgumentException( 'Specify at least one exact window description.' );
		}
		foreach ( $args['window_descriptions'] as $description ) {
			if ( ! is_string( $description ) || trim( $description ) === '' ) {
				throw new InvalidArgumentException( 'Window descriptions must be nonempty strings.' );
			}
		}
		$this->window_descriptions = array_values( $args['window_descriptions'] );

		$weeks = $args['repeat_weeks'];
		if ( ! is_int( $weeks ) || $weeks < 1 || $weeks > intdiv( PHP_INT_MAX, 7 ) ) {
			throw new InvalidArgumentException( 'Repeat weeks must be a positive whole number small enough to convert to days.' );
		}
		if ( ! is_int( $args['active_weeks'] ) || $args['active_weeks'] < 1 || $args['active_weeks'] > $weeks ) {
			throw new InvalidArgumentException( 'Active weeks must be a whole number from 1 to the repeat interval.' );
		}
		$this->cycle_days  = $weeks * 7;
		$this->active_days = $args['active_weeks'] * 7;

		$anchor = is_string( $args['anchor'] )
			? DateTimeImmutable::createFromFormat( '!Y-m-d', $args['anchor'], new DateTimeZone( 'UTC' ) )
			: false;
		if ( ! $anchor || $anchor->format( 'Y-m-d' ) !== $args['anchor'] || $anchor->format( 'N' ) !== '1' ) {
			throw new InvalidArgumentException( 'Anchor must be a valid Monday in YYYY-MM-DD format.' );
		}
		$this->anchor = $anchor;

		// Register only after configuration has been validated in full.
		add_filter( 'gpb_availability_blocks', [ $this, 'filter_blocks' ], 10, 2 );
	}

	private function validate_ids( $ids ): array {
		if ( ! is_array( $ids ) ) {
			throw new InvalidArgumentException( 'Bookable IDs must be an array of positive integers.' );
		}
		foreach ( $ids as $id ) {
			if ( ! is_int( $id ) || $id < 1 ) {
				throw new InvalidArgumentException( 'Bookable IDs must be an array of positive integers.' );
			}
		}
		return array_values( array_unique( $ids ) );
	}

	/**
	 * The anchor sets the cycle's phase, not a minimum booking date.
	 * Use the window's date range in GPB if the schedule has a start/end date.
	 */
	public function is_working_date( string $date ): bool {
		$day    = new DateTimeImmutable( $date, new DateTimeZone( 'UTC' ) );
		$days   = (int) $this->anchor->diff( $day )->format( '%r%a' );
		$offset = $days % $this->cycle_days;
		if ( $offset < 0 ) {
			$offset += $this->cycle_days;
		}
		return $offset < $this->active_days;
	}

	public function filter_blocks( array $blocks, $bookable ): array {
		if ( ! ( $bookable instanceof \GP_Bookings\Resource || $bookable instanceof \GP_Bookings\Service )
			|| ! in_array( (int) $bookable->get_id(), $this->bookable_ids, true ) ) {
			return $blocks;
		}

		foreach ( $blocks as $key => $block ) {
			if ( $block->is_negative() || ! in_array( $block->get_description(), $this->window_descriptions, true ) ) {
				continue;
			}

			$blocks[ $key ] = new class( $block, $bookable, $this ) extends \GP_Bookings\AvailabilityBlock {
				private \GP_Bookings\AvailabilityBlock $original;
				private GPB_Repeating_Availability $schedule;

				public function __construct( $block, $bookable, $schedule ) {
					parent::__construct( [
						'block_id'          => $block->get_id(),
						'object_id'         => $bookable->get_id(),
						'object_type'       => $bookable->get_type(),
						'availability_days' => $block->get_availability_days(),
						'start_date'        => $block->get_start_date(),
						'end_date'          => $block->get_end_date(),
						'start_time'        => $block->get_start_time(),
						'end_time'          => $block->get_end_time(),
						'description'       => $block->get_description(),
						'is_negative'       => false,
						'recurring'         => $block->is_recurring(),
					], $bookable );
					$this->time_behavior = $block->get_time_behavior();
					$this->original      = $block;
					$this->schedule      = $schedule;
				}

				public function is_date_available( $date ) {
					return $this->original->is_date_available( $date ) && $this->schedule->is_working_date( $date );
				}
			};
		}
		return $blocks;
	}
}

// Change these values for your own site.
new GPB_Repeating_Availability( [
	'bookable_ids'        => [ 123 ], // Resource and/or service IDs.
	'window_descriptions' => [ 'Fortnightly rota' ],
	'anchor'              => '2026-11-09', // Monday of a working week; keep fixed.
	'repeat_weeks'        => 2,
] );
