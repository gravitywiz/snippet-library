<?php
/**
 * Gravity Perks // Populate Anything // Display Terms Hierarchically
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Any field populated by GPPA with terms that belong to a hierarchical taxonomy will render these terms indented based
 * on their hierarchy.
 *
 * Note: This will replace the configured value and label templates you may have configured for the field.
 */
add_filter( 'gppa_input_choices', function( $choices, $field, $objects ) {

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
	$walker = function( $all_terms, $term_ids, $parent_id = 0, &$out_array = array(), $level = 0 ) use ( &$walker ) {
		foreach ( $all_terms as $term ) {
			if ( intval( $term->parent ) === intval( $parent_id ) ) {
				$is_applicable_term = in_array( $term->term_id, $term_ids );
				if ( $is_applicable_term ) {
					$term->name  = str_repeat( '—', $level ) . ' ' . $term->name;
					$out_array[] = $term;
				}
				$walker( $all_terms, $term_ids, $term->term_id, $out_array, ( $is_applicable_term ? $level + 1 : $level ) );
			}
		}
		return $out_array;
	};

	$terms   = $walker( $all_terms, wp_list_pluck( $objects, 'term_id' ) );
	$choices = array();

	foreach ( $terms as $object ) {
		$choices[] = array(
			'value'  => $object->term_id,
			'text'   => $object->name,
			'object' => $object,
		);
	}

	return $choices;
}, 10, 3 );
