<?php
/**
 * Gravity Perks // Populate Anything // ACF Repeater Wildcard Filter
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Filter posts/users/terms by ACF repeater subfield values across all rows.
 *
 * When filtering by an ACF repeater subfield, only the specific row you selected is checked
 * (e.g., row 0). This snippet converts that to check all rows of the repeater.
 *
 * Instructions
 *
 * 1. Enable "Populate choices dynamically" on any choice-based field.
 * 2. Select the "Post", "User" or "Term" object type.
 * 3. Add a filter using a repeater subfield property.
 *    For example, if your repeater is labeled "locations" and you want to filter by the "zip_code" subfield,
 *    select "locations_0_zip_code" for the filter property.
 * 4. Add "gppa-acf-repeater-wildcard" to the field's CSS Class Name setting.
 */
add_filter( 'gppa_object_type_filter_after_processing', function( $query, $args ) {

	$field       = rgar( $args, 'field' );
	$property_id = rgar( $args, 'property_id' );

	if ( ! $field || strpos( $field->cssClass, 'gppa-acf-repeater-wildcard' ) === false ) {
		return $query;
	}

	// Match ACF repeater pattern: meta_repeatername_0_subfieldname
	if ( ! preg_match( '/^meta_(.+)_([0-9]+)_(.+)$/', $property_id, $matches ) ) {
		return $query;
	}

	$repeater_name  = $matches[1];
	$row_index      = $matches[2];
	$subfield_name  = $matches[3];
	$original_key   = $repeater_name . '_' . $row_index . '_' . $subfield_name;
	$regexp_pattern = $repeater_name . '_[0-9]+_' . $subfield_name;

	foreach ( $query['where'] as $group_index => $group ) {
		foreach ( $group as $clause_index => $clause ) {
			$query['where'][ $group_index ][ $clause_index ] = str_replace(
				"meta_key = '" . $original_key . "'",
				"meta_key REGEXP '" . $regexp_pattern . "'",
				$clause
			);
		}
	}

	return $query;
}, 10, 2 );
