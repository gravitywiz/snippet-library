<?php
/**
 * Gravity Perks // Entry Blocks // Filter Search All by Label
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Use this snippet to allow searching by choice label in addition to the stored value when searching
 * by a field with choices (e.g. Drop Down, Checkboxes, etc.).
 *
 * Instruction Video: https://www.loom.com/share/87c93d2a9e7240a1b647ef4a4919a054
 */
add_filter( 'gpeb_user_filter_condition', function ( $condition, $filter_key, $filter_value, $gf_queryer ) {
	$form = GFAPI::get_form( $gf_queryer->form_id );
	if ( ! $form ) {
		return $condition;
	}

	if ( 'all' === $filter_key ) {
		$fields = $form['fields'];
	} else {
		return $condition; // Skip input IDs, date_created, created_by, etc.
	}

	// Collect the stored value of EVERY choice whose label partially matches the search term.
	$matched_values = array();
	foreach ( $fields as $field ) {
		if ( empty( $field->choices ) ) {
			continue;
		}
		foreach ( $field->choices as $choice ) {
			if ( stripos( (string) rgar( $choice, 'text' ), (string) $filter_value ) !== false ) {
				$matched_values[] = rgar( $choice, 'value' );
			}
		}
	}

	$matched_values = array_unique( array_filter( $matched_values, 'strlen' ) );

	if ( empty( $matched_values ) ) {
		return $condition; // No label match — leave the original condition alone.
	}

	// Keep the original raw-text search, then OR in a LIKE for each matched value.
	$conditions = array( $condition );
	foreach ( $matched_values as $value ) {
		$column = ( 'all' === $filter_key )
			? new GF_Query_Column( GF_Query_Column::META, 0 )
			: new GF_Query_Column( $filter_key, $gf_queryer->form_id );

		$conditions[] = new GF_Query_Condition(
			$column,
			GF_Query_Condition::LIKE,
			new GF_Query_Literal( $gf_queryer->get_sql_value( 'contains', $value ) )
		);
	}

	return GF_Query_Condition::_or( ...$conditions );
}, 10, 4 );
