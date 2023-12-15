<?php
/**
 * Gravity Forms // Submit to Access // Auto-allow Access for Administrators
 * https://gravitywiz.com/submit-gravity-form-access-content/
 */
add_filter( 'gfsa_has_access', function( $has_access ) {
	return current_user_can( 'administrator' );
} );
