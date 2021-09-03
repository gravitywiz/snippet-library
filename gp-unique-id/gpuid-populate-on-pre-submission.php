<?php
/**
 * Gravity Perks // GP Unique ID // Populate Unique ID on Pre Submission (rather than Post Entry Creation)
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Generate the Unique ID before the entry is actually created. This method is slightly less reliable for guaranteeing a truly unique ID;
 * however, in some cases, you may want access to the unique ID prior to the entry creation.
 *
 * Plugin Name:  GP Unique ID - Populate Unique ID on Pre Submission
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-unique-id/
 * Description:  Generate and populate the Unique ID on pre submission prior to the entry creation. 
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_action( 'gform_pre_submission', function( $form ) {

	if( ! function_exists( 'gp_unique_id' ) ) {
		return;
	}

	// get the GP Unique ID field class, we'll need it to access it's methods
	$gpui_field = gp_unique_id()->field_obj;

	// remove the default GP Unique ID functionality that populates the unique when the entry is saved
	remove_filter( 'gform_entry_post_save', array( $gpui_field, 'populate_field_value' ) );

	// loop through the submitted form object for Unique ID fields
	foreach( $form['fields'] as $field ) {
		if( $field->get_input_type() == 'uid' && ! GFFormsModel::is_field_hidden( $form, $field, array() ) ) {

			// generate a unique ID
			$value = gp_unique_id()->get_unique( $form['id'], $field );

			// populate the unique ID into the $_POST so Gravity Forms will populate it into the entry
			$_POST[ sprintf( 'input_%s', $field['id'] ) ] = $value;

			// since the "current entry" is already set, we need to update it manually so other plugins will have access to the unique ID
			$entry = GFFormsModel::get_current_lead();
			$entry[ $field['id'] ] = $value;
			GFFormsModel::set_current_lead( $entry );

		}
	}

}, 9 );
