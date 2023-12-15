<?php
/**
 * Gravity Perks // Entry Blocks // Display Stars for Rating Fields
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 */
add_filter( 'gpeb_entry', function( $entry, $form ) {
	foreach ( $form['fields'] as $field ) {
		if ( $field->get_input_type() === 'rating' ) {
			$selected_value = $entry[ $field->id ];
			foreach ( $field->choices as $index => $choice ) {
				if ( $choice['value'] === $selected_value ) {
					$entry[ $field->id ] = str_repeat( '⭐', $index + 1 );
					break 2;
				}
			}
		}
	}
	return $entry;
}, 10, 2 );
