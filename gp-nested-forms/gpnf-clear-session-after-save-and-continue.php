<?php
/**
 * Gravity Perks // Nested Forms // Clear Session after Save & Continue
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_action( 'gform_post_process', function( $form ) {
	if ( rgpost( 'gform_save' ) && class_exists( 'GPNF_Session' ) ) {
		$session = new GPNF_Session( $form['id'] );
		$session->delete_cookie();
	}
} );
