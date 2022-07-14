<?php
/**
 * Gravity Perks // Page Transitions // Add a Custom Auto-Progression Condition
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 *
 * Use this filter to register a field type and callback function that can check a field's configuration
 * to determine if it can support auto-progresion.
 */
add_filter( 'gppt_auto_progress_support_conditions', 'add_custom_auto_progress_condition' );
function add_custom_auto_progress_condition( $conditions ) {
	$conditions[] = array(
		'type'     => 'my_custom_field_type',
		'callback' => 'my_custom_field_callback_func',
	);
	return $conditions;
}

function my_custom_field_callback_func( $field ) {
	return $field->someFeature == true;
}
