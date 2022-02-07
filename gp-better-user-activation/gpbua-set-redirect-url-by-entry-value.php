<?php
/**
 * Gravity Perks // GF Better User Activation // Set Redirect URL by Entry Value
 * http://gravitywiz.com/documentation/gravity-forms-better-user-activation/
 */
add_filter( 'gpbua_activation_redirect_url', function( $url, $activation ) {

	/**
	 * @var $activation GPBUA_Activate
	 */
	$entry = $activation->get_signup()->lead;

	// Update "123" to your target form ID.
	if ( $entry['form_id'] == 123 ) {
		// Update "1" to the field ID containing the page ID to which you would like to redirect.
		$url = get_permalink( $entry[1] );
	}

	return $url;
}, 10, 2 );
