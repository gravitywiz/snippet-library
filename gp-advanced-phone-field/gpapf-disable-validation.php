<?php
/**
 * Gravity Perks // Advanced Phone Field // Disable Phone Validation
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * Instruction Video: https://www.loom.com/share/ab1b6a0f4f7b4751b253bface18bfa3e
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

	remove_filter( 'gform_field_validation', array( gp_advanced_phone_field(), 'validation' ) );
}, 16 );
