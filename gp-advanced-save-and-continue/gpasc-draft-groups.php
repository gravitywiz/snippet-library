<?php
/**
 * Gravity Perks // Advanced Save & Continue // Draft Groups by Query Parameter
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 *
 * Instruction Video: https://www.loom.com/share/d8fae1209023492f8a163401b934b052
 *
 * Create draft groups where only drafts saved from a URL with the same group ID
 * (specified by a query parameter) will be displayed when that query parameter is
 * present.
 */
add_filter( 'gpasc_form_resume_tokens', function( $tokens, $form_id ) {
	// Update "order_id" to your desired query parameter.
	$target_parameter = 'order_id';
	$target_value     = rgget( $target_parameter );
	if ( ! $target_value ) {
		return $tokens;
	}
	foreach ( $tokens as &$token ) {
		// If token is specifically passed via parameter, don't interfere.
		$query_token = rgget( 'gf_token' );
		if ( $query_token && $query_token === $token['token'] ) {
			continue;
		}
		$path = parse_url( $token['form_path'], PHP_URL_QUERY );
		parse_str( $path, $params );
		if ( ! isset( $params[ $target_parameter ] ) || $params[ $target_parameter ] != $target_value ) {
			$token = null;
		}
		unset( $token );
	}
	return array_filter( $tokens );
}, 10, 2 );
