<?php
/**
 * Gravity Perks // Populate Anything // Require Filter Groups to have all values present.
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_object_type_post_filter', function( $query_builder_args, $args ) {
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

	/* Only change behavior for field ID 22 in form ID 217. */
	if ( $field->id !== 22 || $field->formId !== 217 ) {
		return $query_builder_args;
	}

	if ( ! $filter_value ) {
		unset( $query_builder_args['where'][ $filter_group_index ] );
	}

	return $query_builder_args;
}, 11, 2 );
