<?php
/**
 * Gravity Perks // Advanced Select // Ignore Empty Search Value
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * When filtering Populate Anything results using the "Advanced Select Search Value" option, by default,
 * it will not show any results until the user has entered a search value. This snippet will ignore the
 * search value filter if no search value has been entered, displaying the first 50 results and allowing
 * the user to infinitely scroll through the all results.
 */
add_filter( 'gpadvs_js_init_args', function( $args ) {
	$args['ignoreEmptySearchValue'] = true;
	return $args;
} );
