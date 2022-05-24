<?php
/**
 * Gravity Perks // Populate Anything // Populate & Delete Coupons
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * This experimental snippet demonstrates how to populate a Drop Down field with coupons (assisted by Populate Anything)
 * and then delete the selected coupon on submission.
 */
// Update "123" to your form ID and "4" to your Drop Down field ID that is populated with coupons.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {

	$choices = array();

	foreach ( $objects as $object ) {
		$meta = json_decode( $object['meta'], ARRAY_A );
		$choices[] = array(
			'text'  => rgar( $meta, 'coupon_code' ),
			'value' => $object['id'],
		);
	}

	return $choices;
}, 10, 3 );

// Update "123" to your form ID.
add_action( 'gform_after_submission_123', function() {
	if ( is_callable( 'gf_coupons' ) ) {
		// Update "4" to the ID of your coupon-populated field.
		gf_coupons()->delete_feed( (int) rgpost( 'input_4' ) );
	}
} );
