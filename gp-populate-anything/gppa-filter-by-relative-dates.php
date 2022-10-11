<?php
/**
 * Gravity Perks // Populate Anything // Filter Results on Relative Dates
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/#how-can-i-filter-posts-those-published-in-the-last-month
 *
 * It adds merge tags that can be used as Custom Values in this format: {INTEGER UNIT ago}, to filter the results on relative dates.
 *
 * -------
 * Instructions:
 * -------
 * 1. Replace 'post' with whatever object type you wish to add these merge tags to.
 *
 *    Note, newer versions of GP Populate Anything support 'gppa_replace_filter_value_variables' which works for all
 *    object types.
 *
 * 2. Change the filter value to "Add Custom Value" and insert a merge tag that follows
 *    the following format: {INTEGER UNIT ago}
 *
 *     Examples:
 *         {3 days ago}
 *         {2 hours ago}
 *         {1 month ago}
 *         {3 weeks ago}
 *         {1 year ago}
 *
 * Another possible way of passing the value is : {relative:valid_strtotime_value}
 *
 * Plugin Name: GP Populate Anything - Filter Results on Relative Dates
 * Plugin URI:  https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description: Filter results on relative dates with custom merge tags
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: http://gravitywiz.com
 **/
// {INTEGER UNIT ago}
add_filter( 'gppa_replace_filter_value_variables_post', 'gppa_replace_int_unit_ago_merge_tags' );
add_filter( 'gppa_replace_filter_value_variables_gf_entry', 'gppa_replace_int_unit_ago_merge_tags' );
add_filter( 'gppa_replace_filter_value_variables_user', 'gppa_replace_int_unit_ago_merge_tags' );
add_filter( 'gppa_replace_filter_value_variables_term', 'gppa_replace_int_unit_ago_merge_tags' );
add_filter( 'gppa_replace_filter_value_variables_database', 'gppa_replace_int_unit_ago_merge_tags' );

function gppa_replace_int_unit_ago_merge_tags( $value ) {
	preg_match_all( '/{(\d+) ((week|day|month|year)s?) ago}/m', $value, $matches, PREG_SET_ORDER, 0 );

	if ( ! $matches || ! count( $matches ) ) {
		$relative_time = explode( ':', substr( $value, 1, -1 ) );
		if ( ! strtotime( $relative_time[1] ) ) {
			return $value;
		}
	}

	foreach ( $matches as $match ) {
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$value = str_replace( $match[0], date( 'Y-m-d H:i:s', strtotime( '-' . $match[1] . ' ' . $match[2] ) ), $value );
	}

	return $value;
}

// {relative:valid_strtotime_value}
add_filter( 'gppa_replace_filter_value_variables_post', 'gppa_replace_relative_strtotime_merge_tags' );
add_filter( 'gppa_replace_filter_value_variables_gf_entry', 'gppa_replace_relative_strtotime_merge_tags' );
add_filter( 'gppa_replace_filter_value_variables_user', 'gppa_replace_relative_strtotime_merge_tags' );
add_filter( 'gppa_replace_filter_value_variables_term', 'gppa_replace_relative_strtotime_merge_tags' );
add_filter( 'gppa_replace_filter_value_variables_database', 'gppa_replace_relative_strtotime_merge_tags' );

function gppa_replace_relative_strtotime_merge_tags( $value ) {
	preg_match_all( '/{relative:(.*?)}/m', $value, $matches, PREG_SET_ORDER, 0 );

	if ( ! $matches || ! count( $matches ) ) {
		return $value;
	}

	foreach ( $matches as $match ) {
		if ( strtotime( $match[1] ) ) {
			$value = str_replace( $match[0], wp_date( 'Y-m-d H:i:s', strtotime( $match[1] ) ), $value );
		}
	}

	return $value;
}
