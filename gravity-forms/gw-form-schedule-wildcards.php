<?php
/**
 * Gravity Wiz // Gravity Forms // Form Schedule Wildcards
 *
 * Support using wildcard months, dates, or years in your form schedules.
 * 
 * Plugin Name:  Gravity Forms Form Schedule Wildcards
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Use wildcards for the month, date, or year in your form schedule start and end dates.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   http://gravitywiz.com/
 *
 * Instructions:
 *
 * 1. Install and activate.
 * 2. Set your form schedule via Form Settings › Restrictions › Schedule form.
 * 3. Use wildcards!
 *    a. Replace the month or date with "99" to dynamically set the current month or date.
 *    b. Replace the year with "9999" to dynamically set the current year.
 */ 
add_filter( 'gform_pre_render', 'gw_form_schedule_wildcards' );
add_filter( 'gform_pre_validation', 'gw_form_schedule_wildcards' );
function gw_form_schedule_wildcards( $form ) {

	if ( rgar( $form, 'scheduleForm' ) && strstr( $form['scheduleStart'], '/' ) && strstr( $form['scheduleEnd'], '/' ) ) {
		$form['scheduleStart'] = gw_convert_date_to_current_month_year( $form['scheduleStart'] );
		$form['scheduleEnd']   = gw_convert_date_to_current_month_year( $form['scheduleEnd'] );
	}

	return $form;
}

function gw_convert_date_to_current_month_year( $date_string ) {

	list( $month, $date, $year ) = explode( '/', $date_string );

	if ( $month == '99' ) {
		$month = gmdate( 'm' );
	}

	if ( $date == '99' ) {
		$date = gmdate( 'd' );
	}

	if ( $year == '9999' ) {
		$year = gmdate( 'Y' );
	}

	$converted_date = implode( '/', array( $month, $date, $year ) );

	return $converted_date;
}
