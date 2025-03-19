<?php
/**
 * Gravity Wiz // Gravity Forms // Sentence Merge Tag Modifier for Checkboxes
 * https://gravitywiz.com
 *
 * Use the :sentence modifier on Checkbox field merge tags to convert...
 *
 * this: First Choice, Second Choice
 * to:   First Choice and Second Choice
 *
 * this: First Choice, Second Choice, Third Choice
 * to:   First Choice, Second Choice, and Third Choice.
 */
add_filter( 'gform_merge_tag_filter', function( $value, $input_id, $modifier, $field, $raw_values, $format ) {

	if ( empty( $modifier ) ) {
		return $value;
	}

	$modifiers = array_map( 'strtolower', explode( ',', $modifier ) );
	if ( ! in_array( 'sentence', $modifiers, true ) ) {
		return $value;
	}

	$values = $raw_values;
	if ( $field->storageType === 'json' ) {
		$values = json_decode( $values );
		if ( ! is_array( $values ) ) {
			$values = array();
		}
	}

	$values = array_filter( array_map( 'trim', $values ) );
	$count  = count( $values );

	if ( $count > 1 ) {
		$last_value = array_pop( $values );
	}

	$value = implode( ', ', $values );

	if ( $count === 2 ) {
		// Gives us: First Choice and Second Choice.
		$value .= " and {$last_value}";
	} elseif ( isset( $last_value ) ) {
		// Gives us: First Choice, Second Choice, and Third Choice.
		$value .= ", and {$last_value}";
	}

	return $value;
}, 10, 6 );
