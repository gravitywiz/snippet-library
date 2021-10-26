<?php
/**
 * Gravity Wiz // Gravity Forms Coupons // Allow Zero Amount Coupons
 * https://gravitywiz.com/
 *
 * By default it's not possible to create a coupn with the amount of zero.
 * This snippet allows you to create coupons with the amount of zero.
 *
 * Plugin Name:  Gravity Forms Coupons - Allow Zero Amount Coupons
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Create coupons with the amount of zero.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_gravityformscoupons_feed_settings_fields', function( $settings ) {

	foreach ( $settings as &$group ) {
		foreach ( $group['fields'] as &$field ) {
			if ( $field['name'] == 'couponAmountType' ) {
				$field['validation_callback'] = null;
			}
		}
	}

	return $settings;
} );
