<?php
/**
 * Gravity Perks // Submit to Access // Auto-allow Access for Administrators
 * https://gravitywiz.com/documentation/gravity-forms-submit-to-access/
 */
add_filter( 'gpsa_has_access', function( $has_access ) {
	if ( current_user_can( 'administrator' ) ) {
		return true;
	}

	return $has_access;
} );
