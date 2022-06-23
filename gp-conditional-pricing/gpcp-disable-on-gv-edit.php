<?php
/**
 * Gravity Perks // Conditional Pricing // Disable Conditional Pricing on GravityView Edit
 * https://gravitywiz.com/documentation/gravity-forms-conditional-pricing/
 */
add_action( 'gform_register_init_scripts', function( $form ) {
	if ( is_callable( 'gravityview_get_context' ) && gravityview_get_context() === 'edit' ) {
		unset( GFFormDisplay::$init_scripts[ $form['id'] ]['gwconditionalpricing_1'] );
	}
	return $form;
}, 11 );
