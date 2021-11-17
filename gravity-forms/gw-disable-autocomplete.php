<?php
/**
 * Gravity Wiz // Gravity Forms // Disable Auto-complete
 *
 * Disable browser auto-complete.
 *
 * @version 0.3
 * @license GPL-2.0+
 * @link    http://gravitywiz.com
 */
// Disable auto-complete on form.
add_filter( 'gform_form_tag', function( $form_tag ) {
	$autocomplete = gw_get_browser_name( $_SERVER['HTTP_USER_AGENT'] ) === 'Chrome' ? 'off' : 'off';
	return str_replace( '>', ' autocomplete="' . $autocomplete . '">', $form_tag );
}, 11 );

// Diable auto-complete on each field.
add_filter( 'gform_field_content', function( $input ) {
	$autocomplete = gw_get_browser_name( $_SERVER['HTTP_USER_AGENT'] ) === 'Chrome' ? 'off' : 'off';
	return preg_replace( '/<(input|textarea)/', '<${1} autocomplete="' . $autocomplete . '" ', $input );
}, 11 );

if ( ! function_exists( 'gw_get_browser_name' ) ) {
	function gw_get_browser_name( $user_agent ) {
		if ( strpos( $user_agent, 'Opera' ) || strpos( $user_agent, 'OPR/' ) ) {
			return 'Opera';
		} elseif ( strpos( $user_agent, 'Edge' ) ) {
			return 'Edge';
		} elseif ( strpos( $user_agent, 'Chrome' ) ) {
			return 'Chrome';
		} elseif ( strpos( $user_agent, 'Safari' ) ) {
			return 'Safari';
		} elseif ( strpos( $user_agent, 'Firefox' ) ) {
			return 'Firefox';
		} elseif ( strpos( $user_agent, 'MSIE' ) || strpos( $user_agent, 'Trident/7' ) ) {
			return 'Internet Explorer';
		}

		return 'Other';
	}
}
