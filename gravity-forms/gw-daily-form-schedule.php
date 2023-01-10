<?php
/**
 * Gravity Wiz // Gravity Forms // Daily Form Schedule
 *
 * Allow your form schedule to apply daily.
 *
 * @version  1.3
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/
 *
 * Plugin Name:  Gravity Forms Daily Form Schedule
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Allow your form schedule to apply daily.
 * Author:       Gravity Wiz
 * Version:      1.3
 * Author URI:   http://gravitywiz.com/
 *
 * Usage:
 *
 * 1. Install and activate.
 * 2. Set your form schedule via Form Settings › Restrictions › Schedule form.
 *  a. Specify your desired daily start and end times via the Start and End Time settings.
 *  b. Leave the Start and End Date inputs EMPTY!
 *
 * Want to make your schedule weekly?
 *
 * 1. Follow the steps above.
 * 2. Specify the day of the week your schedule starts and ends with it's numeric equivalent.
 *  a. Sunday    = 0
 *     Monday    = 1
 *     Tuesday   = 2
 *     Wednesday = 3
 *     Thursday  = 4
 *     Friday    = 5
 *     Saturday  = 6
 *  b. Enter the applicable day of the week number into the Start and End Date inputs respectively.
 *
 * That's it. This super simple plugin will automatically ensure that your schedule will apply daily (or weekly). 🙂
 */
add_filter( 'gform_pre_render', 'gw_daily_form_schedule' );
add_filter( 'gform_pre_validation', 'gw_daily_form_schedule' );
function gw_daily_form_schedule( $form ) {

	// Skip "Gravity Forms Daily Form Schedule" for the forms having set schedule start and schedule end date via Form Settings.
	if ( strstr( $form['scheduleStart'], '/' ) || strstr( $form['scheduleEnd'], '/' ) ) {
		return $form;
	}

	$days = array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' );

	if ( rgar( $form, 'scheduleForm' ) ) {

		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
		$time         = current_time( 'timestamp' );
		$is_interweek = false;

		if ( ! $form['scheduleStart'] || $form['scheduleStart'] <= 6 ) {
			if ( ! rgblank( $form['scheduleStart'] ) && $form['scheduleStart'] <= 6 ) {
				$is_interweek = $form['scheduleStart'] > $form['scheduleEnd'];
				$week_phrase  = (int) $form['scheduleStart'] === 0 || $is_interweek ? 'last week' : 'this week';
				// Sunday last week, Monday this week.
				$time = strtotime( "{$days[ $form['scheduleStart'] ]} {$week_phrase}", $time );
			}

			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$form['scheduleStart'] = date( 'm/d/Y', $time );
		}

		if ( ! $form['scheduleEnd'] || $form['scheduleEnd'] <= 6 ) {
			if ( ! rgblank( $form['scheduleEnd'] ) && $form['scheduleEnd'] <= 6 ) {
				$week_phrase = $is_interweek ? 'next week' : 'this week';
				$time        = strtotime( "{$days[ $form['scheduleEnd'] ]} {$week_phrase}", $time );
			}

			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$form['scheduleEnd'] = date( 'm/d/Y', $time );
		}
	}

	return $form;
}
