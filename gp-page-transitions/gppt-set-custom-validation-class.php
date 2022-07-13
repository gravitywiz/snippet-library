<?php
/**
 * Gravity Perks // Page Transitions // Set a Custom Validation CSS Class
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions
 *
 * Set a Custom Validation CSS Class.
 */
add_filter( 'gppt_script_args', 'set_custom_validation_class', 10, 2 );
function set_custom_validation_class( $args, $form ) {
	$args['transition'] = 'my-custom-validation-class';
	return $args;
}
