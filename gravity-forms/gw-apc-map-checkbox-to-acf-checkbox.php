<?php
/**
 * Gravity Wiz // Gravity Forms // Map GF Checkbox to ACF Checkbox via Advanced Post Creation Add-on
 * https://gravitywiz.com/
 *
 * Map Gravity Forms Checkbox fields to ACF Checkbox fields when using the Advanced Post Creation add-on.
 */
add_filter( 'gform_advancedpostcreation_post_after_creation', function( $post_id, $feed, $entry, $form ) {
	foreach ( $feed['meta']['postMetaFields'] as $post_meta_field ) {
		$field_id = rgar( $post_meta_field, 'value' );
		$field    = GFAPI::get_field( $form, $field_id );
		if ( ! $field || $field->get_input_type() !== 'checkbox' ) {
			continue;
		}
		$meta_key   = rgar( $post_meta_field, 'key' ) === 'gf_custom' ? $post_meta_field['custom_key'] : $post_meta_field['key'];
		$meta_value = $field->get_value_export( $entry );
		if ( is_callable( 'acf_get_field' ) ) {
			$acf_field = acf_get_field( $meta_key );
			if ( $acf_field ) {
				$meta_value = array_map( 'trim', explode( ',', $meta_value ) );
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}
	}
}, 10, 4 );
