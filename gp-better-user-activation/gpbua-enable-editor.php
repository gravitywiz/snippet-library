<?php
/**
 * Gravity Perks // GP Better User Activation // Enable Editor on Activation Page
 * http://gravitywiz.com/documentionat/gravity-forms-better-user-activation/
 */
add_action( 'init', function() {
	if ( is_callable( 'gp_better_user_activation' ) ) {	
		remove_action( 'admin_head', array( gp_better_user_activation(), 'remove_default_content_editor' ) );
	}
} );