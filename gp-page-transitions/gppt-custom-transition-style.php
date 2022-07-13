<?php
/**
 * Gravity Perks // Page Transitions // Adding a Custom Transition Style
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 *
 * Add a Scroll Up Custom Transition Style.
 */
add_filter( 'gppt_transition_styles', 'my_custom_transition_style' );
function my_custom_transition_style( $styles ) {
	$styles['scrollUp'] = __( 'Scroll Up' );
	return $styles;
}
