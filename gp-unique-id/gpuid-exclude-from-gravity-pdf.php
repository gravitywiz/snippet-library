<?php
/**
 * Gravity Perks // Unique ID // Exclude Unique ID Fields from Gravity PDF
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Exclude Unique ID Fields, when using Gravity PDF together with GP Unique ID Perk.
 *
 * Plugin Name:  GP Unique ID - Exclude Unique ID Fields from Gravity PDF
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-unique-id/
 * Description:  Exclude Unique ID Fields, when using Gravity PDF together with GP Unique ID Perk.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_form_post_get_meta', function( $form ) {
	foreach ( $form['fields'] as &$field ) {
		if ( $field->get_input_type() == 'uid' ) {
			$field->cssClass = 'exclude';
		}
	}
	return $form;
} );
