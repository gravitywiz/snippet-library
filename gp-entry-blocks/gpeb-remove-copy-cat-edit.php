<?php
/**
 * Gravity Perks // Entry Blocks // Remove Copy Cat on Edit.
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Removes copy cat functionality on Entry Blocks' Edit Page.
 */
add_filter( 'gpeb_edit_form', function ( $form ) {
	foreach ( $form['fields'] as &$field ) {
		// remove any of the copy cat classes from the field.
		preg_match_all( '/copy-([0-9]+(?:.[0-9]+)*)-to-([0-9]+(?:.[0-9]+)*)(?:-if-([0-9]+(?:.[0-9]+)*))?/', $field['cssClass'], $matches, PREG_SET_ORDER );
		if ( empty( $matches ) ) {
			continue;
		}
		$field['cssClass'] = str_replace( $matches[0], '', $field['cssClass'] );
	}

	return $form;
} );
