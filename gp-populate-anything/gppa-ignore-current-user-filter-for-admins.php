<?php
/**
 * Gravity Perks // Populate Anything // Ignore Current User Filter for Administrators
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * If you've configured a filter based on the "Current User ID" option and wish to ignore that filter for administrators,
 * this snippet will allow you to do so. Please note that all other filters will still be applied.
 *
 * This is useful if you wish to show user-specific results to non-admins but want to show all results to admins.
 */
add_filter( 'gppa_object_type_filter_after_processing', function ( $query_builder_args, $args ) {
	/**
	 * @var $field
	 * @var $filter_value
	 * @var $filter
	 * @var $filter_group
	 * @var $filter_group_index
	 * @var $property
	 * @var $property_id
	 */
	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	extract( $args );

	if ( $filter['value'] !== 'special_value:current_user:ID' || ! current_user_can( 'administrator' ) ) {
		return $query_builder_args;
	}

	array_pop( $query_builder_args['where'][ $filter_group_index ] );

    if ( empty( $query_builder_args['where'][ $filter_group_index ] )  ) {
		unset( $query_builder_args['where'][ $filter_group_index ] );
	}

	return $query_builder_args;
}, 10, 2 );
