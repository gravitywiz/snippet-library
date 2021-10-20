<?php
/**
 * Gravity Wiz // Gravity Forms Coupons // Allow Zero Amount Coupons
 * https://gravitywiz.com/
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
