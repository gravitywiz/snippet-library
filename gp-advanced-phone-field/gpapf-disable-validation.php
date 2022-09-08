<?php
/**
 * Gravity Perks // Advanced Phone Field // Disable Phone Validation
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * Instruction Video: https://www.loom.com/share/9d7666ed480b47c4847f76ebd415ddd8
 *
 * Plugin Name:  GP Advanced Phone Field — Disable Phone Validation
 * Description:  Disable phone number validation provided by Advanced Phone Field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_action( 'init', function () {
	if ( ! function_exists( 'gp_advanced_phone_field' ) ) {
		return;
	}

	remove_filter( 'gform_validation', array( gp_advanced_phone_field(), 'validation' ) );
}, 16 );
