<?php
/**
 * Gravity Perks // Live Preview // Enable Globally For Admins
 * https://gravitywiz.com/documentation/gravity-forms-live-preview/
 */
add_filter( 'gplp_enable_globally', function( $is_global ) {
	return current_user_can( 'administrator' );
} );
