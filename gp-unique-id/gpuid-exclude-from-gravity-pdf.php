<?php
/**
 * Gravity Perks // Unique ID // Exclude Unique ID Fields from Gravity PDF
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 */
add_filter( 'gform_form_post_get_meta', function( $form ) {
	foreach ( $form['fields'] as &$field ) {
		if ( $field->get_input_type() == 'uid' ) {
			$field->cssClass = 'exclude';
		}
	}
	return $form;
} );
