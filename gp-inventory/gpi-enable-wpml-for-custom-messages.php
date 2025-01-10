<?php
/**
 * Gravity Perks // Inventory // WPML Support for Custom Inventory Messages
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
add_filter( 'gform_pre_render', function( $form ) {
	if ( ! function_exists( 'icl_register_string' ) || ! function_exists( 'gp_inventory' ) ) {
		return $form;
	}

	$form_id = $form['id'];
	foreach ( $form['fields'] as $field ) {
		if ( rgar( $field, 'gpiMessageInventoryInsufficient', false ) ) {
			$name = "inventory-insufficient-message-{$form_id}-{$field->id}";
			icl_register_string( 'gp-inventory', $name, $field['gpiMessageInventoryInsufficient'] );
			add_filter( "gpi_inventory_insufficient_message_{$form_id}_{$field->id}", function( $message ) use ( $name ) {
				return  apply_filters( 'wpml_translate_single_string', $message, 'gp-inventory', $name );
			} );
		}
		if ( rgar( $field, 'gpiMessageInventoryExhausted', false ) ) {
			$name = "inventory-exhausted-message-{$form_id}-{$field->id}";
			icl_register_string( 'gp-inventory', $name, $field['gpiMessageInventoryExhausted'] );
			add_filter( "gpi_inventory_exhausted_message_{$form_id}_{$field->id}", function( $message ) use ( $name ) {
				return  apply_filters( 'wpml_translate_single_string', $message, 'gp-inventory', $name );
			} );
		}
		if ( rgar( $field, 'gpiMessageInventoryAvailable', false ) ) {
			$name = "inventory-available-message-{$form_id}-{$field->id}";
			icl_register_string( 'gp-inventory', $name, $field['gpiMessageInventoryAvailable'] );
			add_filter( "gpi_inventory_available_message_{$form_id}_{$field->id}", function( $message ) use ( $name ) {
				return  apply_filters( 'wpml_translate_single_string', $message, 'gp-inventory', $name );
			} );
		}
	}

	return $form;
} );
