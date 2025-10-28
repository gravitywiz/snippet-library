<?php
/**
 * Gravity Perks // Populate Anything // Filter Terms by Depth
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * WordPress doesn't keep track of the depth of a term in a hierarchical taxonomy. This snippet allows you to filter terms
 * by a target depth.
 *
 * Note: This will replace the configured value and label templates you may have configured for the field.
 */
add_filter( 'gppa_input_choices', function( $choices, $field, $objects ) {

	// Update this value to the target depth of terms you want to display.
	$target_depth = 3;

	if ( ! empty( $objects ) && isset( $objects[0]->taxonomy ) ) {
		$taxonomy = get_taxonomy( $objects[0]->taxonomy );
		if ( ! $taxonomy->hierarchical ) {
			return $choices;
		}
	} else {
		return $choices;
	}

	$all_terms = get_terms( array(
		'hide_empty' => false,
		'taxonomy'   => $taxonomy->name,
	) );

	// Walk through all terms but only output terms filtered by Populate Anything.
	$walker = function( $all_terms, $term_ids, $parent_id = 0, &$out_array = array(), $level = 0 ) use ( &$walker, $target_depth ) {
		foreach ( $all_terms as $term ) {
			if ( intval( $term->parent ) === intval( $parent_id ) ) {
				$is_applicable_term = in_array( $term->term_id, $term_ids );
				// Target depth is 1-based but level is 0-based. Subtract 1 from target depth to compare.
				if ( $is_applicable_term && $level === ( $target_depth - 1 ) ) {
					$out_array[] = $term;
				}
				$walker( $all_terms, $term_ids, $term->term_id, $out_array, ( $is_applicable_term ? $level + 1 : $level ) );
			}
		}
		return $out_array;
	};

	$terms   = $walker( $all_terms, wp_list_pluck( $objects, 'term_id' ) );
	$filtered_choices = array();

	foreach ( $terms as $object ) {
		$choice = array(
			'value'  => $object->term_id,
			'text'   => $object->name,
			'object' => $object,
		);
		
		// For Multi Choice fields with persistent choices, preserve existing choice structure.
		if ( method_exists( $field, 'has_persistent_choices' ) && $field->has_persistent_choices() ) {
			// Find matching choice in original choices to preserve key and other properties
			foreach ( $choices as $original_choice ) {
				if ( isset( $original_choice['object'] ) && $original_choice['object']->term_id == $object->term_id ) {
					$choice = array_merge( $original_choice, $choice );
					break;
				}
			}
			// If no existing choice found, generate key for new choices
			if ( ! isset( $choice['key'] ) ) {
				$choice['key'] = md5( $object->term_id );
			}
		}
		
		$filtered_choices[] = $choice;
	}

	return $filtered_choices;
}, 10, 3 );
