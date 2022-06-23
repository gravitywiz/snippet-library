<?php
/**
 * Gravity Perks // Limit Dates // Disable All Dates & Set Enabled Dates via Exceptions
 * http://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Instruction Video: https://www.loom.com/share/46883f47e71447108f70fd725af8ee97
 */
add_filter( 'gpld_limit_dates_options_123_1', function( $options, $form, $field ) {

	$options['exceptionMode'] = 'enable';
	$options['disableAll']    = true;

	return $options;
}, 10, 3 );
