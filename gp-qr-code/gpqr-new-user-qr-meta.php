<?php
/**
 * Gravity Perks // QR Codes // Generate User ID QR Code for New Users
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 */
// Update "user_register" to "gform_user_registered" to limit QR generation only to users created via a Gravity Form.
add_action( 'user_register', function( $user_id ) {
	if ( is_callable( 'gp_qr_code' ) ) {
		add_user_meta( $user_id,'qr_code', gp_qr_code()->generator->generate( (string) $user_id ) );
	}
} );
