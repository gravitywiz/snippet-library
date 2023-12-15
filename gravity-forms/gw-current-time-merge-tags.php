<?php
/**
 * Gravity Wiz //  Gravity Forms // Current Time Merge Tags
 * https://gravitywiz.com/
 *
 * Adds {time_hh}, {time_hh:12}, {time_hh:24}, {time_mm}, and {time_am_pm} merge tags,
 * which can be used to set the default value of a Time field to the Current Time..
 *
 * Usage:
 *
 * {time_hh}    12-hour format of an hour with leading zeros
 * {time_hh:12} 12-hour format of an hour with leading zeros
 * {time_hh:24} 24-hour format of an hour with leading zeros
 * {time_mm}    Minutes with leading zeros
 * {time_am_pm} Uppercase Ante meridiem and Post meridiem
 *
 * Plugin Name:  Gravity Forms - Current Time Merge Tags
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Set the default value of a Time field to the Current Time.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_replace_merge_tags', 'gwiz_current_time_merge_tags', 10 );

function gwiz_current_time_merge_tags( $text ) {

	$local_timestamp = GFCommon::get_local_timestamp( time() );

	// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
	$time_hh    = date( 'h', $local_timestamp );
	$time_hh_24 = date( 'H', $local_timestamp );
	$time_mm    = date( 'i', $local_timestamp );
	$time_am_pm = date( 'A', $local_timestamp );

	$text = str_replace( '{time_hh}', $time_hh, $text );
	$text = str_replace( '{time_hh:12}', $time_hh, $text );
	$text = str_replace( '{time_hh:24}', $time_hh_24, $text );
	$text = str_replace( '{time_mm}', $time_mm, $text );
	$text = str_replace( '{time_am_pm}', $time_am_pm, $text );

	return $text;

}
