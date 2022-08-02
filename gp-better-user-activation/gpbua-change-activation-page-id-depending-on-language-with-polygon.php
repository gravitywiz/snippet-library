<?php
/**
 * Gravity Perks // Better User Activation // Change Activation Page ID Depending on Language With Polylang
 * https://gravitywiz.com/documentation/gravity-forms-better-user-activation/
 */
add_filter( 'gpbua_activation_page_id', function( $activation_page_id ) {

	if ( function_exists( 'pll_current_language' ) ) {
		switch ( pll_current_language() ) {
			// Update the case with your preferred language code and respective activation page ID.
			case 'en':
				$activation_page_id = 123;
				break;
			case 'fr':
				$activation_page_id = 124;
				break;
			case 'de':
				$activation_page_id = 125;
				break;
		}
	}

	return $activation_page_id;
} );
