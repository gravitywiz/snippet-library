<?php
/**
 * Gravity Perks // Page Transitions // Set a Custom Transition Style
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 *
 * Set a Custom Transition Style
 */
add_filter( 'gppt_script_args', 'set_custom_transition_style', 10, 2 );
function set_custom_transition_style( $args, $form ) {
	$args['transitionSettings']['fx'] = 'scrollUp';
	return $args;
}
