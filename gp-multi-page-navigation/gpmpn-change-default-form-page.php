<?php
/**
 * Gravity Perks // Multi-Page Navigation // Change Default Form Page
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 *
 * This snippet sets the default form page to the second page.
 */
add_filter( 'gpmpn_default_page', function( $page ) {
	return 2;
} );
