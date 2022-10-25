<?php
/**
 * Gravity Perks // Multi-page Navigation // Unhook Validation Message Filter
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 *
 * If you are customizing a forms validation message with the `gform_validation_message` hook, you
 * may be frustrated to discover that Multi-page Navigation will override your changes. If you only
 * need to modify the validation message for a single form, the easiest method is to use the 
 * form-specific version of that hook with a priority of 11.
 *
 * add_filter( 'gform_validation_message_123', 'your_func_name', 11 );
 *
 * If you are applying a change globally, use this snippet to remove Multi-page Navigations' 
 * validation customization altogether.
 */
add_filter( 'gform_validation_message', function( $message, $form ) {
	global $wp_filter;
	$hook = "gform_validation_message_{$form['id']}";
	if ( ! isset( $wp_filter[ $hook ] ) ) {
		return $message;
	}
	foreach ( $wp_filter[ $hook ]->callbacks[10] as $callback ) {
		if ( is_a( $callback['function'][0], 'GP_Multi_Page_Navigation' ) ) {
			remove_filter( $hook, $callback['function'] );
		}
	}
	return $message;
}, 10, 2 );
