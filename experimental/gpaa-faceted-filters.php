<?php
/**
 * Gravity Perks // Populate Anything // Faceted Filters
 *
 * Do not require a value for filters which allows building faceted/progressive filters.
 *
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instructions:
 *   1. Add to site (https://gravitywiz.com/documentation/how-do-i-install-a-snippet/)
 *   2. Update field ID and form ID accordingly. In this snippet they are set to 5 and 2 respectively.
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

	/* Only change behavior for field ID 5 in form ID 2. */
	if ( $field->id !== 5 || $field->formId !== 2 ) {
		return $query_builder_args;
	}

	if ( ! $filter_value ) {
		array_pop( $query_builder_args['where'][ $filter_group_index ] );
	}

	return $query_builder_args;
}, 10, 2 );

// Disable requiring field filter values to not be empty for form ID 2
add_filter( 'gppa_has_empty_field_filter_value_2', '__return_false' );
