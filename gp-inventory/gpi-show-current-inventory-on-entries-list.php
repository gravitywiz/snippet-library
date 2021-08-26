<?php
/**
 * Gravity Perks // GP Inventory // Display Simple Inventory Type Current/Remaining Inventory
 * http://gravitywiz.com/documentation/gravity-forms-inventory/
 */
add_action( 'gform_pre_entry_list', function ( $form_id ) {
	$form = GFAPI::get_form( $form_id );

	if ( ! function_exists( 'gp_inventory_type_simple' ) ) {
		return;
	}

	if ( ! gp_inventory_type_simple()->is_applicable_form( $form ) ) {
		return;
	}

	$message_content = '<strong>' . __( 'Current Inventory:', 'gp-inventory' ) . '</strong><ul style="margin-top: .5rem;padding: 0 0 0 2rem;">';

	foreach ( gp_inventory_type_simple()->get_applicable_fields( $form ) as $inventory_field ) {
		$available = gp_inventory_type_simple()->get_available_stock( $inventory_field );

		$message_content .= '<li style="list-style: disc;">' . $inventory_field->get_field_label( false, '' ) . ': ' . $available . '</li>';
	}

	$message_content .= '</ul>';

	GFCommon::add_message( $message_content );
} );
