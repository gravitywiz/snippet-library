<?php
/**
 * Gravity Perks // Page Transitions // Add a Custom Auto-Progression Condition
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 *
 * Add a custom auto-progression condition for a field.
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
