<?php
/**
 * Gravity Perks // Inventory // Disable Inventory for a Form After a Specific Date
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
add_filter( 'gform_form_post_get_meta', function( $form ) {

	// Update to a list of form IDs to target. Leave empty to target all forms.
	$form_ids = array();

	// Update to the date after which inventory should be disabled for the target form(s).
	$target_date = '2023-05-31';

	// Don't run on any Gravity Forms administrative page.
	if ( GFForms::get_page() ) {
		return $form;
	}

	$is_target_form = empty( $form_ids ) || in_array( $form['id'], $form_ids );
	if ( $is_target_form && gmdate( 'Y-m-d' ) > $target_date ) {
		foreach ( $form['fields'] as &$field ) {
			unset( $field->gpiInventory );
		}
	}

	return $form;
} );
