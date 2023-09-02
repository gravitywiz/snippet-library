<?php
/**
 * Gravity Perks // Inventory // Only Show Next n Available Choices
 * https://gravitywins.com/documentation/gravity-forms-inventory/
 *
 * Use this snippet to only show the next n choices available (defaults to the next three).
 *
 * For example, you could show a list of dates and only show the first three date choices until one of them is sold out.
 * Then, the next available date would be shown when the form is rendered again.
 *
 * Video Demo
 *
 * https://www.loom.com/share/81fdb1f69e0c4234b7aa21120180e3e8?sid=2e9cc5ec-ba24-46f7-9712-2b0615aba74c
 *
 * Instructions
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Follow the inline instructions to configure the snippet for your form.
 *
 * 3. Configure your choice-based Inventory-enabled field to "Hide choice when inventory exhausted".
 */
// Update "123" to your form ID and "4" to your Inventory-enabled field.
add_filter( 'gform_field_choice_markup_pre_render_123_4', function( $markup ) {
	static $gw_choice_counter;
	if ( $gw_choice_counter === null ) {
		$gw_choice_counter = 0;
	}
	// Only increment counter for a field that would otherwise be displayed.
	if ( $markup ) {
		$gw_choice_counter++;
	}
	// Update "3" to the number of choices that should be shown.
	if ( $gw_choice_counter > 3 ) {
		$markup = '';
	}
	return $markup;
} );
