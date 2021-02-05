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

	$walker = function( $terms, $parent_id = 0, &$out_array = array(), $level = 0 ) use ( &$walker ) {
		foreach ( $terms as $item ) {
			if ( intval( $item->parent ) === intval( $parent_id ) ) {
				$item->name  = str_repeat( '—', $level ) . ' ' . $item->name;
				$out_array[] = $item;
				$walker( $terms, $item->term_id, $out_array, $level + 1 );
			}
		}
		return $out_array;
	};

	$objects = $walker( $objects );
	$choices = array();

	foreach ( $objects as $object ) {
		$choices[] = array(
			'value' => $object->term_id,
			'text'  => $object->name,
		);
	}

	return $choices;
}, 10, 3 );
