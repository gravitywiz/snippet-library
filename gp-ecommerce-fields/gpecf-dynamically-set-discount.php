<?php
/**
 * Gravity Perks // GP eCommerce Fields // Dynamically Set Discount Amount
 * http://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_render_123', 'gw_set_discount_amount' );
add_filter( 'gform_pre_validation_123', 'gw_set_discount_amount' );
add_filter( 'gform_pre_submission_filter_123', 'gw_set_discount_amount' );
function gw_set_discount_amount( $form ) {

	foreach( $form['fields'] as &$field ) {

		// Update "2" to your Discount field ID.
		if( $field->id == 2 ) {
			$field->discountAmount = 50;
		}

	}

	return $form;
}
