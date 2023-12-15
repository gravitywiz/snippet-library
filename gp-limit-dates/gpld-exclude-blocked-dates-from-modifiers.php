<?php
/**
 * Gravity Perks // GP Limit Dates // Exclude Blocked Dates from Modifiers
 *
 * Automatically excludes blocked dates from a Date Modifier in GP Limit Dates.
 *
 * @version 0.1
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    https://gravitywiz.com
 *
 * Plugin Name: GP Limit Dates — Exclude Blocked Dates from Modifiers
 * Plugin URI: http://gravitywiz.com/documentation/gravity-forms-limit-dates/
 * Description: Automatically excludes blocked dates from a Date Modifier in GP Limit Dates.
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: http://gravitywiz.com
 *
 */
add_filter( 'gpld_date_value', 'gpld_extend_modifiers_by_blocked_dates', 10, 4 );
function gpld_extend_modifiers_by_blocked_dates( $end_date, $field, $key, $options ) {

	if ( ! $end_date ) {
		return $end_date;
	}

	$value       = rgar( $options, $key );
	$is_field_id = is_numeric( $value ) && $value > 0;

	if ( $value == '{today}' ) {
        // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
		$date = strtotime( 'midnight', current_time( 'timestamp' ) );
		//$date = strtotime( '05/21/2016 midnight' );
	} elseif ( $is_field_id ) {

		$mod_field_id = $value;
		$form         = GFAPI::get_form( $field->formId );
		$mod_field    = GFFormsModel::get_field( $form, $mod_field_id );

		$date = gp_limit_dates()->parse_timestamp( rgpost( sprintf( 'input_%s', $mod_field->id ) ), $mod_field );

	} else {
		$date = strtotime( $value );
	}

	$blocked_count = 0;

	// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
	$begin    = new DateTime( date( 'Y-m-d', $date ) );
	$end      = new DateTime( date( 'Y-m-d', $end_date ) );
	$interval = DateInterval::createFromDateString( '1 day' );

	// period should not include the starting day, should always inlude the very last day
	$period = new DatePeriod( $begin, $interval, $end->add( $interval ) );

	foreach ( $period as $_date ) {
		if ( ! gpld_is_valid_date( $_date->getTimestamp(), $field ) ) {
			$blocked_count++;
		}
	}

	if ( ! $blocked_count ) {
		return $end_date;
	}

	$end = new DateTime( date( 'Y-m-d', $end_date ) );

	// extend endDate to account for blocked dates
	while ( $blocked_count > 0 ) {
		$end->add( DateInterval::createFromDateString( '1 day' ) );
		// only deduct valid dates from our counter
		if ( gpld_is_valid_date( $end->getTimestamp(), $field ) ) {
			$blocked_count--;
		}
	}

	// set end date to first unblocked date
	while ( ! gpld_is_valid_date( $end->getTimestamp(), $field ) ) {
		$end->add( $interval );
	}

	return $end->getTimestamp();
}

function gpld_is_valid_date( $date, $field ) {

	$date = gp_limit_dates()->parse_timestamp( $date, $field );

	$is_valid_day = gp_limit_dates()->is_valid_day_of_week( $date, $field );
	$is_valid     = $is_valid_day;
	$is_excepted  = gp_limit_dates()->is_excepted( $date, $field );

	if ( $is_excepted ) {
		$is_valid = ! $is_valid;
	}

	return $is_valid;
}

add_filter( 'wp_footer', 'gpld_exclude_blocked_dates_js' );
add_filter( 'gform_preview_footer', 'gpld_exclude_blocked_dates_js' );
function gpld_exclude_blocked_dates_js() {
	?>

	<script type="text/javascript">

		if( window.gform ) {

			gform.addFilter( 'gpld_modified_date', function( modifiedDate, modifier, date, data, fieldId ) {
				return gpldGetModifiedDateWithBlockedDates( date, modifiedDate, data, fieldId );
			} );

			function gpldGetModifiedDateWithBlockedDates( startDate, endDate, data, fieldId ) {

				var date         = new Date( startDate ),
					endDate      = new Date( endDate ),
					blockedCount = 0;

				// find all the blocked dates between the start and end dates
				for ( date; date <= endDate; date.setDate( date.getDate() + 1 ) ) {
					if( ! GPLimitDates.isDateShown( date, data, fieldId )[0] ) {
						blockedCount++;
					}
				}

				if( ! blockedCount ) {
					return endDate;
				}

				// extend endDate to account for blocked dates
				while( blockedCount > 0 ) {
					endDate.setDate( endDate.getDate() + 1 );
					// only deduct valid dates from our counter
					if( GPLimitDates.isDateShown( endDate, data, fieldId )[0] ) {
						blockedCount--;
					}
				}

				// set end date to first unblocked date
				while( ! GPLimitDates.isDateShown( endDate, data, fieldId )[0] ) {
					endDate.setDate( endDate.getDate() + 1 );
				}

				return endDate;
			}

		}

	</script>

	<?php
}
