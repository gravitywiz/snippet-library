<?php
/**
 * Gravity Perks // Inventory // Disable Object Cache
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * GP Inventory caches objects to improve performance. This snippet disables object caching for a specific form.
 *
 * Instructions:
 *
 *  1. Install the snippet.
 *     https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 */


// This snippet disables object caching for a specific form.
add_filter( 'gpi_should_cache_object', function ( $cache, $form_id ) {
	if ( $form_id === 123 ) { // Update "123" to your form ID
		return false;
	}

	return $cache;
}, 10, 2 );

// This snippet disables object caching for all forms.
add_filter( 'gpi_should_cache_object', '__return_false' );
