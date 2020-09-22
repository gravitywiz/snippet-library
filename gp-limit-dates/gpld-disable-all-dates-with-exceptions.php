<?php
/**
 * Gravity Perks // Limit Dates // Disable All Dates & Set Enabled Dates via Exceptions
 * http://gravitywiz.com/documentation/gravity-forms-limit-dates/
 */
add_filter( 'gpld_limit_dates_options_123_1', function( $options, $form, $field ) {

	$options['exceptionMode'] = 'enable';
	$options['disableAll'] = true;

	return $options;
}, 10, 3 );