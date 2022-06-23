<?php
/**
 * Gravity Perks // Populate Anything // Parse ACF Date Picker Fields for Comparison
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * The snippet will cast dates saved by Advanced Custom Fields into dates that are comparable using MySQL queries.
 *
 * Instruction
 *
 * Replace DATEPICKERMETAKEYNAME with the appropriate ACF Date Picker Field meta key name.
 *
 * Plugin Name:  GP Populate Anything — Parse ACF Date Picker Fields for Comparison
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Cast dates saved by Advanced Custom Fields into dates that are comparable using MySQL queries.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gppa_object_type_post_filter_meta_DATEPICKERMETAKEYNAME', 'process_filter_acf_date_picker', 10, 4 );
function process_filter_acf_date_picker( $query_builder_args, $args ) {

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

	if ( ! isset( $object_type->acf_meta_query_counter ) ) {
		$object_type->acf_meta_query_counter = 0;
	}

	$object_type->acf_meta_query_counter ++;
	$as_table = 'mq' . $object_type->acf_meta_query_counter;

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$query_builder_args['where'][ $filter_group_index ][] = $wpdb->prepare( "(
				{$as_table}.meta_key = %s
				AND
				STR_TO_DATE({$as_table}.meta_value, '%%Y%%m%%d')
				{$meta_operator}
				STR_TO_DATE(%s, '%%m/%%d/%%Y')
			)", rgar( $property, 'value' ), $meta_value );

	$query_builder_args['joins'][ $as_table ] = "LEFT JOIN {$wpdb->postmeta} AS {$as_table} ON ( {$wpdb->posts}.ID = {$as_table}.post_id )";

	return $query_builder_args;

}
