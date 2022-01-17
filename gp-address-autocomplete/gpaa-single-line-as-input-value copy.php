<?php
/**
 * Gravity Perks // GP Address Autocomplete // Use Single Line Text field as autocomplete input
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete
 *
 * Instruction Video: https://www.loom.com/share/680513c2f0dc403aa6a001f9950f4e77
 *
 * This snippet allows you to use Single Line Text fields as input selectors for GP Address Autocomplete instead of the default Address field.
 *
 * Description:  Customize the filter name and autocomplete input selector accordingly
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */

// Customize the filter name below to match the form ID and Address field ID.
// Example: gpaa_init_args_1_1
add_filter( 'gpaa_init_args_FORMID_ADDRESSFIELDID', function ( $args ) {
	// Customize the autocomplete selector below to match the Single Line Text input selector.
	// Example: #input_1_2
	$args['inputSelectors']['autocomplete'] = '#input_FORMID_INPUTID';

	return $args;
} );
