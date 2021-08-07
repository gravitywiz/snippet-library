<?php
/**
 * Gravity Perks // GP eCommerce Fields // Dynamically Set Discount Amount
 * http://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 */
// UPDATE: 123 to your form ID.
add_filter( 'gform_pre_render_123', 'gw_set_discount_amount' );
add_filter( 'gform_pre_validation_123', 'gw_set_discount_amount' );
add_filter( 'gform_pre_submission_filter_123', 'gw_set_discount_amount' );
add_filter( 'gform_admin_pre_render_123', 'gw_set_discount_amount' );
function gw_set_discount_amount( $form ) {

	if ( current_filter() === 'gform_admin_pre_render_' . $form['id'] && GFForms::get_page() !== 'entry_detail' ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {

		// Update "5" to your Discount field ID.
		if ( $field->id == 5 ) {
			$field->discountAmount = 50;
		}

	}

	return $form;
}
