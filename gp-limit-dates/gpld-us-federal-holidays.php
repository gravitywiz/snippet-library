<?php
/**
 * Gravity Perks // Limit Dates // US Federal Holidays
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Add US federal holidays as exceptions to your GP Limit Dates fields. Supports fixed-date holidays (e.g.
 * Independence Day) and floating holidays (e.g. Thanksgiving, Memorial Day). Fixed holidays that fall on a
 * Saturday or Sunday are automatically shifted to the observed weekday (Saturday to Friday, Sunday to Monday).
 *
 * Credit: Clifford (https://github.com/cliffordp)
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of this file with your form and field IDs.
 */
class GPLD_US_Federal_Holidays {

	private $_args     = array();
	private $_holidays = null;

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'           => false,
			'field_ids'         => array(),
			'years_to_generate' => 20,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		$form_id   = $this->_args['form_id'];
		$field_ids = (array) $this->_args['field_ids'];

		if ( ! $form_id ) {
			// Sitewide: apply to all GPLD-enabled Date fields on all forms.
			add_filter( 'gpld_limit_dates_options', array( $this, 'add_holiday_exceptions' ), 10, 3 );
			return;
		}

		if ( empty( $field_ids ) ) {
			// Form-wide: apply to all GPLD-enabled Date fields on the specified form.
			add_filter( "gpld_limit_dates_options_{$form_id}", array( $this, 'add_holiday_exceptions' ), 10, 3 );
			return;
		}

		foreach ( $field_ids as $field_id ) {
			add_filter( "gpld_limit_dates_options_{$form_id}_{$field_id}", array( $this, 'add_holiday_exceptions' ), 10, 3 );
		}

	}

	public function add_holiday_exceptions( $field_options, $form, $field ) {

		if ( $this->_holidays === null ) {
			$this->_holidays = $this->generate_holidays();
		}

		$exceptions                  = isset( $field_options['exceptions'] ) ? $field_options['exceptions'] : array();
		$field_options['exceptions'] = array_values( array_unique( array_merge( $exceptions, $this->_holidays ) ) );

		return $field_options;
	}

	public function generate_holidays() {

		$holidays     = array();
		$current_year = (int) wp_date( 'Y' );

		for ( $i = 0; $i <= $this->_args['years_to_generate']; $i++ ) {
			$year = $current_year + $i;

			// Fixed-date holidays (may fall on a weekend).
			$fixed_dates = array(
				"{$year}-01-01", // New Year's Day
				"{$year}-06-19", // Juneteenth
				"{$year}-07-04", // Independence Day
				"{$year}-11-11", // Veterans Day
				"{$year}-12-25", // Christmas Day
			);

			foreach ( $fixed_dates as $date_string ) {
				$timestamp = strtotime( $date_string );

				$day_of_week = (int) gmdate( 'w', $timestamp );
				if ( $day_of_week === 0 ) {
					// Sunday -> Observe on Monday
					$timestamp = strtotime( '+1 day', $timestamp );
				} elseif ( $day_of_week === 6 ) {
					// Saturday -> Observe on Friday
					$timestamp = strtotime( '-1 day', $timestamp );
				}

				$holidays[] = gmdate( 'm/d/Y', $timestamp );
			}

			// Floating-date holidays (always fall on a weekday).
			$floating_dates = array(
				"third monday of january {$year}",     // Martin Luther King Jr. Day
				"third monday of february {$year}",    // Washington's Birthday / Presidents' Day
				"last monday of may {$year}",          // Memorial Day
				"first monday of september {$year}",   // Labor Day
				"second monday of october {$year}",    // Columbus Day / Indigenous Peoples' Day
				"fourth thursday of november {$year}", // Thanksgiving Day
			);

			foreach ( $floating_dates as $date_string ) {
				$holidays[] = gmdate( 'm/d/Y', strtotime( $date_string ) );
			}
		}

		return $holidays;
	}

}

# Configuration

# Apply to all GPLD-enabled Date fields on all forms.
new GPLD_US_Federal_Holidays( array(
	'years_to_generate' => 20, // Matches the datepicker's default 20-year forward range.
) );

# Apply to all GPLD-enabled Date fields on a specific form.
//new GPLD_US_Federal_Holidays( array(
//	'form_id'           => 123,
//	'years_to_generate' => 20,
// ) );

# Apply to specific GPLD-enabled Date fields on a specific form.
//new GPLD_US_Federal_Holidays( array(
//	'form_id'           => 123,
//	'field_ids'         => array( 4, 5 ),
//	'years_to_generate' => 20,
//) );
