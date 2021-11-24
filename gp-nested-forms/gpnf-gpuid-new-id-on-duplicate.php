<?php
/**
 * Gravity Perks // Nested Forms + Unique ID // Generate New ID on Child Entry Duplication
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gpnf_duplicate_entry', function( $entry ) {
	if ( ! is_callable( 'gp_unique_id' ) ) {
		return $entry;
	}
	$form = GFAPI::get_form( $entry['form_id'] );
	foreach ( $form['fields'] as $field ) {
		if ( is_a( $field, 'GF_Field_Unique_ID' ) ) {
			$entry[ $field->id ] = gp_unique_id()->get_unique( $form['id'], $field );
		}
	}
	return $entry;
} );
