<?php
/**
 * Gravity Perks // GP Inventory // Display Simple Inventory Type Current/Remaining Inventory
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Instruction Video: https://www.loom.com/share/1ddb22a2bc584462a83e3158c1a7c64a
 */

add_action( 'gform_pre_entry_list', function ( $form_id ) {
	$form = GFAPI::get_form( $form_id );

	if ( ! function_exists( 'gp_inventory_type_simple' ) ) {
		return;
	}

	if ( ! gp_inventory_type_simple()->is_applicable_form( $form, true ) ) {
		return;
	}

	$message_content = '<strong>' . __( 'Current Inventory:', 'gp-inventory' ) . '</strong><ul style="margin-top: .5rem;padding: 0 0 0 2rem;">';

	foreach ( gp_inventory_type_simple()->get_applicable_fields( $form, true ) as $inventory_field ) {

		if ( isset( $inventory_field->choices ) && is_array( $inventory_field->choices ) ) {
			$message_content .= '<li style="list-style: disc;">' . $inventory_field->get_field_label( false, '' );
			$message_content .= '<ul style="margin-top: .5rem;padding: 0 0 0 2rem;">';
			$counts          = gp_inventory_type_choices()->get_choice_counts( $form['id'], $inventory_field );

			foreach ( $inventory_field->choices as $choice ) {

				$limit     = (int) $choice['inventory_limit'];
				$count     = (int) rgar( $counts, $choice['value'] );
				$available = (int) $limit - $count;

				$message_content .= '<li style="list-style: circle;">' . $choice['text'] . ': ' . $available . '</li>';
			}

			$message_content .= '</ul></li>';
		} else {
			$available = gp_inventory_type_simple()->get_available_stock( $inventory_field );

			$message_content .= '<li style="list-style: disc;">' . $inventory_field->get_field_label( false, '' ) . ': ' . $available . '</li>';
		}
	}

	$message_content .= '</ul>';

	GFCommon::add_message( $message_content );
} );
