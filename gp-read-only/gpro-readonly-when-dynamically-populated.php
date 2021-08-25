<?php
/**
 * Gravity Perks // Read Only // Readonly When Dynamically Populated
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 *
 * Use this snippet to make fields readonly if they are dynamically populated. You must enable the "Read-only" field
 * setting for this functionality to apply.
 * 
 * See video: 
 */
add_filter( 'gform_pre_render', function( $form, $ajax, $field_values ) {

	foreach ( $form['fields'] as &$field ) {
		if ( $field->gwreadonly_enable ) {
			$value = GFFormsModel::get_field_value( $field, $field_values, false );
			if ( ! $value ) {
				$field->gwreadonly_enable = false;
			}
		}
	}

	return $form;
}, 10, 3 );
