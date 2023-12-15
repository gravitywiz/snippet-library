<?php
/**
 * Gravity Perks // Multi-Page Navigation // Change Default Form Page
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 */
add_filter( 'gpmpn_default_page', function( $page ) {
	// Update "2" to the page at which the form should start. "2" would start the form on the second page.
	return 2;
} );
