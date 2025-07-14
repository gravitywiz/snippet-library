<?php
/**
 * Gravity Perks // Randomizer // Randomize Fields to Its Own Page
 * https://gravitywiz.com/documentation/gravity-forms-randomizer/
 *
 * Use this snippet to randomize fields within their respective pages.
 *
 * See video: https://www.loom.com/share/2dd4f3c6995349ae91d4b88f3990294b
 */
add_filter( 'gpr_filtered_fields', function( $filtered_fields, $form, $display_count ) {

	if ( ! $display_count ) {
		$filtered_fields = randomize_fields_within_pages( $form['fields'] );
	} else {
		$filtered_fields = arrange_fields_by_page( $form['fields'], $filtered_fields );
	}
	return $filtered_fields;
}, 10, 3 );

function arrange_fields_by_page( $fields_backup, $filtered_fields ) {
	$filtered_map = array();
	foreach ( $filtered_fields as $field ) {
		$filtered_map[ $field->id ] = $field;
	}

	$result   = array();
	$used_ids = array();

	foreach ( $fields_backup as $field ) {
		if ( $field->type === 'page' ) {
			$result[] = $field;
		} elseif ( isset( $filtered_map[ $field->id ] ) ) {
			$result[] = $filtered_map[ $field->id ];

			$used_ids[] = $field->id;
		}
	}

	foreach ( $filtered_fields as $field ) {
		if ( ! in_array( $field->id, $used_ids, true ) && $field->type !== 'page' ) {
			$result[] = $field;
		}
	}

	// Clean up redundant pages
	$cleaned_result = array();
	$prev_is_page   = false;

	foreach ( $result as $field ) {
		if ( $field->type === 'page' ) {
			if ( $prev_is_page ) {
				continue; // Skip consecutive page
			}
			$prev_is_page = true;
		} else {
			$prev_is_page = false;
		}
		$cleaned_result[] = $field;
	}

	// Remove leading page
	if ( isset( $cleaned_result[0] ) && $cleaned_result[0]->type === 'page' ) {
		array_shift( $cleaned_result );
	}

	// Remove trailing page
	if ( ! empty( $cleaned_result ) && end( $cleaned_result )->type === 'page' ) {
		array_pop( $cleaned_result );
	}

	return $cleaned_result;
}

function randomize_fields_within_pages( $fields ) {
	$result        = array();
	$current_group = array();

	foreach ( $fields as $field ) {
		if ( $field->type === 'page' ) {
			// Shuffle current group and add to result.
			shuffle( $current_group );
			$result        = array_merge( $result, $current_group );
			$result[]      = $field; // Add the page marker.
			$current_group = array(); // Start new group.
		} else {
			$current_group[] = $field;
		}
	}

	// Add any remaining fields in the last group.
	if ( ! empty( $current_group ) ) {
		shuffle( $current_group );
		$result = array_merge( $result, $current_group );
	}

	return $result;
}
