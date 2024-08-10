<?php
/**
 * Gravity Perks // GP Limit Dates // Inline Datepicker only on Mobile Devices.
 * http://gravitywiz.com/documentation/gp-limit-dates/
 *
 * Instruction Video: https://www.loom.com/share/2e3e9838f2994b619402d18ff9f96114
 */
// Replace 278 with your Form ID, and 3 with your Date Field ID
add_filter( 'gpld_limit_dates_options_278_3', function( $options, $form, $field ) {
	if ( ! wp_is_mobile() ) {
		$options['inlineDatepicker'] = false;
	}
	return $options;
}, 10, 3);
