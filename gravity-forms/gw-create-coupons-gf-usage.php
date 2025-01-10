<?php
/**
 * Gravity Wiz // Gravity Forms // Create Coupons with Gravity Forms for Gravity Forms
 * https://gravitywiz.com/creating-coupons-for-gf-coupons-add-on-with-gravity-forms/
 */

// Coupon with Flat Discount, Applied to Total
new GW_Create_Coupon(
	array(
		'form_id'         => 608,
		'source_field_id' => 1,
		'plugin'          => 'gf',
		'amount'          => 15,
		'type'            => 'flat',
	)
);

// Coupon with Percentage Discount, Applied to Total
new GW_Create_Coupon(
	array(
		'form_id'         => 608,
		'source_field_id' => 1,
		'plugin'          => 'gf',
		'amount'          => 15,
		'type'            => 'percentage',
	)
);

// Stackable Coupon with Usage Limit and Expiration Date
new GW_Create_Coupon(
	array(
		'form_id'         => 608,
		'source_field_id' => 1,
		'plugin'          => 'gf',
		'amount'          => 15,
		'type'            => 'flat',
		'meta'            => array(
			'form_id'           => 620,
			'coupon_stackable'  => true,
			'coupon_limit'      => 10,
			'coupon_expiration' => '12/31/2015',
		),
	)
);

// Unlimited Use Coupon during the month of December 2015 using Coupon Start and Coupon Expiration
new GW_Create_Coupon(
	array(
		'form_id'         => 608,
		'source_field_id' => 1,
		'plugin'          => 'gf',
		'amount'          => 15,
		'type'            => 'flat',
		'meta'            => array(
			'form_id'           => 620,
			'coupon_stackable'  => false,
			'coupon_limit'      => false,
			'coupon_start'      => '12/1/2015',
			'coupon_expiration' => '12/31/2015',
		),
	)
);

// All the things!
new GW_Create_Coupon(
	array(
		'form_id'         => 608,
		'source_field_id' => 1,
		'plugin'          => 'gf',
		'amount'          => 15,
		'type'            => 'flat', // accepts: 'flat', 'percentage'
		'meta'            => array(
			'form_id'           => false,
			'coupon_start'      => '', // MM/DD/YYYY
			'coupon_expiration' => '', // MM/DD/YYYY
			'coupon_limit'      => false,
			'coupon_stackable'  => false,
		),
	)
);
