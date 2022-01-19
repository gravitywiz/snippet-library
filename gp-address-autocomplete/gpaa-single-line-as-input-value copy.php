<?php
/**
 * Gravity Perks // GP Address Autocomplete // Use Single Line Text field as autocomplete input
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instruction Video: https://www.loom.com/share/680513c2f0dc403aa6a001f9950f4e77
 *
 * This snippet allows you to use Single Line Text fields as input selectors for GP Address Autocomplete instead of the default Address field.
 */

// Update "123" to your form ID and "4" to your Address field ID.
add_filter( 'gpaa_init_args_123_4', function ( $args ) {
	// Update "123" to your form ID and "5" to your Single Line Text field ID.
  $args['inputSelectors']['autocomplete'] = '#input_123_5';

	return $args;
} );
