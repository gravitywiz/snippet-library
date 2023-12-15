<?php
/**
 * Gravity Perks // Populate Anything // Use Post Meta Date Picker Fields for Comparison
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * The snippet will cast dates saved by Advanced Custom Fields or Pods into dates that are comparable using MySQL queries.
 *
 * Instructions:
 *  1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *  2. Replace DATEPICKERMETAKEYNAME with the appropriate ACF/Pods Date Picker Field meta key name.
 *  3. Uncomment/comment the appropriate $date_format variable depending on if you are using ACF or Pods.
 *
 *
 * Plugin Name:  GP Populate Anything — Use Post Meta Date Picker Fields for Comparison
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Cast dates saved by Advanced Custom Fields or Pods into dates that are comparable using MySQL queries.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gppa_object_type_post_filter_meta_DATEPICKERMETAKEYNAME', 'process_filter_post_meta_date_picker', 10, 4 );
function process_filter_post_meta_date_picker( $query_builder_args, $args ) {

	// Uncomment/comment the appropriate lines below depending on if you are using ACF or Pods.
	$date_format = '%%Y%%m%%d'; // ACF
	//$date_format = '%%Y-%%m-%%d'; // Pods

	global $wpdb;

	/**
	 * @var $filter_value
	 * @var $filter
	 * @var $filter_group
	 * @var $filter_group_index
	 * @var $property
	 * @var $property_id
	 */
	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	extract( $args );

	$object_type   = gp_populate_anything()->get_object_type( 'post' );
	$meta_operator = $object_type->get_sql_operator( $filter['operator'] );
	$meta_value    = $object_type->get_sql_value( $filter['operator'], $filter_value );

	if ( ! isset( $object_type->post_date_meta_counter ) ) {
		$object_type->post_date_meta_counter = 0;
	}

	$object_type->post_date_meta_counter ++;
	$as_table = 'mq' . $object_type->post_date_meta_counter;

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$query_builder_args['where'][ $filter_group_index ][] = $wpdb->prepare( "(
				{$as_table}.meta_key = %s
				AND
				STR_TO_DATE({$as_table}.meta_value, )
				{$meta_operator}
				STR_TO_DATE(%s, $date_format)
			)", rgar( $property, 'value' ), $meta_value );

	$query_builder_args['joins'][ $as_table ] = "LEFT JOIN {$wpdb->postmeta} AS {$as_table} ON ( {$wpdb->posts}.ID = {$as_table}.post_id )";

	return $query_builder_args;

}
