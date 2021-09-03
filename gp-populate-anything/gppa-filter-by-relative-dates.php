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
 *		     {1 month ago}
 *         {3 weeks ago}
 *         {1 year ago}
 *
 * Plugin Name: GP Populate Anything - Filter Results on Relative Dates
 * Plugin URI:  https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description: Filter results on relative dates with custom merge tags
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: http://gravitywiz.com
 **/
add_filter( 'gppa_replace_filter_value_variables_post', function ( $value ) {
	preg_match_all( '/{(\d+) ((week|day|month|year)s?) ago}/m', $value, $matches, PREG_SET_ORDER, 0 );

	if ( ! $matches || ! count( $matches ) ) {
		return $value;
	}

	foreach ( $matches as $match ) {
		$value = str_replace( $match[0], date( 'Y-m-d H:i:s', strtotime( '-' . $match[1] . ' ' . $match[2] ) ), $value );
	}

	return $value;
} );
