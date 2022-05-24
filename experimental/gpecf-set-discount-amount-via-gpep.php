<?php
/**
 * Gravity Perks // GP eCommerce Fields // Dynamically Set Discount Amount via Easy Passthrough
 * http://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * This experimental snippet allows you to set the discount amount by a field that has been populated via Easy Passthrough.
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_render_123', 'gw_set_discount_amount' );
add_filter( 'gform_pre_process_123', 'gw_set_discount_amount' );
add_filter( 'gform_admin_pre_render_123', 'gw_set_discount_amount' );
function gw_set_discount_amount( $form ) {

	if ( current_filter() === 'gform_admin_pre_render_' . $form['id'] && GFForms::get_page() !== 'entry_detail' ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {
		// Update "5" to your Discount field ID.
		if ( $field->id == 3 ) {
			// Update "6" to your
			$field->discountAmount = gw_get_gpep_value( $form['id'], 6 );
		}
	}

	return $form;
}

function gw_get_gpep_value( $form_id, $field_id ) {

	if ( GFForms::get_page() && rgget( 'lid' ) ) {
		$entry        = GFAPI::get_entry( rgget( 'lid' ) );
		$field_values = $entry;
	} else {
		$field_values = gp_easy_passthrough()->get_field_values( $form_id );
	}

	return rgar( $field_values, $field_id );
}
