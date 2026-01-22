<?php
/**
 * Gravity Wiz // Gravity Forms // Restrict Coupons by Page ID
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/d34a76c4450c478f8db6c601260e6115
 *
 * Restrict coupon usage based on the current page ID where the form is being displayed.
 * This snippet uses the gform_coupons_can_apply_coupon filter to add custom validation
 * logic that checks if a coupon can be applied based on the page ID.
 */
add_filter( 'gform_coupons_can_apply_coupon', function ( $can_apply, $coupon_code, $existing_coupon_codes, $feed, $form ) {

	// TODO: Define coupon restrictions by page ID.
	$coupon_page_restrictions = array(
		'abc' => array( 57 ),    // Coupon 'abc' only works on page ID 57
		'xyz' => array( 59 ),    // Coupon 'xyz' only works on page ID 59
		// Add more coupon restrictions as needed.
	);

	$current_page_id = get_the_ID();
	if ( ! $current_page_id && ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		$current_page_id = url_to_postid( esc_url_raw( $_SERVER['HTTP_REFERER'] ) );
	}

	// If we can't get the page ID, allow the coupon (fallback).
	if ( ! $current_page_id ) {
		return $can_apply;
	}

	// Check if this coupon has page restrictions.
	$coupon_code_key = strtolower( $coupon_code );
	if ( isset( $coupon_page_restrictions[ $coupon_code_key ] ) ) {
		$allowed_page_ids = $coupon_page_restrictions[ $coupon_code_key ];
		if ( ! in_array( $current_page_id, $allowed_page_ids ) ) {
			$can_apply['is_valid']       = false;
			$can_apply['invalid_reason'] = esc_html__( 'Invalid coupon.', 'gravityformscoupons' );
		}
	}

	return $can_apply;

}, 10, 5 );
