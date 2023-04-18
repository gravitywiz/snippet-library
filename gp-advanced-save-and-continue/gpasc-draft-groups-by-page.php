<?php
/**
 * Gravity Perks // Advanced Save & Continue // Draft Groups by Page
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-continue/
 *
 * Create draft groups where only drafts saved from the current URL will be displayed. For example, if you're using the
 * same form on multiple pages, you can use this snippet to display drafts saved from the current page only.
 */
add_filter( 'gpasc_form_resume_tokens', function( $tokens, $form_id ) {

	// Set ignore parameters to false if you would like parameters to be included in identifying unique pages.
	$ignore_parameters = true;

	if ( wp_doing_ajax() ) {
		return $tokens;
	}

	$target_value = $_SERVER['REQUEST_URI'];
	if ( $ignore_parameters ) {
		$target_value = strtok( $target_value, '?' );
	}

	foreach ( $tokens as $index => $token ) {
		// If token is specifically passed via parameter, don't interfere.
		$query_token = rgget( 'gf_token' );
		if ( $query_token && $query_token === $token['token'] ) {
			continue;
		}
		$token_path = $token['form_path'];
		if ( $ignore_parameters ) {
			$token_path = strtok( $token['form_path'], '?' );
		}
		if ( $token_path !== $target_value ) {
			unset( $tokens[ $index ] );
		}
	}

	return array_filter( $tokens );
}, 10, 2 );
