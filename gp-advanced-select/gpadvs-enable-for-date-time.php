<?php
/**
 * Gravity Perks // Advanced Select // Enable Advanced Select for Date & Time Fields
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Enable Advanced Select for Date and Time Fields.
 *
 * Instruction Video: https://www.loom.com/share/6a681f81df3f4043a85aed7e8c38bc1f
 *
 * Instructions:
 *
 * 1. Install this snippet by following the instructions here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */

add_filter( 'gpadvs_is_supported_input_type', function ( $supported_types ) {
	array_push( $supported_types, 'date', 'time' );

	return $supported_types;
}, 10, 1 );

add_filter( 'gpadvs_js_init_args', function ( $init_args, $form, $field ) {
	// For Date Dropdown, it must be enabled on all of the MM / DD / YYYY
	if ( $field->type == 'date' && $field->dateType == 'datedropdown' && $field->gpadvsEnable ) {
		// Initialize script for each part: Month (_1), Day (_2), Year (_3)
		foreach ( range( 1, 3 ) as $index ) {
			$init_args_for_date_dropdown            = $init_args;
			$init_args_for_date_dropdown['fieldId'] = $init_args['fieldId'] . '_' . $index;

			$script = 'new GPAdvancedSelect(' . json_encode( $init_args_for_date_dropdown ) . ');';
			$slug   = 'gp_advanced_select_' . $init_args_for_date_dropdown['formId'] . '_' . $init_args_for_date_dropdown['fieldId'] . '_' . $index;

			// Add the script for this Date part
			GFFormDisplay::add_init_script( $init_args_for_date_dropdown['formId'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
		}
		// ignore the default script
		return array();
	}

	// Time Field Only has dropdown on the AM/PM.
	if ( $field->type == 'time' && $field->gpadvsEnable ) {
		$init_args['fieldId'] = $init_args['fieldId'] . '_3';
	}

	return $init_args;
}, 10, 3 );
