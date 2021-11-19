<?php
/**
 * Gravity Perks // GP eCommerce Fields // Set Tax Amount Dynamically
 * https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 */
// Update '123' to the Form ID
add_filter( 'gform_pre_render_123', 'gpecf_dynamic_tax_amount' );
add_filter( 'gform_pre_validation_123', 'gpecf_dynamic_tax_amount' );
add_filter( 'gform_pre_submission_filter_123', 'gpecf_dynamic_tax_amount' );
add_filter( 'gform_admin_pre_render_123', 'gpecf_dynamic_tax_amount' );
function gpecf_dynamic_tax_amount( $form ) {

	if ( $form['fields'][0]->is_form_editor() ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {
		// Update '4' to the Tax field ID
		if ( $field->id == 4 ) {
			$field->taxAmount = 25; // Update '25' to the tax percentage amount.
		}
	}

	return $form;
}
